<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use App\Models\Fsm\HelpDesk;
use App\Models\Fsm\ServiceProvider;
use App\Http\Requests\Fsm\HelpDeskRequest;
use Auth;
use Illuminate\Http\Request;
use DB;
use App\Services\Fsm\HelpDeskService;
use App\Services\Auth\UserService;

class HelpDeskController extends Controller
{
    protected HelpDeskService $helpDeskService;
    protected UserService $userService;

     /**
     * Constructor method for the class.
     * @param HelpDeskService $helpDeskService The HelpDeskService instance used for helpdesk-related operations.
     * @param UserService $userService The UserService instance used for kpitarget-related operations.
     * 
     * @return void
     */
    public function __construct(HelpDeskService $helpDeskService, UserService $userService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Help Desks', ['only' => ['index']]);
        $this->middleware('permission:View Help Desk', ['only' => ['show']]);
        $this->middleware('permission:Add Help Desk', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Help Desk', ['only' => ['edit', 'update']]);
        $this->middleware('permission:View Help Desk History', ['only' => ['history']]);
        $this->middleware('permission:Delete Help Desk', ['only' => ['destroy']]);
        $this->userService = $userService;
        $this->helpDeskService = $helpDeskService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title =__('Help Desks');
        // Fetch operational service provider
        $service_providers = ServiceProvider::Operational()->pluck('company_name','id');
        return view('fsm/help-desks.index', compact('page_title','service_providers'));
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
        return $this->helpDeskService->getAllHelpDesks($data);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = __("Add Help Desk");
        $helpDesk = null;
        $serviceProviders = ServiceProvider::Operational()->orderBy('company_name')->pluck('company_name', 'id');
        return view('fsm/help-desks.create', compact('page_title','helpDesk', 'serviceProviders' ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(HelpDeskRequest $request)
    {
        $data = $request->all();
        $this->helpDeskService->storeOrUpdate($id = null,$data);    
        if(Auth::user()->service_provider_id)
        {
            $data['service_provider_id'] =  Auth::user()->service_provider_id;
            $data['help_desk_id'] =  HelpDesk::where('name',$data['name'])->pluck('id')->first();
            $data['user_type'] = "Help Desk";
            $data['roles']= "Service Provider - Help Desk";
            $data['gender']= null;
            $data['username']= null;
            $data['status'] = '1';
        }
        else
        {
            $data['help_desk_id'] =  HelpDesk::where('name',$data['name'])->pluck('id')->first();
            $data['user_type'] = "Help Desk";
            $data['roles']= "Municipality - Help Desk";
            $data['gender']= null;
            $data['username']= null;
            $data['status'] = '1';

        }

        if(!is_null($request->create_user))
        {
            $this->userService->storeOrUpdate($id = null,$data);
            
        }
        return redirect('fsm/help-desks')->with('success', __('Help Desk created successfully!'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $helpDesk = HelpDesk::find($id);
        if ($helpDesk) {
            $page_title = __("Help Desk Details");
            return view('fsm/help-desks.show', compact('page_title', 'helpDesk'));
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $helpDesk = HelpDesk::find($id);
        $serviceProviders = ServiceProvider::Operational()->orderBy('company_name')->pluck('company_name', 'id');
        if ($helpDesk) {
            $page_title = __("Edit Help Desk");
            return view('fsm/help-desks.edit', compact('page_title', 'helpDesk','serviceProviders'));
        } else {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(HelpDeskRequest $request, $id)
    {
        $helpDesk = HelpDesk::find($id);
        if ($helpDesk) {
            $data = $request->all();
            $this->helpDeskService->storeOrUpdate($helpDesk->id,$data);
            return redirect('fsm/help-desks')->with('success', __('Help Desk updated successfully.'));
        } else {
            Flash::error('Failed to update help desks');
            return redirect('fsm/help-desks')->with('error', __('Failed to update help desks.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $helpDesk = HelpDesk::find($id);
        if ($helpDesk) {
            if($helpDesk->users()->exists()){
                return redirect('fsm/help-desks')->with('error', __('Cannot delete Help Desk that has associated User Information.'));
            }
            $helpDesk->delete();
            return redirect('fsm/help-desks')->with('success', __('Help Desk deleted successfully.'));
        } else {
            return redirect('fsm/help-desks')->with('error', __('Failed to delete help desks.'));
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
        $helpDesk = HelpDesk::find($id);
        if ($helpDesk) {
            $page_title = __("Help Desk History");
            return view('fsm/help-desks.history', compact('page_title', 'helpDesk'));
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
        return $this->helpDeskService->download($data);
        
    }

}
