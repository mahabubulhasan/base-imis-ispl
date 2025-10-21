<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)
namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use DB;
use Carbon\Carbon;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use App\Helpers\Common;
use Spatie\Permission\Models\Role;
use App\Models\Fsm\HelpDesk;
use App\Models\Fsm\ServiceProvider;
use App\Models\Fsm\TreatmentPlant;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;


class UserService
{

    protected $session;
    protected $instance;

    /**
     * Constructs a new UserService object.
     *
     *
     */
    public function __construct()
    {
        /*Session code
        ....
         here*/
    }

    /**
     * Get all users based on the user's role.
     *
     * @param mixed $data Additional data (not used in the method)
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllData($data)
    {
        if (Auth::user()->hasRole('Service Provider - Admin')) {
            return (User::where('service_provider_id', '=', Auth::user()->service_provider_id)->where('id', '!=', Auth::id())->latest('created_at')->get());
        } else if (Auth::user()->hasRole('Treatment Plant - Admin')) {
            return (User::where('treatment_plant_id', '=', Auth::user()->treatment_plant_id)->where('id', '!=', Auth::id())->latest('created_at')->get());
        } else if ((Auth::user()->hasRole('Municipality - Sanitation Department'))) {
            return (User::whereIn('user_type', ['Service Provider', 'Treatment Plant', 'Help Desk'])->where('id', '!=', Auth::id())->latest('created_at')->get());
        } else if ($data->user()->hasRole('Municipality - IT Admin') || Auth::user()->hasRole('Municipality - Executive')) {

            return (User::where('id', '!=', Auth::id())->get());
        } else {
            return  $users = User::latest('created_at')->where('id', '!=', Auth::id())->get();
        }
    }

    /**
     * Get roles and help desks based on the user's role.
     *
     * @param mixed $data Additional data (not used in the method)
     * @return array Roles and help desks
     */
    public function getRoleHelpDeskData($data)
    {
        if (!$data->user()->hasRole("Super Admin") && !$data->user()->hasRole("Municipality - Super Admin") && !$data->user()->hasRole("Municipality - IT Admin")) {
            if ($data->user()->hasRole("Municipality - Sanitation Department")) {
                $roles = Role::where('name', '!=', 'Super Admin')
                    ->where('name', 'LIKE', '%Service Provider%')
                    ->pluck('name', 'name');
                $helpDesks = HelpDesk::orderBy('name')->pluck('name', 'id');
            }
            else if ($data->user()->hasRole("Service Provider - Admin")) {
                $helpDesks = HelpDesk::where('service_provider_id', '=', $data->user()->service_provider_id)->orderBy('name')->pluck('name', 'id');
            }
            else {
                $roles = Role::where('name', '!=', 'Super Admin')
                    ->where('name', '!=', 'Service Provider - Admin')
                    ->where('name', '!=', 'Service Provider')
                    ->where('name', 'LIKE', '%Service Provider%')
                    ->pluck('name', 'name');
                $helpDesks = HelpDesk::where('service_provider_id', '=', $data->user()->service_provider_id)->orderBy('name')->pluck('name', 'id');
            }
        } else {
            $roles = Role::where('name', '!=', 'Super Admin')->pluck('name', 'name')->all();
            $helpDesks = HelpDesk::orderBy('name')->pluck('name', 'id');
        }
        return ['roles' => $roles, 'helpDesks' => $helpDesks];
    }


    /**
     * Get user-related data such as roles, treatment plants, help desks, and service providers.
     *
     * @param int $id The user ID
     * @return array User-related data
     */
    public function getUserRelatedData($id)
    {
        $userDetail = User::findorfail($id);

        if ($userDetail->treatment_plant_id) {
            $treatmentPlants = TreatmentPlant::findorfail($userDetail->treatment_plant_id);
        } else {
            $treatmentPlants = null;
        }
        if ($userDetail->help_desk_id) {
            $helpDesks = HelpDesk::findorfail($userDetail->help_desk_id);
        } else {
            $helpDesks = null;
        }
        if ($userDetail->service_provider_id) {
            $serviceProviders = ServiceProvider::findorfail($userDetail->service_provider_id);

        } else {
            $serviceProviders = null;
        }

        $userRoles = array();
        foreach ($userDetail->roles as $role) {
            $userRoles[] = $role->name;
        }
        return ['roles' => $userRoles, 'helpDesks' => $helpDesks, 'treatmentPlants' => $treatmentPlants, 'serviceProviders' => $serviceProviders];
    }

