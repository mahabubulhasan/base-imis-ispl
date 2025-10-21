<?php
// Last Modified Date: 10-05-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Controllers\Fsm;

use App\Services\Fsm\CtptUserServiceClass;
use App\Http\Requests\Fsm\CtptUserRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Fsm\Ctpt;
use App\Models\Fsm\CtptUsers;




class CtptUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected CtptUserServiceClass $ctptUserServiceClass;
    public function __construct(CtptUserServiceClass $ctptUserServiceClass)
    {
        $this->middleware('auth');
        $this->middleware('permission:List PT Users Log', ['only' => ['index']]);
        $this->middleware('permission:View PT Users Log', ['only' => ['show']]);
        $this->middleware('permission:Add PT Users Log', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit PT Users Log', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete PT Users Log', ['only' => ['destroy']]);
        $this->middleware('permission:View PT Users Log History', ['only' => ['history']]);
        $this->middleware('permission:Export PT Users Logs', ['only' => ['export']]);
        $this->ctptUserServiceClass = $ctptUserServiceClass;

    }

    public function index()
    {

        $page_title = __("PT Users Log");
        $name = Ctpt::pluck('name');
        return view('fsm.ctpt-users.index', compact('page_title', 'name'));
    }


    public function getData(Request $request)
    {
        return $this->ctptUserServiceClass->fetchData($request);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = __("Add PT Users Log");
        $ctptData = ctpt::where('status', true)
            ->where('type', 'Public Toilet')
            ->get(['id', 'name'])
            ->mapWithKeys(function ($item) {
                return [$item->id => ($item->name ? $item->id . ' - ' . $item->name : $item->id)];
            })
            ->toArray();
        $name = $ctptData;

        return view('fsm.ctpt-users.create', compact('page_title', 'name'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CtptUserRequest $request)
    {

        return $this->ctptUserServiceClass->storeData($request);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $info = CtptUsers::find($id);
        if ($info) {
            $page_title = __("PT Users Log Details");
            $ctptData = Ctpt::select('id', 'name')
            ->where('id', $info->toilet_id)
            ->first();
            $name = $ctptData->name ? $ctptData->id . ' - ' . $ctptData->name : $ctptData->id;
            return view('fsm.ctpt-users.show', compact('page_title','info','name'));
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
        $info = CtptUsers::find($id);
        if ($info) {
            $page_title = __("Edit PT Users Log");
            $ctptData = Ctpt::select('id', 'name')
            ->where('id', $info->toilet_id)
            ->first();
            $name = $ctptData->name ? $ctptData->id . ' - ' . $ctptData->name : $ctptData->id;
            $users = CtptUsers::where('id','=',$id)->latest()->first();
            return view('fsm.ctpt-users.edit', compact('page_title','info','name','users'));
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
    public function update(CtptUserRequest $request, $id)
    {

        return $this->ctptUserServiceClass->updateData($request, $id);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $pt_user_log = CtptUsers::find($id);
        if ($pt_user_log) {
            $pt_user_log->delete();
            return redirect('fsm/ctpt-users')->with('success', __('PT Users Log Deleted Successfully.'));
            }
        else {
            return redirect('fsm/ctpt-users')->with('error', __('Failed to delete PT Users Log.'));
        }
    }

    public function history($id)
    {
        $ctpt = CtptUsers::find($id);
        if ($ctpt) {
            $page_title = __("PT Users Log History");
            return view('fsm.ctpt-users.history', compact('page_title', 'ctpt'));
        } else {
            abort(404);
        }
    }

    public function export()
    {
        return $this->ctptUserServiceClass->exportData();

    }
}
