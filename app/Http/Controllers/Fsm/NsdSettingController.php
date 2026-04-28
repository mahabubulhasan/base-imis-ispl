<?php

namespace App\Http\Controllers\Fsm;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Fsm\Nsd;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Fsm\NsdDashboardController;
use Illuminate\Support\Facades\Log;

class NsdSettingController extends Controller
{
    public function index()
    {
        $nsd = Nsd::first();
        if (!$nsd) {
            return redirect()->route('nsd-setting.create');
        }
        return redirect()->route('nsd-setting.edit', $nsd->id);
    }

    public function create()
    {
        $page_title = 'National Sanitation Dashboard Integration Settings';
        return view('fsm.nsd-setting.create', compact('page_title'));
    }

    public function edit($id)
    {
        $page_title = 'National Sanitation Dashboard Integration Settings';
        $nsd= NSD::findOrFail($id);
        return view('fsm.nsd-setting.edit', compact('nsd', 'page_title'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nsd_username' => 'required|string',
            'nsd_password' => 'required|string',
            'city' => 'required|string',
            'api_post_url' => 'required|url',
            'api_login_url' => 'required|url',
        ], [
            'nsd_username.required' => 'The Username is required.',
            'nsd_password.required' => 'The Password is required.',
            'city.required' => 'The City is required.',
            'api_post_url.required' => 'The URL TO Send Data is required.',
            'api_post_url.url' => 'The URL To Send Data must be a valid URL.',
            'api_login_url.required' => 'The URL To Authenticate is required.',
            'api_login_url.url' => 'The URL To Authenticate must be a valid URL.',
        ]);

        $req_data = [
            'nsd_username'   => $data['nsd_username'],
            'nsd_password'   => !empty($data['nsd_password']) ? Crypt::encrypt($data['nsd_password']) : null,
            'city'           => $data['city'],
            'api_post_url'   => $data['api_post_url'],
            'api_login_url'  => $data['api_login_url'],
        ]; // TODO remove it

        $nsd = NSD::create([
            'nsd_username'   => $data['nsd_username'],
            'nsd_password'   => !empty($data['nsd_password']) ? Crypt::encrypt($data['nsd_password']) : null,
            'city'           => $data['city'],
            'api_post_url'   => $data['api_post_url'],
            'api_login_url'  => $data['api_login_url'],
        ]);

        // Log::debug(json_encode($req_data));

        $credentials = [
            'nsd_username' => $data['nsd_username'],
            'nsd_password' => !empty($data['nsd_password']) ? $data['nsd_password'] : Crypt::decrypt($nsd->nsd_password),
            'api_login_url' => $data['api_login_url'],
        ];

        // Log::debug(json_encode($credentials));

        $dashboardController = new NsdDashboardController();
        $bearerToken = $dashboardController->getBearerToken($credentials);

        if (!$bearerToken) {
            return redirect()->back()
                ->withInput($request->all())
                ->with('error', 'Invalid username, password, or URL For Authentication.');
        }

        // Log::debug('Bearer Token: ' . $bearerToken);

        $city = $data['city'];
        $checkStatus = $dashboardController->checkNsdStatus($city);
        Log::debug($city . ' Status: ' . json_encode($checkStatus));

        if (!$checkStatus) {
            return redirect()->back()
                ->withInput($request->all())
                ->with('error', 'Invalid City or URL To Send Data.');
        }

        // Save the data if both checks pass
        $nsd->nsd_username = $data['nsd_username'];
        if (!empty($data['nsd_password'])) {
            $nsd->nsd_password = Crypt::encrypt($data['nsd_password']);
        }
        $nsd->city = $data['city'];
        $nsd->api_post_url = $data['api_post_url'];
        $nsd->api_login_url = $data['api_login_url'];
        $nsd->save();

        return redirect()->route('nsd-setting.edit', $nsd->id)->with('success', 'National Sanitation Dashboard Credentials created successfully');
    }

    public function update(Request $request, $id)
    {

        $data = $request->validate([
            'nsd_username' => 'required|string',
            'city' => 'required|string',
            'api_post_url' => 'required|url',
            'api_login_url' => 'required|url',
        ], [
            'nsd_username.required' => 'The Username is required.',
            'city.required' => 'The City is required.',
            'api_post_url.required' => 'The URL To Send Data is required.',
            'api_post_url.url' => 'The URL To Send Data must be a valid URL.',
            'api_login_url.required' => 'The URL To Authenticate is required.',
            'api_login_url.url' => 'The URL To Authenticate must be a valid URL.',
        ]);

        $nsd = NSD::findOrFail($id);

        $credentials = [
            'nsd_username' => $data['nsd_username'],
            'nsd_password' => $request->get('nsd_password') ?? Crypt::decrypt($nsd->nsd_password),
            'api_login_url' => $data['api_login_url'],
        ];

        $dashboardController = new NsdDashboardController();
        $bearerToken = $dashboardController->getBearerToken($credentials);

        if (!$bearerToken) {
            return redirect()->back()
                ->withInput($request->all())
                ->with('error', 'Invalid Username, Password, or URL For Authentication.');
        }

        $checkStatus = $dashboardController->checkNsdStatus(
            $data['city'],
            $data['api_post_url']
        );

        if (!($checkStatus) || isset($checkStatus['error'])) {
            return redirect()->back()
                ->withInput($request->all())
                ->with('error', 'Invalid City or URL To Send Data.');
        }

        // Save the data if both checks pass
        $nsd->nsd_username = $data['nsd_username'];
        if (!empty($data['nsd_password'])) {
            $nsd->nsd_password = Crypt::encrypt($data['nsd_password']);
        }
        $nsd->city = $data['city'];
        $nsd->api_post_url = $data['api_post_url'];
        $nsd->api_login_url = $data['api_login_url'];
        $nsd->save();

        return redirect()->route('nsd-setting.edit', $id)
            ->with('success', 'National Sanitation Dashboard settings updated successfully');
        }

}