    /**
     * Store or update user data based on the presence of an ID.
     *
     * @param int|null $id The user ID (null for new user)
     * @param array|Request $data The user data (array or Request object)
     * @return void
     */
    public function storeOrUpdate($id, $data)
    {

        if (is_null($id)) {
            $input = $data;
            $user = new User();
            $user->name = $input['name'];
            $user->username = strtolower($input['username']);
            $user->email = strtolower($input['email']);
            $user->password = bcrypt($input['password']);
            $user->user_type = $input['user_type'];
            $user->gender = $input['gender'];
            switch ($user->user_type) {
                case ('Service Provider'):
                    $user->service_provider_id = $input['service_provider_id'];
                    break;
                case ('Treatment Plant'):
                    $user->treatment_plant_id = $input['treatment_plant_id'];
                break;
                case('Help Desk'):
                    if(isset($input['service_provider_id'])){
                        $user->service_provider_id = $input['service_provider_id'];

                    }
                    $user->help_desk_id = isset($input['help_desk_id']) ? $input['help_desk_id'] : (isset($input['help_desk_id_1']) ? $input['help_desk_id_1'] : $input['help_desk_id_2']);
                
                    break;
                default:
                    break;
            }
            $user->status = $input['status'];
            $user->save();

            $roles = [$input['roles']];
            // Search Super Admin role
            $super_admin = array_search('Super Admin', $roles);

            // array_seearch returns false if an element is not found
            // so we need to do a strict check here to make sure
            if ($super_admin !== false) {

                // Remove from array
                unset($roles[$super_admin]);
            }
            $user->assignRole($roles);
        } else {
            $user = User::find($id);
            $input = $data->all();
            $user->name = $input['name'];
            $user->gender = $input['gender'];
            $user->username = strtolower($input['username']);
            $user->email = strtolower($input['email']);
            if ($input['password']) {
                $user->password = bcrypt($input['password']);
            }
            $user->user_type = $input['user_type'];
            switch ($user->user_type) {
                case ('Service Provider'):
                    $user->service_provider_id = $input['service_provider_id'];
                    $user->help_desk_id =  null;
                    $user->treatment_plant_id =  null;
                    break;
                case ('Treatment Plant'):
                    $user->treatment_plant_id = $input['treatment_plant_id'];
                    $user->help_desk_id =  null;
                    $user->service_provider_id =  null;

                break;
                case('Help Desk'):
                    if(isset($input['service_provider_id'])){
                        $user->service_provider_id = isset($input['service_provider_id']);
                    }
                    $user->help_desk_id = $input['help_desk_id'];
                    $user->treatment_plant_id =  null;
                break;
            default:
                    break;
            }

            DB::table('fsm.applications')->where('user_id', $user->id)->update(['service_provider_id' => $user->service_provider_id]);

            DB::table('auth.model_has_roles')->where('model_id', $id)->delete();

            $roles = $input['roles'];
            // Search Super Admin role
            $super_admin = array_search('Super Admin', $roles);

            // array_seearch returns false if an element is not found
            // so we need to do a strict check here to make sure
            if ($super_admin !== false) {

                // Remove from array
                unset($roles[$super_admin]);
            }
            $user->assignRole($roles);
            $user->status = $input['status'];
            $user->save();
        //flushing all sessions for that user
        // Log out all other sessions for user who's password is being updated except current session
        if ($input['password']) {
            $this->terminateOtherSessions($user);
        }
    }
    }
    /**
     * Download a listing of the specified resource from storage.
     *
     * @param array $data
     * @return null
     */
    public function download($data)
    {
        $columns = [
            __('User ID'),
            __('Name'),
            __('Gender'),
            __('Username'),
            __('Email'),
            __('Treatment Plant'), // This will be the name from the TreatmentPlant table
            __('Help Desk'), // This will be the name from the HelpDesk table
            __('Service Provider'), // This will be the name from the ServiceProvider table
            __('User Type'),
            __('Status')

        ];

        // Query with joins to the fsm.treatment_plants, fsm.service_providers, and fsm.help_desks tables
        $query = User::select(
                'users.id',
                'users.name',
                'users.gender',
                'users.username',
                'users.email',
                'treatment_plants.name as treatment_plant_name', // Get the treatment plant name
                'help_desks.name as help_desk_name', // Get the help desk name
                'service_providers.company_name as service_provider_name', // Get the service provider name
                'users.user_type',
                'users.status'
            )
            ->leftJoin('fsm.treatment_plants', 'users.treatment_plant_id', '=', 'fsm.treatment_plants.id') // Join with treatment_plants
            ->leftJoin('fsm.help_desks', 'users.help_desk_id', '=', 'fsm.help_desks.id') // Join with help_desks
            ->leftJoin('fsm.service_providers', 'users.service_provider_id', '=', 'fsm.service_providers.id') // Join with service_providers
            ->leftJoin('auth.model_has_roles', 'users.id', '=', 'auth.model_has_roles.model_id') // Join with roles
            ->leftJoin('auth.roles', 'auth.roles.id', '=', 'model_has_roles.role_id') // Join with roles
            ->where('roles.name','!=','Super Admin')
            ->whereNull('users.deleted_at'); // Exclude deleted users
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Users.csv')
            ->addRowWithStyle($columns, $style); // Top row of excel

        // Chunk the query to avoid memory issues
        $query->chunk(5000, function ($users) use ($writer) {
            // Format the result to include the treatment plant name, help desk name, and service provider name
            $dataToExport = $users->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->gender,
                    $user->username,
                    $user->email,
                    $user->treatment_plant_name ?? '', // Treatment plant name
                    $user->help_desk_name ?? '', // Help desk name
                    $user->service_provider_name ?? '', // Service provider name
                    $user->user_type,
                    $user->status == 1 ? 'Active' : 'Not Active' // Convert status to "Active" or "Not Active"
                ];
            });

            // Write the rows to the CSV
            $writer->addRows($dataToExport->toArray());
        });

        // Close the CSV writer
        $writer->close();
    }



    protected function terminateOtherSessions($user)
    {
        // Keep the current session ID
        $currentSessionId = session()->getId();
        // Delete all other sessions for the user
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }
}
