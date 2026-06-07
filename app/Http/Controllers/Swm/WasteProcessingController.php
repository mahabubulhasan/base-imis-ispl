<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Swm\Concerns\HandlesSwmExcelImport;
use App\Http\Requests\Swm\WasteProcessingRequest;
use App\Imports\Swm\WasteProcessingImport;
use App\Models\Swm\WasteProcessingLog;
use App\Services\Swm\WasteProcessingService;
use Illuminate\Http\Request;

class WasteProcessingController extends Controller
{
    use HandlesSwmExcelImport;

    public function __construct(
        protected WasteProcessingService $wasteProcessingService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:List SW Waste Processing', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Waste Processing', ['only' => ['show']]);
        $this->middleware('permission:Add SW Waste Processing', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Waste Processing', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Waste Processing', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Waste Processing to Excel', ['only' => ['export', 'downloadTemplate']]);
        $this->middleware('permission:Import SW Waste Processing From Excel', ['only' => ['importForm', 'importStore']]);
        $this->middleware('permission:View SW Waste Processing History', ['only' => ['history']]);
    }

    public function index()
    {
        $page_title = __('Waste Processing');

        return view('swm.service-management.waste-processing.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->wasteProcessingService->getAll($request->all());
    }

    public function create()
    {
        $page_title = __('Add Waste Processing Log');
        $wasteProcessingLog = null;

        return view('swm.service-management.waste-processing.create', compact(
            'page_title',
            'wasteProcessingLog'
        ));
    }

    public function store(WasteProcessingRequest $request)
    {
        $id = $this->wasteProcessingService->storeOrUpdate(null, $request->validated());
        if ($id === null) {
            return redirect()->back()->withInput()->with('error', __('Failed to create waste processing log.'));
        }

        return redirect()->route('swm.waste-processing.index')->with('success', __('Waste processing log created successfully.'));
    }

    public function show(WasteProcessingLog $waste_processing)
    {
        $page_title = __('Waste Processing Details');
        $wasteProcessingLog = $waste_processing;

        return view('swm.service-management.waste-processing.show', compact('page_title', 'wasteProcessingLog'));
    }

    public function edit(WasteProcessingLog $waste_processing)
    {
        $page_title = __('Edit Waste Processing');
        $wasteProcessingLog = $waste_processing;

        return view('swm.service-management.waste-processing.edit', compact(
            'page_title',
            'wasteProcessingLog'
        ));
    }

    public function update(WasteProcessingRequest $request, WasteProcessingLog $waste_processing)
    {
        $id = $this->wasteProcessingService->storeOrUpdate((int) $waste_processing->id, $request->validated());
        if ($id === null) {
            return redirect()->back()->withInput()->with('error', __('Failed to update waste processing log.'));
        }

        return redirect()->route('swm.waste-processing.index')->with('success', __('Waste processing log updated successfully.'));
    }

    public function destroy(WasteProcessingLog $waste_processing)
    {
        $waste_processing->delete();

        return redirect()->route('swm.waste-processing.index')->with('success', __('Waste processing log deleted successfully.'));
    }

    public function history(WasteProcessingLog $waste_processing)
    {
        $page_title = __('Waste Processing History');
        $wasteProcessingLog = $waste_processing;

        return view('swm.service-management.waste-processing.history', compact('page_title', 'wasteProcessingLog'));
    }

    public function export(Request $request)
    {
        return $this->wasteProcessingService->download($request->all());
    }

    public function downloadTemplate()
    {
        $this->wasteProcessingService->downloadTemplate();
    }

    public function importForm()
    {
        return $this->swmImportFormView(
            __('Import Waste Processing from Excel'),
            route('swm.waste-processing.index'),
            'swm.waste-processing.import.store'
        );
    }

    public function importStore(Request $request)
    {
        return $this->swmImportStore(
            $request,
            WasteProcessingImport::class,
            ['entry_at', 'report_date', 'reporting_month'],
            'swm.waste-processing.index',
            'importswm',
            'waste-processing-import'
        );
    }
}
