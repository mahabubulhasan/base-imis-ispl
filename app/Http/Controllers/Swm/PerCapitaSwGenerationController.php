<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\PerCapitaSwGenerationRequest;
use App\Services\Swm\SwmModuleSettingsService;

class PerCapitaSwGenerationController extends Controller
{
    public function __construct(protected SwmModuleSettingsService $settingsService)
    {
        $this->middleware('auth');
        $this->middleware('permission:View SW Per Capita Generation Setting', ['only' => ['edit']]);
        $this->middleware('permission:Edit SW Per Capita Generation Setting', ['only' => ['update']]);
    }

    public function edit()
    {
        $page_title = __('Waste Generation');
        $setting = $this->settingsService->get();

        return view('swm.settings.per-capita-sw-generation', compact('page_title', 'setting'));
    }

    public function update(PerCapitaSwGenerationRequest $request)
    {
        $this->settingsService->update($request->validated());

        return redirect()
            ->route('swm.settings.per-capita-sw-generation.edit')
            ->with('success', __('Settings saved successfully.'));
    }
}
