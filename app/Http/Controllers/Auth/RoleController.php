<?php
// Last Modified Date: 08-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Session;
use Illuminate\Support\Facades\Input;
use Laracasts\Flash\Flash;
use DB;
use Form;

class RoleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:List Roles', ['only' =>['index']]);
        $this->middleware('permission:View Role', ['only' =>['show']]);
        $this->middleware('permission:Add Role', ['only' =>['create', 'store']]);
        $this->middleware('permission:Edit Role', ['only' =>['edit', 'update']]);
        $this->middleware('permission:Delete Role', ['only' =>['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = __("Roles");
        $roles = Role::where('name', '!=', 'Super Admin')->get();

        return view('roles.index',[
            'page_title'=>$page_title,
            'roles' => $roles
        ]);
    }

    public function searchPermission(Request $request, $id)
    {
        $search = $request->search;
        $page_title = __('Edit Role');
        $role = Role::find($id);
       $permission = DB::select("SELECT * FROM permissions WHERE LOWER(permissions.name) LIKE LOWER('%" . $search . "%')");

       $rolePermissions = DB::table("role_has_permissions")
       ->where("role_has_permissions.role_id",$id)

       ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
       ->all();
       return view('roles.edit',compact('page_title', 'role','permission','rolePermissions'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = __('Create Role');
        $permission = Permission::get();
        $grouped_permissions = $this->getGroupedPermissions();
        $rolePermissions = array();
        return view('roles.create', compact('page_title','permission', 'rolePermissions','grouped_permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
        'name' => 'required|unique:pgsql.auth.roles,name',
        'permission' => 'required',
        ]);
        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));

        return redirect('auth/roles')->with('success', __('Role created successfully.'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $page_title = __('Edit Role');
        $role = Role::find($id);
        $permission = Permission::get();
        $grouped_permissions = $this->getGroupedPermissions();
        $rolePermissions = DB::table("auth.role_has_permissions")->where("auth.role_has_permissions.role_id",$id)
        ->pluck('auth.role_has_permissions.permission_id','auth.role_has_permissions.permission_id')
        ->all();
        return view('roles.edit',compact('page_title', 'role','permission','rolePermissions','grouped_permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        if($role && $role->name != 'Super Admin') {
            $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
            ]);

            $role->name = $request->input('name');
            $role->save();
            $role->syncPermissions($request->input('permission'));

            return redirect('auth/roles')->with('success', __('Role updated successfully.'));
        }
        else {
            return redirect('auth/roles')->with('error', __('Failed to update role.'));
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
        $role = Role::find($id);

        if($role && $role->name != 'Super Admin') {
            $role->delete();

            return redirect('auth/roles')->with('success', __('Role deleted successfully.'));
        }
        else {
            return redirect('auth/roles')->with('error', __('Failed to delete role.'));
        }
    }

    /**
     * Get the specified resource from storage.
     *
     * @param null
     * @return array $groupedPermissions
     */
    public function getGroupedPermissions(){

        $dashboard = Permission::where('group','Dashboard')->orderBy('type')->get();
        $building_dashboard = Permission::where('group','Building Dashboard')->orderBy('type')->get();
        $building_structures = Permission::where('group','Building Structures')->orderBy('type')->get();
        $building_surveys = Permission::where('group','Building Surveys')->orderBy('type')->get();
        $low_income_communities = Permission::where('group','Low Income Communities')->orderBy('type')->get();
        $fsm_dashboard = Permission::where('group','FSM Dashboard')->orderBy('type')->get();
        $containments = Permission::where('group','Containments')->orderBy('type')->get();
        $service_providers = Permission::where('group','Service Providers')->orderBy('type')->get();
        $employee_infos = Permission::where('group','Employee Infos')->orderBy('type')->get();
        $desludging_vehicles = Permission::where('group','Desludging Vehicles')->orderBy('type')->get();
        $treatment_plants = Permission::where('group','Treatment Plants')->orderBy('type')->get();
        $treatment_plant_efficiency_standards = Permission::where('group','Treatment Plant Efficiency Standards')->orderBy('type')->get();
        $treatment_plant_efficiency_tests = Permission::where('group','Treatment Plant Efficiency Tests')->orderBy('type')->get();
        $applications = Permission::where('group','Applications')->orderBy('type')->get();
        $emptyings = Permission::where('group','Emptyings')->orderBy('type')->get();
        $sludge_collections = Permission::where('group','Sludge Collections')->orderBy('type')->get();
        $feedbacks = Permission::where('group','Feedbacks')->orderBy('type')->get();
        $help_desks = Permission::where('group','Help Desks')->orderBy('type')->get();
        $sewer_connection = Permission::where('group','Sewer Connection')->orderBy('type')->get();
        $ptct_toilets = Permission::where('group','PT/CT Toilets')->orderBy('type')->get();
        $pt_users_logs = Permission::where('group','PT Users Logs')->orderBy('type')->get();
        $cwis = Permission::where('group','CWIS')->orderBy('type')->get();
        $kpi_dashboard = Permission::where('group','KPI Dashboard')->orderBy('type')->get();
        $kpi_target = Permission::where('group','KPI Target')->orderBy('type')->get();
        $utility_dashboard = Permission::where('group','Utility Dashboard')->orderBy('type')->get();
        $roads = Permission::where('group','Roads')->orderBy('type')->get();
        $drain = Permission::where('group','Drain')->orderBy('type')->get();
        $sewers = Permission::where('group','Sewers')->orderBy('type')->get();
        $watersupply_network = Permission::where('group','WaterSupply Network')->orderBy('type')->get();
        $swm_service_payment = Permission::where('group','Swm Service Payment')->orderBy('type')->get();
        $property_tax_collection_iss = Permission::where('group','Property Tax Collection ISS')->orderBy('type')->get();
        $water_supply_iss = Permission::where('group','Water Supply ISS')->orderBy('type')->get();
        $data_export = Permission::where('group','Data Export')->orderBy('type')->get();
        $maps = Permission::where('group','Maps')->orderBy('type')->get();
        $water_samples = Permission::where('group','Water Samples')->orderBy('type')->get();
        $hotspots = Permission::where('group','Hotspots')->orderBy('type')->get();
        $yearly_waterborne_cases = Permission::where('group','Yearly Waterborne Cases')->orderBy('type')->get();
        $users = Permission::where('group','Users')->orderBy('type')->get();
        $roles = Permission::where('group','Roles')->orderBy('type')->get();
        $api = Permission::where('group','API')->orderBy('type')->get();
        $language = Permission::where('group','Language')->orderBy('type')->get();

        $groupedPermissions = collect([
            'Dashboard' => $dashboard,
            'Building Dashboard' => $building_dashboard,
            'Building Structures' => $building_structures,
            'Building Surveys' => $building_surveys,
            'Low Income Communities' => $low_income_communities,
            'FSM Dashboard' => $fsm_dashboard,
            'Containments' => $containments,
            'Service Providers' => $service_providers,
            'Employee Infos' => $employee_infos,
            'Desludging Vehicles' => $desludging_vehicles,
            'Treatment Plants' => $treatment_plants,
            'Treatment Plant Efficiency Standards' => $treatment_plant_efficiency_standards,
            'Treatment Plant Efficiency Tests' => $treatment_plant_efficiency_tests,
            'Applications' => $applications,
            'Emptyings' => $emptyings,
            'Sludge Collections' => $sludge_collections,
            'Feedbacks' => $feedbacks,
            'Help Desks' => $help_desks,
            'Sewer Connection' => $sewer_connection,
            'PT/CT Toilets' => $ptct_toilets,
            'PT Users Logs' => $pt_users_logs,
            'CWIS' => $cwis,
            'KPI Dashboard' => $kpi_dashboard,
            'KPI Target' => $kpi_target,
            'Utility Dashboard' => $utility_dashboard,
            'Roads' => $roads,
            'Drain' => $drain,
            'Sewers' => $sewers,
            'WaterSupply Network' => $watersupply_network,
            'Swm Service Payment' => $swm_service_payment,
            'Property Tax Collection ISS' => $property_tax_collection_iss,
            'Water Supply ISS' => $water_supply_iss,
            'Data Export' => $data_export,
            'Maps' => $maps,
            'Water Samples' => $water_samples,
            'Hotspots' => $hotspots,
            'Yearly Waterborne Cases' => $yearly_waterborne_cases,
            'Users' => $users,
            'Roles' => $roles,
            'API' => $api,
            'Language' => $language,
        ]);

        return $groupedPermissions;
    }

    public function getRoles(Request $request){

        $type = $request->user_type;
        if($type == "Help Desk"){
        $roles = Role::where('name', "LIKE", "%$type%")
                    ->pluck('name', 'name');
        } else {
            $user_id = $request->id;
            $roles = Role::where('name', "LIKE", "%$type%")->where('name',"NOT ILIKE", '%Help Desk%')
                    ->pluck('name', 'name');
        }
        $html = '<select name="roles[]" class="form-control chosen-select" id="roles" multiple="true">';

        foreach($roles as $role)
        {
            if($request->roles && in_array($role, json_decode($request->roles)))
            {
                $html .= '<option value="'.$role.'" selected>'.$role.'</option>';
            }
            else
            {
                $html .= '<option value="'.$role.'">'.$role.'</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }

    public function getservRoles(Request $request)
    {
        \Log::info('Request Data:', $request->all()); // Log request data for debugging

        $type = $request->user_type;

        if ($type == "Help Desk") {
            $roles = collect(['Service Provider - Help Desk']);
        } else {
            $roles = Role::where('name', "LIKE", "%$type%")
                         ->where('name', "NOT ILIKE", '%Help Desk%')
                         ->pluck('name', 'name');
        }

        $html = '<select name="roles[]" class="form-control chosen-select" id="roles" multiple="true">';

        foreach ($roles as $role) {
            if ($request->roles && in_array($role, json_decode($request->roles))) {
                $html .= '<option value="' . $role . '" selected>' . $role . '</option>';
            } else {
                $html .= '<option value="' . $role . '">' . $role . '</option>';
            }
        }

        $html .= '</select>';
        return $html;
    }


}
