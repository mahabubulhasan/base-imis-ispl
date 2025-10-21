<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LayerInfo\Ward;

class ExportShpKmlController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:Data Export Map Tools', ['only' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = __("Export to Shape File or KML");
        $wards = Ward::orderBy('ward', 'asc')->pluck('ward', 'ward')->all();
        return view('export-shp-kml.index', compact('page_title', 'wards'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportShape()
    {

    }
}
