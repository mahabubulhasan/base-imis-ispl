{{--
// Last Modified: 2026-03-04
// Developed By: Streams Tech Ltd.
// Description: Renders landing page About tab sections and feature/module content.
--}}

<!-- ABOUT TAB -->
<div id="about" class="tab-content p-5 md:p-10 min-h-[calc(100vh-100px)] animate-fadeIn">
    <div class="max-w-6xl mx-auto space-y-8">
        <!-- ABOUT SECTION -->
        <div class="bg-white p-6 md:p-10 rounded-xl shadow-lg border border-slate-200">
            <div class="section-title text-center">
                <h3 class="text-slate-900">About <span class="text-primary">IMIS</span></h3>
            </div>
            <div class="text-left space-y-6">
                <p class="text-slate-700 leading-relaxed">
                    IMIS is an open-source GIS-based Digital Public Infrastructure (DPI) which functions as both a
                    municipal information system and a software solution, integrating data, processes, and services
                    to enhance municipal governance—particularly in sanitation management with Citywide Inclusive
                    Sanitation (CWIS) approach to achieve SDG 6.2. It offers municipalities data-driven
                    decision-making tools to strengthen governance across various sectors. By leveraging open-source
                    technologies and Geographic Information Systems (GIS), it facilitates:
                </p>

                <ul class="text-slate-700 list-disc pl-8 space-y-1">
                    <li>Planning, management, and monitoring of sanitation systems using the CWIS approach.</li>
                    <li>End-to-end FSM (Faecal Sludge Management) service chain oversight, including real-time data
                        tracking.</li>
                    <li>Generation and visualization of CWIS indicators for performance assessment.</li>
                    <li>Intuitive dashboards for tracking CWIS indicators, Key Performance Indicators (KPIs), and
                        other essential municipal governance metrics.</li>
                </ul>

                <p class="text-slate-700 leading-relaxed">
                    IMIS as a sub-national public data system contributes to national-level monitoring by feeding data
                    into centralized systems, supporting CWIS indicators and other critical metrics for achieving
                    sanitation targets. Beyond sanitation management, with its modular and scalable design, Base IMIS
                    empowers local authorities by providing a unified, data-driven framework that enhances efficiency,
                    accountability, and service delivery in municipal governance.
                </p>
            </div>
        </div>

        <!-- CWIS SECTION -->
        <div class="bg-white p-6 md:p-10 rounded-xl shadow-lg border border-slate-200">
            <div class="section-title">
                <h3 class="text-slate-900">Citywide Inclusive Sanitation <span class="text-primary">(CWIS)</span></h3>
            </div>
            <div class="text-left">
                <p class="text-slate-700">
                    CWIS is an approach to achieve SDG 6.2 for safe, equitable and financially viable sanitation
                    systems and services. CWIS ensures everyone in a city has access to safely managed sanitation,
                    and human waste is safely managed along the whole sanitation service chain ensuring protection
                    of the environment and human health.
                </p>
                <div class="flex justify-center my-6">
                    <img src="{{ asset('img/svg/landing-page/cwis.jpg') }}" alt="CWIS" class="max-w-full h-auto rounded-lg shadow-lg">
                </div>
                <p class="text-slate-700">CWIS approach focuses on service provision and its enabling environment rather than on building
                    infrastructure, therefore, reliable data is the key success factor for CWIS. UN Water SDG 6
                    global acceleration framework has also identified data and information as one of the five
                    accelerators of SDG 6 outcomes.</p>
            </div>
        </div>

        <!-- FEATURES SECTION -->
        <div class="bg-white p-6 md:p-10 rounded-xl shadow-lg border border-slate-200">
            <div class="section-title text-center">
                <h3 class="text-slate-900">Features of <span class="text-primary">IMIS</span></h3>
            </div>
            <div class="text-left">
                <ul class="text-slate-700 list-disc pl-8 space-y-1 leading-relaxed">
                    <li>Spatial context for municipal data - infrastructure, services, and resources</li>
                    <li>Efficient storage and management of municipal data, including infrastructure and essential
                        services</li>
                    <li>Integration of CWIS data to support planning, management, and evaluation of sanitation
                        systems and services</li>
                    <li>Decision support tools for decision-making based on spatial analysis and modelling</li>
                    <li>Real-time dashboard for monitoring KPIs and CWIS indicators</li>
                    <li>User-friendly interfaces with access control features</li>
                    <li>Scalability to adapt to the evolving technology and information needs</li>
                    <li>Mainstreaming CWIS service chain into the city's business process</li>
                    <li>Interoperable with external data sources, including tax/revenue, public health, emergency
                        response data and more</li>
                    <li>Robust security measures to safeguard sensitive data, ensuring city data privacy compliance
                    </li>
                </ul>
            </div>
        </div>

        <!-- FUNCTIONAL MODULES SECTION -->
        <div class="bg-white p-6 md:p-10 rounded-xl shadow-lg border border-slate-200">
            <div class="section-title">
                <h3 class="text-slate-900">Functional<span class="text-primary"> Modules</span></h3>
            </div>

            <!-- Grid of modules -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Building Information Management System -->
                <div class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/buildingIMS.svg') }}" alt="Building Icon" class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">Building Information Management System</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>Maintains information about all existing and new buildings with their building footprints, sanitation system, socio-economic condition, etc</li>
                        <li>Maintains information about low-income communities with their geographic coverage and sanitation system</li>
                    </ul>
                </div>

                <!-- Property Tax Collection Support System -->
                <div class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/propertyTaxCollectionIMS.svg') }}" alt="Property Tax Icon" class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">Property Tax Collection Support System</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>Enables import of property tax or other revenue data into IMIS for spatial visualization of buildings or containments with their tax or revenue collection status</li>
                    </ul>
                </div>

                <!-- Urban Management Decision Support System -->
                <div class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/urbanManagementDSS.svg') }}" alt="Urban Management Icon" class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">Urban Management Decision Support System</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>Dashboard for monitoring the situation of sanitation and other elements required for planning, management and monitoring and evaluation of CWIS</li>
                        <li>Dashboards for monitoring KPIs and CWIS indicators</li>
                        <li>Tools for real-time monitoring of the sanitation service chain</li>
                        <li>Spatial analysis tools</li>
                        <li>Query and attribute analysis tools</li>
                        <li>Basic navigation tools for exploration, analysis, and visualization of spatial data within a GIS environment and tools for printing maps</li>
                    </ul>
                </div>

                <!-- Utility Information Management System -->
                <div class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/utilityIMS.svg') }}" alt="Utility Icon" class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">Utility Information Management System</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>Maintains road network information</li>
                        <li>Maintains water supply network information</li>
                        <li>Maintains sewerage network information</li>
                        <li>Maintains drainage network information</li>
                    </ul>
                </div>

                <!-- Solid Waste Information Support System -->
                <div class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/swmPaymentStatus.svg') }}" alt="Solid Waste Icon" class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">Solid Waste Information Support System</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>Enables import of solid waste management data into the system for spatial visualization of buildings with their solid waste management status</li>
                    </ul>
                </div>

                <!-- Water Supply Information Support System -->
                <div class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/watersupplyISS.svg') }}" alt="Water Supply Icon" class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">Water Supply Information Support System</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>Enables import of water supply bill payment data into the system for spatial visualization of buildings with their bill payment status</li>
                    </ul>
                </div>

                <!-- Fecal Sludge Information Management System -->
                <div class="border border-slate-200 rounded-lg p-5 hover:shadow-lg hover:shadow-primary/10 transition-shadow duration-300 bg-slate-50 lg:col-span-1">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/svg/landing-page/fecalSludgeIMS.svg') }}" alt="Fecal Sludge Icon" class="h-16 w-16 mx-auto mb-3">
                        <h5 class="text-lg font-semibold text-slate-900">Fecal Sludge Information Management System</h5>
                    </div>
                    <ul class="list-disc list-inside space-y-2 text-slate-700 text-sm text-left">
                        <li>Maintains information about all containments with their geographic location</li>
                        <li>Maintains information about FSM service providers and their resources</li>
                        <li>Maintains information about the Fecal Sludge Treatment Plant and the FS disposed records</li>
                        <li>Maintains the quality test record of treated wastewater and compost generated from the treatment plant</li>
                        <li>Maintains records of services from containment emptying to transport, and desludging of FS in the treatment plant</li>
                        <li>Maintains the customer feedback data</li>
                    </ul>
                </div>

            </div>
        </div>

    </div>
</div>
