<?php

namespace App\Http\Controllers\Fsm;

use Illuminate\Support\Facades\Crypt;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Cwis\cwis_mne;
use App\Models\Fsm\Nsd;

class NsdDashboardController extends Controller
{
    protected $client;
    private $bearerToken;
    private $email;
    private $password;
    private $apiLoginUrl;
    private $apiPostUrl;
    private $city_db;

    public function __construct()
    {
        $this->email = NSD::pluck('nsd_username')->first();
        $this->password = NSD::pluck('nsd_password')->first();
        $this->city_db = NSD::pluck('city')->first();
        $this->apiLoginUrl = NSD::pluck('api_login_url')->first();
        $this->apiPostUrl = NSD::pluck('api_post_url')->first();
        if ($this->password) {
            $this->password = Crypt::decryptString($this->password);
            $this->password = unserialize($this->password);
        }
    }

    public function pushToNsd($year)
    {
        if (empty($year)) {
            return redirect("cwis/cwis/getall")->with('error', 'Please select a year.');
        }

        // Validate if the year exists in CWIS records
        $all_years = cwis_mne::pluck('year')->toArray();
        if (!in_array($year, $all_years)) {
            return redirect()->back()->with('error', 'Please select a valid year from the dropdown.');
        }

        // Get authentication token for NSD
        $bearerToken = $this->getBearerToken($this->password);
        if (empty($bearerToken)) {
            return redirect()->back()->with('error', 'Login to NSD failed. Please Check your NSD Integration Setting.');
        }

        // Fetch all published years and check if the selected year is already published
        $publishedYears = $this->checkNsdStatus($this->city_db);       
        if (!empty($publishedYears) && isset($publishedYears[0]['published_years'])) {
            if (in_array($year, $publishedYears[0]['published_years'])) {
                return redirect()->back()->with('error', "Data for the selected year $year has already been published.");
            }
        }

        // Fetch CWIS data for the selected year
        $cwis = $this->getCwisData($year);
        if (empty($cwis)) {
            return redirect()->back()->with('error', 'Failed to retrieve CWIS data for the selected year.');
        }
        
        $response = $this->postToNSD($cwis, $year);

        // Handle API response
        if (isset($response['error'])) {
            return redirect()->back()->with('error', $response['error']);
        }

        return redirect()->back()->with('success', "CWIS data for year $year pushed successfully to NSD! Please use the 'Check Status' button to verify the status.");
    }


    public function getBearerToken($credentials = null)
    {   
       
        try {
            if (!empty($this->bearerToken)) {
                return $this->bearerToken;
            }
            $email = $credentials['nsd_username'] ?? $this->email;
            $password = $credentials['nsd_password'] ?? $this->password;
            $apiLoginUrl = $credentials['api_login_url'] ?? $this->apiLoginUrl;

            if (empty($this->email) || empty($this->password) || empty($this->apiLoginUrl)) {
                throw new \Exception("Missing required configuration for authentication.");
            }
            // Proceed with authentication logic
            $client = new Client([
                'base_uri' => $apiLoginUrl,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);

            $rawData = json_encode([
                'email' => $email,
                'password' => $password,
                'grant_type' => 'password',
            ]);
            $response = $client->post('api/v1/auth/authenticate', [
                'body' => $rawData,
            ]);

            $responseData = json_decode((string) $response->getBody(), true);
            $this->bearerToken = $responseData['access_token'];

            return $this->bearerToken;
        } catch (\Exception $e) {
            \Log::error('Error fetching bearer token: ' . $e->getMessage());
            return null;
        }
    }

    public function getCwisData($year)
    {   
        try {
            $data = cwis_mne::SELECT('indicator_code as cwisCode','label as name','outcome','data_value as value')->where('year', $year)->get()->toArray();
            return $data;
           
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error fetching CWIS data: ' . $e->getMessage());
            return null; // Return null or a default error message
        }
        
    }

    public function postToNSD($cwis, $year)
    {
        $year = (integer)$year;
        $cwis_post_data = [
               "city"=> $this->city_db,
                "year"=> $year,
                "indicators"=> $cwis
        ];
        // Initialize the HTTP client
        try {
            $client = new Client([
                'base_uri' => $this->apiPostUrl,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . trim($this->bearerToken),
                ],
                'timeout' => 10,
            ]);

            $response = $client->post('api/v1/imis/indicators', [
                'body' => json_encode($cwis_post_data),
            ]);

            $responseData = json_decode($response->getBody(), true);
            return $responseData;
        } catch (ConnectException $e) {
            return ['error' => 'NSD Service not available, Please Check with your IT Admin.'];
        } catch (ClientException $e) {

            $status = $e->getResponse()->getStatusCode();
            if ($status == 401) {
                return ['error' => 'Selected City Not found. Please check your NSD Integration Setting.'];
            } else {
                return ['error' => "Client error occurred (HTTP $status). Please check the request."];
            }
        } catch (RequestException $e) {
            // Other errors (fallback)
            return ['error' => 'Request error: ' . $e->getMessage()];
        }
    }

    public function checkNsdStatus($city = null, $apiPostUrl = null)
{
    try {
        $token = $this->getBearerToken($this->password); 

        if (!$token) {
            throw new \Exception("Failed to retrieve bearer token.");
        }

        $client = new Client([
            'base_uri' => $apiPostUrl ?? $this->apiPostUrl, // Use passed-in value or fallback
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . trim($token),
                'X-Requested-With' => 'XMLHttpRequest',
                'Host' => parse_url($apiPostUrl ?? $this->apiPostUrl, PHP_URL_HOST),
            ],
        ]);

        $cities = is_array($city) ? $city : [$city ?? $this->city_db];

        foreach ($cities as $city) {
            $response = $client->get('api/v1/imis/indicators/metadata', [
                'query' => [
                    'city' => $city,
                    'published_years' => "",
                    'draft_year' => "",
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            $filteredData = array_filter($responseData, function ($item) use ($city) {
                return isset($item['city']) && $item['city'] === $city;
            });

            return array_values($filteredData);
        }

        return [];
    } catch (\Exception $e) {
        \Log::error('Error fetching NSD status: ' . $e->getMessage());
        return ['error' => 'Failed to fetch NSD status.'];
    }
}

}