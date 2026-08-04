{{--
// Last Modified: 2026-08-04
// Developed By: Streams Tech Ltd.
// Description: Renders landing page About tab sections and feature/module content with language support.
--}}

<!-- ABOUT TAB -->
<div id="about" class="tab-content p-5 md:p-10 min-h-[calc(100vh-100px)] animate-fadeIn">
    <div class="max-w-6xl mx-auto space-y-8">
        <!-- ABOUT SECTION -->
        <div class="bg-white p-6 md:p-10 rounded-xl shadow-lg border border-slate-200">
            <div class="section-title text-center">
                <h3 class="text-slate-900">{{ __('about.page_title') }} <span class="text-primary">IMIS</span></h3>
            </div>
            <div class="text-left space-y-6">
                <p class="text-slate-700 leading-relaxed">
                    {{ __('about.about_description') }}
                </p>

                <ul class="text-slate-700 list-disc pl-8 space-y-1">
                    <li>{{ __('about.features.feature_1') }}</li>
                    <li>{{ __('about.features.feature_2') }}</li>
                    <li>{{ __('about.features.feature_3') }}</li>
                    <li>{{ __('about.features.feature_4') }}</li>
                </ul>

                <p class="text-slate-700 leading-relaxed">
                    {{ __('about.additional_info') }}
                </p>
            </div>
            <div class="flex items-center justify-between bg-slate-100 rounded-lg px-5 py-3 mt-6">
                <div class="copyright text-sm text-slate-700">
                    <strong>Base IMIS © 2022-{{ now()->format('Y') }} {{ __('about.copyright') }} <a
                            href="http://www.innovativesolution.com.np">
                            {{ __('about.copyright_ispl') }}</a> & <a href="https://www.gwsc.ait.ac.th/">{{ __('about.copyright_gwsc_ait') }}</a> {{ __('about.copyright_license') }} <a
                            href="https://creativecommons.org/licenses/by-nc-sa/4.0/?ref=chooser-v1">{{ __('about.copyright_license_link') }}
                        </a>
                    </strong>
                </div>
                <div class="credits text-xs text-slate-500">
                    Developed by
                    <a href="https://innovativesolution.com.np/">Innovative Solution Pvt. Ltd.</a>
                </div>
            </div>
        </div>

        <!-- CWIS SECTION -->
        <div class="bg-white p-6 md:p-10 rounded-xl shadow-lg border border-slate-200">
            <div class="section-title">
                <h3 class="text-slate-900">{{ __('about.cwis_title') }} <span class="text-primary">{{ __('about.cwis_acronym') }}</span></h3>
            </div>
            <div class="text-left">
                <p class="text-slate-700">
                    {{ __('about.cwis_description') }}
                </p>
                <div class="flex justify-center my-6">
                    <img src="{{ asset('img/svg/landing-page/cwis.jpg') }}" alt="CWIS"
                        class="max-w-full h-auto rounded-lg shadow-lg">
                </div>
                <p class="text-slate-700">{{ __('about.cwis_focus') }}</p>
            </div>
        </div>

        <!-- FEATURES SECTION -->
        <div class="bg-white p-6 md:p-10 rounded-xl shadow-lg border border-slate-200">
            <div class="section-title text-center">
                <h3 class="text-slate-900">{{ __('about.features_title') }} <span class="text-primary">IMIS</span></h3>
            </div>
            <div class="text-left">
                <ul class="text-slate-700 list-disc pl-8 space-y-1 leading-relaxed">
                    @foreach(__('about.features_list') as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- FUNCTIONAL MODULES SECTION -->
        <div class="bg-white p-6 md:p-10 rounded-xl shadow-lg border border-slate-200">
            <div class="section-title">
                <h3 class="text-slate-900">{{ __('about.modules_title') }}<span class="text-primary"> {{ __('about.modules_subtitle') }}</span></h3>
            </div>

            <!-- Grid of modules -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Building Information Management System -->
                <div
                    class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/buildingIMS.svg') }}" alt="Building Icon"
                            class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">{{ __('about.module_building_title') }}</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>{{ __('about.module_building_desc_1') }}</li>
                        <li>{{ __('about.module_building_desc_2') }}</li>
                    </ul>
                </div>

                <!-- Property Tax Collection Support System -->
                <div
                    class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/propertyTaxCollectionIMS.svg') }}"
                            alt="Property Tax Icon" class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">{{ __('about.module_property_tax_title') }}</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>{{ __('about.module_property_tax_desc') }}</li>
                    </ul>
                </div>

                <!-- Urban Management Decision Support System -->
                <div
                    class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/urbanManagementDSS.svg') }}"
                            alt="Urban Management Icon" class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">{{ __('about.module_urban_mgmt_title') }}</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>{{ __('about.module_urban_mgmt_desc_1') }}</li>
                        <li>{{ __('about.module_urban_mgmt_desc_2') }}</li>
                        <li>{{ __('about.module_urban_mgmt_desc_3') }}</li>
                        <li>{{ __('about.module_urban_mgmt_desc_4') }}</li>
                        <li>{{ __('about.module_urban_mgmt_desc_5') }}</li>
                        <li>{{ __('about.module_urban_mgmt_desc_6') }}</li>
                    </ul>
                </div>

                <!-- Utility Information Management System -->
                <div
                    class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/utilityIMS.svg') }}" alt="Utility Icon"
                            class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">{{ __('about.module_utility_title') }}</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>{{ __('about.module_utility_desc_1') }}</li>
                        <li>{{ __('about.module_utility_desc_2') }}</li>
                        <li>{{ __('about.module_utility_desc_3') }}</li>
                        <li>{{ __('about.module_utility_desc_4') }}</li>
                    </ul>
                </div>

                <!-- Solid Waste Information Support System -->
                <div
                    class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/swmPaymentStatus.svg') }}" alt="Solid Waste Icon"
                            class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">{{ __('about.module_swm_title') }}</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>{{ __('about.module_swm_desc') }}</li>
                    </ul>
                </div>

                <!-- Water Supply Information Support System -->
                <div
                    class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/watersupplyISS.svg') }}" alt="Water Supply Icon"
                            class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">{{ __('about.module_water_supply_title') }}</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>{{ __('about.module_water_supply_desc') }}</li>
                    </ul>
                </div>

                <!-- Fecal Sludge Information Management System -->
                <div
                    class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50 lg:col-span-1">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/fecalSludgeIMS.svg') }}" alt="Fecal Sludge Icon"
                            class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">{{ __('about.module_fsm_title') }}</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>{{ __('about.module_fsm_desc_1') }}</li>
                        <li>{{ __('about.module_fsm_desc_2') }}</li>
                        <li>{{ __('about.module_fsm_desc_3') }}</li>
                        <li>{{ __('about.module_fsm_desc_4') }}</li>
                        <li>{{ __('about.module_fsm_desc_5') }}</li>
                        <li>{{ __('about.module_fsm_desc_6') }}</li>
                    </ul>
                </div>

            </div>
        </div>

    </div>
</div>