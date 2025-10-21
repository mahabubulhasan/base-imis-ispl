<?php

namespace App\Http\Controllers\SewerConnection;
use App\Models\BuildingInfo\BuildContain;
use App\Models\BuildingInfo\Building;
use App\Models\SewerConnection\SewerConnection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SewerConnection\SewerConnectionService;
class SewerConnectionController extends Controller
{
    protected SewerConnectionService $sewerconnection;
    public function __construct(SewerConnectionService $sewerconnection)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Sewer Connection', ['only' => ['index']]);
        $this->middleware('permission:Delete Sewer Connection', ['only' => ['destroy']]);
        $this->sewerconnection = $sewerconnection;
    }

    public function index()
    {
        $page_title = __("Sewer Connection");
        return view('sewer-connection/index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        $data = $request->all();
        return $this->sewerconnection->getAllData($data);
    }

    public function approvesewer($bin)
    {
        return $this->sewerconnection->approve($bin);
    }


    public function geombin($bin)
    {
        return $this->sewerconnection->getGeom($bin);
    }
    public function geomsewer($sewer)
    {
        return $this->sewerconnection->getsewerGeom($sewer);
    }
    public function destroy($id)
    {
        $sewerconnection = SewerConnection::find($id);

        if ($sewerconnection) {

                $sewerconnection->delete();
                return redirect('sewerconnection/sewerconnection')->with('success',__('Sewer connection deleted Successfully.'));


        } else {
            return redirect('sewerconnection/sewerconnection')->with('error',__('Failed to delete Sewer connection.'));
        }
    }

}
