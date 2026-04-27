<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\LicRequest;
use App\Models\Swm\Lic;
use App\Services\Swm\LicService;
use Illuminate\Http\Request;

class LicController extends Controller
{
    protected LicService $licService;

    public function __construct(LicService $licService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW LIC', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW LIC', ['only' => ['show']]);
        $this->middleware('permission:Add SW LIC', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW LIC', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW LIC', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW LIC to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW LIC History', ['only' => ['history']]);
        $this->licService = $licService;
    }

    public function index()
    {
        $page_title = __('LIC');

        return view('swm.service-coverage.lic.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->licService->getAllLic($request->all());
    }

    public function create()
    {
        $page_title = __('Add LIC');
        $lic = null;

        return view('swm.service-coverage.lic.create', compact('page_title', 'lic'));
    }

    public function store(LicRequest $request)
    {
        $this->licService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.lic.index')->with('success', __('LIC created successfully.'));
    }

    public function show(Lic $lic)
    {
        $page_title = __('LIC Details');

        return view('swm.service-coverage.lic.show', compact('page_title', 'lic'));
    }

    public function edit(Lic $lic)
    {
        $page_title = __('Edit LIC');

        return view('swm.service-coverage.lic.edit', compact('page_title', 'lic'));
    }

    public function update(LicRequest $request, Lic $lic)
    {
        $this->licService->storeOrUpdate((int) $lic->id, $request->all());

        return redirect()->route('swm.lic.index')->with('success', __('LIC updated successfully.'));
    }

    public function destroy(Lic $lic)
    {
        $lic->delete();

        return redirect()->route('swm.lic.index')->with('success', __('LIC deleted successfully.'));
    }

    public function history(Lic $lic)
    {
        $page_title = __('LIC History');

        return view('swm.service-coverage.lic.history', compact('page_title', 'lic'));
    }

    public function export(Request $request)
    {
        return $this->licService->download($request->all());
    }
}
