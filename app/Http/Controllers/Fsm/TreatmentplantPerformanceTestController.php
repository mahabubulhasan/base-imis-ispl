<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)
namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Fsm\TreatmentplantPerformanceTestRequest;
use App\Models\Fsm\TreatmentPlant;
use App\Services\Fsm\TreatmentplantPerformanceTestService;
use App\Models\Fsm\TreatmentPlantPerformanceTest;
use App\Models\SiteSetting;



class TreatmentplantPerformanceTestController extends Controller
{
    protected TreatmentplantPerformanceTestService $treatmentplantPerformanceTestService;

    public function __construct(TreatmentplantPerformanceTestService $treatmentplantPerformanceTestService)
    {
        $this->middleware('auth');
        $this->treatmentplantPerformanceTestService = $treatmentplantPerformanceTestService;
    }
    /**
    * Display a listing of the treatment plant performance test data.
    *
    * @param TreatmentplantPerformanceTestRequest $request The request object containing the treatment plant performance test data.
    * @return \Illuminate\Contracts\View\View Returns a view with the performance efficiency standards data.
    */
    public function index(TreatmentplantPerformanceTestRequest $request)
    {
        $page_title = __("Performance Efficiency Standards");
        $temp_data = TreatmentPlantPerformanceTest::all();
        $updated = $temp_data->isNotEmpty() ? $temp_data->max('updated_at') : null;
        if(!empty($updated))
        {
            $data = TreatmentPlantPerformanceTest::where('updated_at', $updated)->first();
        }
        else
        {
            $data = $temp_data->first();
        }

        return view('fsm/treatment-plant-performance-test.index', compact('page_title', 'data','updated'));
    }

    /**
    * Store or update treatment plant performance test data.
    *
    * @param TreatmentplantPerformanceTestRequest $request The request object containing the treatment plant performance test data.
    * @return \Illuminate\Http\RedirectResponse Redirects the user to a specified URL with a success message.
    */
    public function store(TreatmentplantPerformanceTestRequest $request)
    {
        $data = $request->all();
        $this->treatmentplantPerformanceTestService->storeOrUpdate($data);
        return redirect('fsm/treatment-plant-performance-test')->with('success', __('Performance Efficiency Standards updated successfully.'));
    }

}
