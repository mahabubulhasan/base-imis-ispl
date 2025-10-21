<?php
// Last Modified Date: 07-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)    
namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Fsm\KpiService;
use App\Models\Fsm\ServiceProvider;
use App\Models\Fsm\KeyPerformanceIndicator;
use App\Http\Requests\Fsm\KpiTargetRequest;
use App\Models\Fsm\KpiTarget;


use DB;



class KpiTargetController extends Controller
{
    protected KpiService $kpiService;

     /**
     * Constructor method for the class.
     * @param KpiService $kpiService The KpiService instance used for kpitarget-related operations.
     * @return void
     */

    public function __construct(KpiService $kpiService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List KPI Target', ['only' => ['index']]);
        $this->middleware('permission:View KPI Target', ['only' => ['show']]);
        $this->middleware('permission:Add KPI Target', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit KPI Target', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete KPI Target', ['only' => ['destroy']]);
        $this->middleware('permission:Export KPI Target', ['only' => ['export']]);
        $this->middleware('permission:View KPI Target History', ['only' => ['history']]);

       
        $this->kpiService = $kpiService;
    }

    /**
     * Controller method for displaying the index page of KPI Target.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $page_title = __("KPI Target");
         // Fetch distinct years 
        $years = KpiTarget::distinct()->pluck('year')->sortDesc()->all();
         // Retrieve all indicator from the database,ordered by their 'id' column in ascending order
        $indicators = KeyPerformanceIndicator::orderBy('id', 'asc')->pluck('indicator','id')->all();
        return view('fsm/kpi-target.index', compact('page_title','indicators', 'years' ));
    }

    /**
     * Retrieves data based on the provided request parameters.
     *
     * @param Illuminate\Http\Request $request 
     * @return mixed The data retrieved from the KPI service.
     */
    public function getData(Request $request)
    {
        $data = $request->all();
        return $this->kpiService->getAllData($data);
        
        
    }

    /**
     * Display the form for creating a new KPI target.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $page_title = __("Add KPI Target");
        $indicators = KeyPerformanceIndicator::orderBy('id', 'asc')->pluck('indicator','id')->all();
        return view('fsm/kpi-target.create', compact('page_title', 'indicators'));
    }

    /** 
    * Store a newly created resource in storage.
    *
    * @param  \App\Http\Requests\Fsm\KpiTargetRequest  $request
    * @return \Illuminate\Http\RedirectResponse
    */
    public function store(KpiTargetRequest $request)
{
        $data = $request->all();
        $this->kpiService->storeOrUpdate(null, $data);
        return redirect('fsm/kpi-targets')->with('success', __('KPI Target created successfully.'));
    
}
    /**
     * Display the form for editing a KPI target.
     *
     * @param  int  $id  The ID of the KPI target to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
          // Retrieve all indicators to populate a dropdown menu in the form
        $indicators = KeyPerformanceIndicator::orderBy('id', 'asc')->pluck('indicator','id')->all();
        // Find the KPI target with the given ID
        $kpi = KpiTarget::find($id);
        if ($kpi) {
            $page_title = __("Edit KPI Target");
            return view('fsm.kpi-target.edit', compact('page_title', 'kpi', 'indicators'));
        } else {
            abort(404);
        }
    }

    /**
     * Update a KPI target.
     *
     * @param  \App\Http\Requests\Fsm\KpiTargetRequest  $request  
     * @param  int  $id  The ID of the KPI target to be updated
     * @return \Illuminate\Http\RedirectResponse  A redirect response indicating success or failure of the update
     */
    public function update(KpiTargetRequest $request, $id)
    {
        
        $kpi = KpiTarget::find($id);
        if ($kpi) {
            $data = $request->all();
      
            $this->kpiService->storeOrUpdate($kpi->id,$data);
            return redirect('fsm/kpi-targets')->with('success',__('KPI Target updated successfully.'));
        } else {
            return redirect('fsm/kpi-targets')->with('error',__('Failed to update KPI Target'));
        }
    }


    /**
     * Display the details of a specific KPI target.
     *
     * @param  int  $id  
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $kpi = KpiTarget::find($id);
        // Retrieve the corresponding indicator for the KPI
        $indicators = KeyPerformanceIndicator::where('id','=',$kpi->indicator_id)->pluck('indicator', 'id')->first();
        
        if ($kpi) {
            $page_title = __("KPI Target Details");
            return view('fsm/kpi-target.show', compact('page_title', 'kpi', 'indicators'));
        } else {
            abort(404);
        }
    }

    /**
     * Remove the specified KPI target from storage.
     *
     * @param  int  $id 
     * @return \Illuminate\Http\RedirectResponse A redirect response indicating success or failure
     */
    public function destroy($id)
    {
        $kpi = KpiTarget::find($id);
        if ($kpi) {
            $kpi->delete();
             $kpi_years = KpiTarget::pluck('year')->unique()->toArray();
            $existingYears = Quarters::pluck('year')->unique()->toArray();
            $missingYears = array_diff($existingYears, $kpi_years);
            if (!empty($missingYears)) {
                Quarters::whereIn('year', $missingYears)->delete();
            }
            return redirect('fsm/kpi-targets')->with('success',__('KPI Target deleted successfully.'));
        } else {
            return redirect('fsm/kpi-targets')->with('error',__('Failed to delete KPI Target'));
        }
    }
    
    /**
     * Display history of the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function history($id)
    {
        $kpi = KpiTarget::find($id);
        if ($kpi) {
            $page_title = __("KPI Target History");
            return view('fsm/kpi-target.history', compact('page_title', 'kpi'));
        } else {
            abort(404);
        }
    }

     /**
     * Export a listing of the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $data = $request->all();
        return $this->kpiService->download($data);
        
    }
}


