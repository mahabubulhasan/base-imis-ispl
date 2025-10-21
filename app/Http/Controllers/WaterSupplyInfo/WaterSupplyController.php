<?php

namespace App\Http\Controllers\WaterSupplyInfo;

use App\Http\Controllers\Controller;
use App\Models\WaterSupplyInfo\WaterSupply;
use Illuminate\Http\Request;
use App\Models\WaterSupplyInfo\WaterSupplyStatus;
use App\Models\LayerInfo\Ward;
use App\Models\WaterSupplyInfo\DueYear;
use DataTables;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\AbstractWriter;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\WaterSupplyImport;
use Maatwebsite\Excel\HeadingRowImport;

class WaterSupplyController extends Controller
{
     public function __construct()
        {
            $this->middleware('auth');
            $this->middleware('permission:List Water Supply Collection', ['only' => ['index']]);
            $this->middleware('permission:Import Water Supply Collection From CSV', ['only' => ['create', 'store']]);
            $this->middleware('permission:Export Water Supply Collection Info', ['only' => ['export', 'exportunmatched']]);

        }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = __("Water Supply Payments");
        $wards = Ward::getInAscOrder();
        $dueYears = DueYear::getInAscOrder();
        return view('watersupply-info.index', compact('page_title','wards', 'dueYears'));
    }
    /**
     * Prepare data for the DataTable.
     *
     * @param Request $request
     * @return DataTables
     * @throws Exception
     */
    public function getData(Request $request)
    {

        $buildingData = DB::table(DB::raw('(SELECT water_customer_id, bin, tax_code, ward, customer_name, customer_contact, due_year 
            FROM watersupply_info.watersupply_payment_status
            ORDER BY water_customer_id DESC) pmt'))
            ->leftjoin('watersupply_info.due_years AS due', 'due.value', '=', 'pmt.due_year')
            ->select('pmt.*',  'due.name');

        return DataTables::of($buildingData)
            ->filter(function ($query) use ($request) {
                if ($request->dueyear_select) {
                    $query->where('due.name', $request->dueyear_select);
                }
                if ($request->ward_select) {
                    $query->where('pmt.ward', $request->ward_select);
                }
            })
            ->make(true);

    }
    /**
     * Display the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $page_title = __("Import Water Supply");
        return view('watersupply-info.create', compact('page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ini_set('max_execution_time', 600);
        Validator::extend('file_extension', function ($attribute, $value, $parameters, $validator) {
                if( !in_array( $value->getClientOriginalExtension(), $parameters ) ){
                return false;
            }
            else {
                return true;
            }
        },  __('File must be csv format.') );
        $this->validate($request,
                ['csvfile' => 'required|file_extension:csv'],
                ['required' => __('The csv file is required.')],
        );

        if (!$request->hasfile('csvfile')) {
            return redirect('watersupply-payment/data')->with('error','CSV file is required.');
        }
        if ($request->hasFile('csvfile')) {

                $filename = 'watersupply-payments.' . $request->file('csvfile')->getClientOriginalExtension();

                if (Storage::disk('importwatersupply')->exists('/' . $filename)){
                    Storage::disk('importwatersupply')->delete('/' . $filename);
                    //deletes if already exists
                }
                $stored = $request->file('csvfile')->storeAs('/', $filename, 'importwatersupply');

                if ($stored)
                {
                    $storage = Storage::disk('importwatersupply')->path('/');
                    $location = preg_replace('/\\\\/', '', $storage);

                    $file_selection = Storage::disk('importwatersupply')->listContents();
                    $filename = $file_selection[0]['basename'];
                    //checking csv file has all heading row keys

                    $headings = (new HeadingRowImport)->toArray($location.$filename);
                    $heading_row_errors = array();
                    if (!in_array("water_customer_id", $headings[0][0])) {
                        $heading_row_errors['water_customer_id'] = "Heading row : water_customer_id is required";
                    }
                    if (!in_array("customer_name", $headings[0][0])) {
                        $heading_row_errors['customer_name'] = "Heading row : customer_name is required";
                    }
                    if (!in_array("customer_contact", $headings[0][0])) {
                        $heading_row_errors['customer_contact'] = "Heading row : customer_contact is required";
                    }
                    if (!in_array("last_payment_date", $headings[0][0])) {
                        $heading_row_errors['last_payment_date'] = "Heading row : last_payment_date is required";
                    }
                    if (count($heading_row_errors) > 0) {
                    return back()->withErrors($heading_row_errors);
                    }
                    \DB::statement('TRUNCATE TABLE watersupply_info.watersupply_payments RESTART IDENTITY');
                    #\DB::statement('ALTER SEQUENCE IF exists watersupply_info.watersupply_payments_id_seq RESTART WITH 1');
                    $import = new WaterSupplyImport();
                    $import->import($location.$filename);
                    
                    $message = __('Successfully Imported Water Supply Payments From CSV.');
                    \DB::statement("select watersupply_info.fnc_watersupplystatus()");

                    \DB::statement('select watersupply_info.fnc_updonimprt_gridnward_watersupply()');

                    return redirect('watersupply-payment')->with('success',$message);

                }
                else{
                    $message = __('Water Supply Payments Not Imported From CSV.');
                }

        }
        flash('Could not import from csv. Try Again');
        return redirect('watersupply-payment');
    }
    /**
     * Export building tax payment data to a CSV file.
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        $ward = $_GET['ward'] ?? null;
        $due_year = $_GET['due_year'] ?? null;
        $columns = [
            __('Water Customer ID'),
            __('BIN'),
            __('Tax Code'),
            __('Ward'),
            __('Customer Name'),
            __('Customer Contact'),
            __('Due Years')
        ];

        $query = DB::table('watersupply_info.watersupply_payment_status AS pmt')
                                ->leftjoin('watersupply_info.due_years AS due', 'due.value', '=', 'pmt.due_year')
                                ->select('pmt.*', 'due.name')
                                ->where('pmt.deleted_at', null)
                                ->orderBy('pmt.water_customer_id', 'ASC');

        if (!empty($ward)) {
            $query->where('pmt.ward', $ward);
        }
        if (!empty($due_year)) {
            $query->where('due.name', $due_year);
        }
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Water Supply Information Support System.csv')
            ->addRowWithStyle($columns, $style); //Top row of csv

        $query->chunk(5000, function ($waterpayments) use ($writer) {
            foreach($waterpayments as $waterpayment) {

                $values = [];
                $values[] = $waterpayment->water_customer_id;
                $values[] = $waterpayment->bin;
                $values[] = $waterpayment->tax_code;
                $values[] = $waterpayment->ward;
                $values[] = $waterpayment->customer_name;
                $values[] = $waterpayment->customer_contact;
                $values[] = $waterpayment->name;

                $writer->addRow($values);
            }
        });

        $writer->close();
    }
    public function exportunmatched()
    {
        $columns = [
            __('Water Customer ID'),
            __('Customer Name'),
            __('Customer Contact'),
            __('Last Payment date')
        ];

        $query = DB::table('watersupply_info.watersupply_payments AS pmt')
                 ->leftjoin('building_info.buildings as b', 'pmt.water_customer_id', '=', 'b.water_customer_id')
                 ->select('pmt.*')
                 ->whereNull('b.water_customer_id')
                 ->orderBy('pmt.water_customer_id', 'ASC');    
       
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Unmatched Records-Water Supply Information Support System.csv')
            ->addRowWithStyle($columns, $style); //Top row of csv

        $query->chunk(5000, function ($waterpayments) use ($writer) {
            foreach($waterpayments as $waterpayment) {
                $values = [];
                $values[] = $waterpayment->water_customer_id;
                $values[] = $waterpayment->customer_name;
                $values[] = $waterpayment->customer_contact;
                $values[] = $waterpayment->last_payment_date;
                $writer->addRow($values);
            }
        });

        $writer->close();
    }
}
