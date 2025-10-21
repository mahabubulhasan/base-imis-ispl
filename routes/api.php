<?php

use App\Http\Controllers\Api\ApiServiceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BuildingSurveyController;
use App\Http\Controllers\Api\SewerConnectionController;
use App\Http\Controllers\Api\EmptyingServiceController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\BuildingInfo\BuildingController;
use App\Http\Controllers\BuildingSearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
|
| Login--------------------------------------------------------------------
|
| An API route for logging in to the application.
|
*/
Route::get('get-Building-sewercode/{sewercode}',[BuildingSearchController::class,'getSewerCode']);
Route::post('/login', [AuthController::class, 'login']);

/*
|
| Protected API Routes ----------------------------------------------------
|
| All API routes in this group are protected by the auth:sanctum middleware
|
*/
Route::group([
    'name' => 'protected-api-routes',
    'middleware' => 'auth:sanctum'
],function (){
    /*
    |
    | Logout---------------------------------------------------------------
    |
    | An API route for logging out of the application.
    |
    */
    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |
    | API Service Routes---------------------------------------------------
    |
    | All common API routes are grouped here.
    | /application/{id} : Get Application with ID of {id}
    | /containment/{application-id} : Get Containment(s) of application with
    |                                 ID of {application-id}
    | /service-providers : Get list of service providers
    |
    */
    Route::group(['name' => 'apiService'],function (){
        Route::get('/application/{id}',[ApiServiceController::class,'getApplicationDetails']);
        Route::get('/containment/{application_id}',[ApiServiceController::class,'getContainmentDetails']);
        Route::get('/service-providers',[ApiServiceController::class,'getServiceProviders']);
    });

    /*
    |
    | Emptying Routes---------------------------------------------------
    |
    | /assessed-applications : Get list of assessed applications
    | /treatment-plants : Get list of treatment plants
    | /vacutug-types : Get list of desludging vehicles
    | /drivers : Get list of drivers
    | /emptiers : Get list of emptiers
    | /save-emptying : Save the emptying data
    |
    */
    Route::group(['name' => 'emptyingService'],function (){
        Route::get('/assessed-applications',[EmptyingServiceController::class,'getAssessedApplications']);
        Route::get('/treatment-plants',[EmptyingServiceController::class,'getTreatmentPlants']);
        Route::get('/vacutugs',[EmptyingServiceController::class, 'getVacutugs']);
        Route::get('/drivers',[EmptyingServiceController::class,'getDrivers']);
        Route::get('/emptiers',[EmptyingServiceController::class,'getEmptiers']);
        Route::post('/save-emptying',[EmptyingServiceController::class,'save']);
    });

    /*
    |
    | Building Survey Routes---------------------------------------------------
    |
    | /wms/buildings : Get WMS layer of buildings
    | /wms/containments : Get WMS layer of containments
    | /wms/wards : Get WMS layer of wards
    | /wms/roads' : Get WMS layer of roads
    | /save-building : Save the building survey
    | /save-containment : Save the containment survey
    |
    */
    Route::group(['name' => 'buildingSurvey'],function (){
        Route::group(['name' => 'wms','prefix' => 'wms'],function (){
            Route::get('/buildings',[BuildingSurveyController::class,'getBuildingWms']);
            Route::get('/containments',[BuildingSurveyController::class,'getContainmentWms']);
            Route::get('/wards',[BuildingSurveyController::class,'getWardWms']);
            Route::get('/roads',[BuildingSurveyController::class,'getRoadWms']);
        });
        Route::post('/save-building',[BuildingSurveyController::class,'saveBuilding']);
        Route::post('/save-containment',[BuildingSurveyController::class,'saveContainment']);
    });
    Route::group(['name' => 'sewerConnection'],function (){
        Route::get('/buildingcode',[BuildingSurveyController::class,'getBuildingCodes']);
        Route::get('/sewercode',[BuildingSurveyController::class,'getSewerCodes']);
        Route::group(['name' => 'wms','prefix' => 'wms'],function (){
            Route::get('/sewers',[SewerConnectionController::class,'getSewerWms']);

        });
        Route::post('/save-sewerconnection',[SewerConnectionController::class,'saveSewerConnections']);

    });
    Route::group([
        'name' => 'language',
        'prefix' => 'language',
        'namespace' => 'Language',
        'middleware' => 'auth'
    ], function () {

        Route::get('languages', [LanguageController::class, 'headerDropdown']);
        Route::get('translations/{lang_id}', [LanguageController::class, 'getTranslation']);
    });


    Route::prefix('revamp')->group(function() {
        Route::get('/collection', [BuildingSearchController::class,'getAll']);
        Route::get('get-Building-bin/{bin}',[BuildingSearchController::class,'getBuildingBin']);
        Route::get('get-Building-roadcode/{roadcode}',[BuildingSearchController::class,'getBuildingRoadcode']);
        Route::get('get-Building-housenumber/{housenumber}',[BuildingSearchController::class,'getBuildingHouseNumber']);
        Route::get('get-Building-sewercode/{sewercode}',[BuildingSearchController::class,'getSewerCode']);
        Route::get('get-Building-preconnected/{housenumber}',[BuildingSearchController::class,'getBinOfPreconnectedBuilding']);
        Route::get('get-Building-sanitation/{sanitation}',[BuildingSearchController::class,'getSanitationSystem']);
        Route::get('get-Building-housenumber/{housenumber}',[BuildingSearchController::class,'getBuildingHouseNumber']);

        });

});
