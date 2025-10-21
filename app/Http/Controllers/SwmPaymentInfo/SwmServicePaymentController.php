<?php
// Last Modified Date: 10-07-2024
//Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024)
namespace App\Http\Controllers\SwmPaymentInfo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SwmPaymentInfo\SwmPaymentStatus;
use App\Models\LayerInfo\Ward;
use App\Models\SwmPaymentInfo\DueYear;
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
use App\Imports\SwmImport;
use Maatwebsite\Excel\HeadingRowImport;
use App\Models\SwmPaymentInfo\SwmPayment;


class SwmServicePaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:List SWM Service Payment Collection', ['only' => ['index']]);
        $this->middleware('permission:Import SWM Service Payment Collection From CSV', ['only' => ['create', 'store']]);
        $this->middleware('permission:Export SWM Service Payment Collection Info', ['only' => ['export', 'exportunmatched']]);

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = __("Solid Waste Information Support System");
        $wards = Ward::getInAscOrder();
        $dueYears = DueYear::getInAscOrder();

        return view('swmpayment-info.index', compact('page_title','wards', 'dueYears'));
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
        $buildingData =DB::table(DB::raw('(SELECT swm_customer_id, bin, tax_code, ward, customer_name, customer_contact, due_year 
                    FROM swm_info.swmservice_payment_status
                    ORDER BY swm_customer_id DESC) AS swm'))
                ->leftJoin('swm_info.due_years AS due', 'due.value', '=', 'swm.due_year')
                ->select('swm.*', 'due.name', 'swm.bin');
              


        return DataTables::of($buildingData)
            ->filter(function ($query) use ($request) {
                if ($request->dueyear_select) {
                    $query->where('due.name', $request->dueyear_select);
                }
                if ($request->ward_select) {
                    $query->where('swm.ward', $request->ward_select);
                }
                if ($request->swm_customer_id) {
                    $query->where('swm_customer_id', 'ILIKE', '%' . $request->swm_customer_id . '%');
                }
                if ($request->bin) {
                    $query->where('swm.bin', 'ILIKE', '%' . $request->bin . '%');
                }
                if ($request->tax_code) {
                    $query->where('swm.tax_code', 'ILIKE', '%' . $request->tax_code . '%');
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
        $page_title = __("Import Solid Waste Information Support System");
        return view('swmpayment-info.create', compact('page_title'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Validator::extend('file_extension', function ($attribute, $value, $parameters, $validator) {
                if( !in_array( $value->getClientOriginalExtension(), $parameters ) ){
                return false;
            }
            else {
                return true;
            }
        }, __('File must be csv format.') );
        $this->validate($request,
                ['csvfile' => 'required|file_extension:csv'],
                ['required' => __('The csv file is required.')],
        );

        if (!$request->hasfile('csvfile')) {

            return redirect('swm-payment/data')->with('error',__('The csv file is required.'));
        }
        if ($request->hasFile('csvfile')) {

                $filename = 'swm-service-payments.' . $request->file('csvfile')->getClientOriginalExtension();
                if (Storage::disk('importswmpayment')->exists('/' . $filename)){
                    Storage::disk('importswmpayment')->delete('/' . $filename);
                    //deletes if already exists
                }
                $stored = $request->file('csvfile')->storeAs('/', $filename, 'importswmpayment');

                if ($stored)
                {
                    $storage = Storage::disk('importswmpayment')->path('/');
                    $location = preg_replace('/\\\\/', '', $storage);

                    $file_selection = Storage::disk('importswmpayment')->listContents();
                    $filename = $file_selection[0]['basename'];

                    //checking csv file has all heading row keys
                    $headings = (new HeadingRowImport)->toArray($location.$filename);
                    $heading_row_errors = array();
                    if (!in_array("swm_customer_id", $headings[0][0])) {
                        $heading_row_errors['swm_customer_id'] = "Heading row : swm_customer_id is required";
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
                    \DB::statement('TRUNCATE TABLE swm_info.swmservice_payments RESTART IDENTITY');
                    #\DB::statement('ALTER SEQUENCE IF exists swm_info.swmservice_payment_id_seq RESTART WITH 1');
                    $import = new SwmImport();
                    $import->import($location.$filename);

                    $message = __('Successfully Imported SWM Service Payments From CSV.');
                    \DB::statement("select swm_info.fnc_swmpaymentstatus()");

                    \DB::statement('select swm_info.fnc_updonimprt_gridnward_swm()');

                    return redirect('swm-payment')->with('success',$message);

                }
                else{
                    $message = __('Building Tax Payments Not Imported From CSV.');
                }

        }
        flash('Could not import from csv. Try Again');
        return redirect('swm-payment');
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
        $swm_customer_id = $_GET['swm_customer_id'] ?? null;
        $bin = $_GET['bin'] ?? null;
        $tax_code = $_GET['tax_code'] ?? null;

        $columns = [
            __('SWM Customer ID'),
            __('BIN'),
            __('Tax Code'),
            __('Ward'),
            __('Customer Name'),
            __('Customer Contact'),
            __('Due Years')
        ];

        $query = DB::table('swm_info.swmservice_payment_status AS pmt')
                                ->leftjoin('swm_info.due_years AS due', 'due.value', '=', 'pmt.due_year')
                                ->select('pmt.*', 'due.name')
                                ->where('pmt.deleted_at', null)
                                ->orderBy('pmt.swm_customer_id', 'ASC');


        if (!empty($ward)) {
            $query->where('pmt.ward', $ward);
        }
        if (!empty($due_year)) {
            $query->where('due.name', $due_year);
        }
        if (!empty($swm_customer_id)) {
            $query->where('pmt.swm_customer_id', 'ILIKE', '%' . $swm_customer_id . '%');
        }
        if (!empty($tax_code)) {
            $query->where('pmt.tax_code', 'ILIKE', '%' . $tax_code . '%');
        }
        if (!empty($bin)) {
            $query->where('pmt.bin', 'ILIKE', '%' . $bin . '%');
        }
        // $results = $query->get();  // Get the results to confirm if the filter is applied

        
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Solid Waste Information Support System.csv')
            ->addRowWithStyle($columns, $style); //Top row of csv

        $query->chunk(5000, function ($swmpayments) use ($writer) {
            foreach($swmpayments as $swmpayment) {

                $values = [];
                $values[] = $swmpayment->swm_customer_id;
                $values[] = $swmpayment->bin;
                $values[] = $swmpayment->tax_code;
                $values[] = $swmpayment->ward;
                $values[] = $swmpayment->customer_name;
                $values[] = $swmpayment->customer_contact;
                $values[] = $swmpayment->name;
                $writer->addRow($values);
            }
        });

        $writer->close();
    }
    public function exportunmatched()
    {
        $columns = [
            __('SWM Customer ID'),
            __('Customer Name'),
            __('Customer Contact'),
            __('Last Payment date')
        ];

        $query = DB::table('swm_info.swmservice_payments AS pmt')
                 ->leftjoin('building_info.buildings as b', 'pmt.swm_customer_id', '=', 'b.swm_customer_id')
                 ->select('pmt.*')
                 ->whereNull('b.swm_customer_id')
                 ->orderBy('pmt.swm_customer_id', 'ASC');    
       
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Unmatched Records-Solid Waste Information Support System.csv')
            ->addRowWithStyle($columns, $style); //Top row of csv

        $query->chunk(5000, function ($swmpayments) use ($writer) {
            foreach($swmpayments as $swmpayment) {
                $values = [];
                $values[] = $swmpayment->swm_customer_id;
                $values[] = $swmpayment->customer_name;
                $values[] = $swmpayment->customer_contact;
                $values[] = $swmpayment->last_payment_date;
                $writer->addRow($values);
            }
        });

        $writer->close();
    }
}
