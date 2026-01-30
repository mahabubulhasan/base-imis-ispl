<!-- Last Modified Date: 23-12-2024
Developed By: Streams Tech Ltd. -->
<!DOCTYPE html>
<html lang="en" class="overflow-x-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('constants.SITE_NAME') }} - IMIS Portal</title>

    <!-- Tailwind & Leaflet for FSM form -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Chart.js for Dashboard Charts (v2.9.4 for compatibility) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>

    <!-- jQuery for Dashboard Functionality -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap for Dashboard -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AdminLTE for Dashboard Card Widgets -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <!-- Font Awesome for Dashboard Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Select2 for FSM Form -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Cleave.js for input masking -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.0.2/cleave.min.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('layout/css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <style>
        /* FSM Form Custom Styles */
        .app_fieldset {
            border: 1px solid #e2e8f0;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.25rem;
            background: #ffffff;
        }

        .app_fieldset > legend {
            padding: 0 .5rem;
            font-weight: 600;
            color: #0f394c;
            font-size: 1rem;
            width: auto;
        }

        #map {
            height: 360px;
            border-radius: 0.5rem;
        }

        /* Success Modal Styles */
        .fsm-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }

        .fsm-modal-content {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .fsm-modal-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: checkmarkPulse 0.6s ease-out;
        }

        @keyframes checkmarkPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .fsm-modal-icon i {
            color: white;
            font-size: 2.5rem;
        }

        /* Field error states */
        .field-error {
            border-color: #ef4444 !important;
            border-width: 2px !important;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }

        /* Loading spinner for submit button */
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spinner 0.6s linear infinite;
        }

        @keyframes spinner {
            to { transform: rotate(360deg); }
        }

        /* Select2 customization for FSM form */
        .select2-container--default .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
            border: 2px solid #d1d5db;
            border-radius: 0.5rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(1.5em + 0.75rem);
            padding-left: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + 0.75rem);
        }

        @media (max-width: 576px) {
            .app_fieldset {
                padding: 0.75rem;
            }

            .app_fieldset > legend {
                font-size: 0.95rem;
            }
        }
    </style>
</head>

<body class="m-0 font-sans overflow-x-hidden min-h-screen relative"
    style="background: url('{{ asset(config('constants.BACKGROUND_IMAGE_URL')) }}') no-repeat center center fixed; background-size: cover; background-color: #f0f4f8;">
    <div class="h-10 bg-gradient-to-br to-[#343a40] from-[#403a40]">
        <div class="text-white "></div>
    </div>
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-white/85 -z-10"></div>
    <header
        class="px-4 md:px-8 py-3 md:py-4 flex flex-wrap items-center justify-between shadow-lg sticky top-0 z-50 bg-white">
        <div class="flex items-center gap-3 md:gap-4 flex-shrink-0">
            <img src="{{ asset(config('constants.LOGO_URL')) }}" alt="{{ config('constants.SITE_NAME') }} Logo"
                class="h-10 md:h-12 lg:h-14 w-auto transition-transform duration-300 hover:scale-105">
            <h1 class="text-base md:text-xl lg:text-2xl font-semibold tracking-wide whitespace-nowrap">{{
                config('constants.SITE_NAME') }}</h1>
        </div>
        <button
            class="hidden max-md:block text-white text-3xl p-1 bg-transparent border-none cursor-pointer transition-transform duration-300 hover:scale-110 order-3"
            onclick="toggleMenu()" aria-label="Toggle menu">
            ☰
        </button>
        <nav id="navMenu"
            class="w-full md:w-auto order-4 md:order-none max-h-0 md:max-h-none overflow-hidden md:overflow-visible transition-all duration-300 md:transition-none">
            <ul
                class="flex flex-col md:flex-row gap-0 md:gap-6 lg:gap-8 m-0 p-0 list-none items-center md:bg-transparent bg-white/10 md:bg-none rounded-lg md:rounded-none md:p-0 py-2">
                <li class="nav-item cursor-pointer font-medium text-sm lg:text-base px-4 py-3 md:py-2 rounded-md transition-all duration-300 hover:bg-[#3b3a40]/20 w-full md:w-auto text-center whitespace-nowrap relative no-underline"
                    onclick="openTab('about', this)">About</li>
                <li class="nav-item cursor-pointer font-medium text-sm lg:text-base px-4 py-3 md:py-2 rounded-md transition-all duration-300 hover:bg-[#3b3a40]/20 w-full md:w-auto text-center whitespace-nowrap relative no-underline"
                    onclick="openTab('dashboard', this)">Public Dashboard</li>
                <li class="nav-item cursor-pointer font-medium text-sm lg:text-base px-4 py-3 md:py-2 rounded-md transition-all duration-300 hover:bg-[#3b3a40]/20 w-full md:w-auto text-center whitespace-nowrap relative no-underline"
                    onclick="openTab('feedback', this)">Feedback</li>
                <li class="nav-item cursor-pointer font-medium text-sm lg:text-base px-4 py-3 md:py-2 rounded-md transition-all duration-300 hover:bg-[#3b3a40]/20 w-full md:w-auto text-center whitespace-nowrap relative no-underline"
                    onclick="openTab('fsm', this)">FSM Application</li>
                <li class="nav-item cursor-pointer font-medium text-sm lg:text-base px-4 py-3 md:py-2 rounded-md transition-all duration-300 hover:bg-[#3b3a40]/20 w-full md:w-auto text-center whitespace-nowrap relative no-underline"
                    onclick="openTab('contact', this)">Contact</li>
                <li class="nav-item cursor-pointer font-medium text-sm lg:text-base px-4 py-3 md:py-2 rounded-md transition-all duration-300 hover:bg-[#3b3a40]/20 w-full md:w-auto text-center whitespace-nowrap relative no-underline active-tab"
                    onclick="openTab('login', this)">Login</li>
            </ul>
        </nav>
    </header>

    <!-- LOGIN TAB -->
    <div id="login" class="tab-content hidden p-5 md:p-10 bg-transparent min-h-[calc(60vh-100px)] animate-fadeIn">
        <div
            class="flex flex-col md:flex-row items-center justify-center gap-8 md:gap-12 min-h-[calc(70vh-120px)] px-4 py-5">
            <!-- Banner (Left) -->
            <div class="w-full md:flex-1">
                <div class="text-left mb-6 md:mb-8 animate-slideDown" style="margin-top: 5px; margin-bottom: 5px;">
                    <h2 class="text-[#1f3b7d] text-2xl md:text-3xl lg:text-4xl font-bold mb-2 md:mb-3"
                        style="text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); margin-top: 5px; margin-bottom: 5px;">
                        Integrated Municipal Information System (IMIS)</h2>
                    <h4 class="text-sm md:text-base py-[5px]">Secure access to municipal services and information</h4>
                    <p class="text-gray-600 border-l-[5px] border-[#68717c] pl-[3px]">This product was developed under
                        the Inclusive and Integrated Sanitation & Hygiene Project in 10 towns</p>
                </div>
            </div>

            <!-- Login Box (Right) -->
            <div class="md:flex-none">
                <div
                    class="relative rounded-2xl p-6 md:p-10 w-full max-w-[420px] ml-auto animate-scaleIn border border-white/65 shadow-2xl bg-white/45 ring-1 ring-white/55 overflow-hidden">
                    <h3 class="text-[#0056b3] text-xl md:text-2xl mb-5 md:mb-6 text-center font-semibold">Sign In
                    </h3>

                    @if(isset($errors) && count($errors) > 0)
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded">
                        <ul class="list-disc list-inside text-red-700 text-sm">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if(Session::get('success', false))
                    <?php $data = Session::get('success'); ?>
                    @if (is_array($data))
                    @foreach ($data as $msg)
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded">
                        <p class="text-green-700 text-sm">{{ $msg }}</p>
                    </div>
                    @endforeach
                    @else
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded">
                        <p class="text-green-700 text-sm">{{ $data }}</p>
                    </div>
                    @endif
                    @endif

                    <form method="POST" action="{{ route('login.perform') }}">
                        @csrf
                        <input type="text" name="username" placeholder="Username" required aria-label="Username"
                            value="{{ old('username') }}"
                            class="w-full px-4 py-3 md:py-3.5 my-2 md:my-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10 @error('username') border-red-500 @enderror">
                        @error('username')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror

                        <input type="password" name="password" placeholder="Password" required aria-label="Password"
                            class="w-full px-4 py-3 md:py-3.5 my-2 md:my-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10 @error('password') border-red-500 @enderror">
                        @error('password')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror

                        <div
                            class="flex flex-col sm:flex-row justify-between items-start sm:items-center text-sm my-4 md:my-5 gap-3 sm:gap-2">
                            <label class="flex items-center gap-2 cursor-pointer text-gray-600">
                                <input type="checkbox" name="remember" value="1" class="cursor-pointer w-4 h-4">
                                Remember Me
                            </label>
                            @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-[#0056b3] font-medium hover:text-[#003d82] hover:underline transition-colors duration-300">Forgot
                                Password?</a>
                            @endif
                        </div>
                        <button type="submit" style="box-shadow: 2px 2px 5px rgba(0,0,0,0.2), -2px -2px 5px rgba(255,255,255,0.7), inset 0 0 0 rgba(0,0,0,0);"
                            class="relative w-full py-3 md:py-3.5 rounded-lg text-base md:text-lg font-semibold cursor-pointer transition-all duration-200 tracking-wide uppercase text-[#003d82] bg-white/30 backdrop-blur-md border border-white/40 hover:bg-[#722f37] hover:text-white active:shadow-[inset_2px_2px_5px_rgba(0,0,0,0.3),_inset_-2px_-2px_5px_rgba(255,255,255,0.1)] active:translate-y-0.5">
                            <span
                                class="pointer-events-none absolute inset-0 rounded-lg bg-[linear-gradient(135deg,_rgba(255,255,255,0.4)_0%,_rgba(255,255,255,0.1)_50%,_rgba(255,255,255,0)_100%)]"></span>
                            <span class="relative">LOGIN</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ABOUT TAB -->
    <div id="about" class="tab-content hidden p-5 md:p-10 bg-transparent min-h-[calc(100vh-100px)] animate-fadeIn">
        <div class="max-w-6xl mx-auto space-y-8">
            <!-- ABOUT SECTION -->
            <div class="bg-white p-6 md:p-10 rounded-2xl shadow-2xl">
                <div class="section-title">
                    <h3>About <span>IMIS</span></h3>
                </div>
                <div class="text-left">
                    <p>
                        IMIS is an open-source GIS-based Digital Public Infrastructure (DPI) which functions as both a
                        municipal information system and a software solution, integrating data, processes, and services
                        to enhance municipal governance—particularly in sanitation management with Citywide Inclusive
                        Sanitation (CWIS) approach to achieve SDG 6.2. It offers municipalities data-driven
                        decision-making tools to strengthen governance across various sectors. By leveraging open-source
                        technologies and Geographic Information Systems (GIS), it facilitates:
                    <ul>
                        <li>Planning, management, and monitoring of sanitation systems using the CWIS approach.</li>
                        <li>End-to-end FSM (Faecal Sludge Management) service chain oversight, including real-time data
                            tracking.</li>
                        <li>Generation and visualization of CWIS indicators for performance assessment.</li>
                        <li>Intuitive dashboards for tracking CWIS indicators, Key Performance Indicators (KPIs), and
                            other essential municipal governance metrics.</li>
                    </ul>
                    IMIS as a sub-national public data system contributes to national-level monitoring by feeding data
                    into centralized systems, supporting CWIS indicators and other critical metrics for achieving
                    sanitation targets.
                    Beyond sanitation management, with its modular and scalable design, Base IMIS empowers local
                    authorities by providing a unified, data-driven framework that enhances efficiency, accountability,
                    and service delivery in municipal governance.
                    </p>
                </div>
            </div>

            <!-- CWIS SECTION -->
            <div class="bg-white p-6 md:p-10 rounded-2xl shadow-2xl">
                <div class="section-title">
                    <h3>Citywide Inclusive Sanitation <span>(CWIS)</span></h3>
                </div>
                <div class="text-left">
                    <p>
                        CWIS is an approach to achieve SDG 6.2 for safe, equitable and financially viable sanitation
                        systems and services. CWIS ensures everyone in a city has access to safely managed sanitation,
                        and human waste is safely managed along the whole sanitation service chain ensuring protection
                        of the environment and human health.
                    </p>
                    <div class="flex justify-center my-6">
                        <img src="{{ asset('img/svg/landing-page/cwis.jpg') }}" alt="CWIS" class="max-w-full h-auto rounded-lg shadow-lg">
                    </div>
                    <p>CWIS approach focuses on service provision and its enabling environment rather than on building
                        infrastructure, therefore, reliable data is the key success factor for CWIS. UN Water SDG 6
                        global acceleration framework has also identified data and information as one of the five
                        accelerators of SDG 6 outcomes.</p>
                </div>
            </div>

            <!-- FEATURES SECTION -->
            <div class="bg-white p-6 md:p-10 rounded-2xl shadow-2xl">
                <div class="section-title">
                    <h3>Features of <span>IMIS</span></h3>
                </div>
                <div class="text-left">
                    <ul>
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
                <div style="text-align: center">
                    <!-- <button class="btn-get-started"> Learn More</button> -->
                </div>
            </div>

            <!-- FUNCTIONAL MODULES SECTION -->
            <div class="bg-white p-6 md:p-10 rounded-2xl shadow-2xl">
                <div class="section-title">
                    <h3>Functional<span> Modules</span></h3>
                </div>

                <!-- Grid of modules -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <!-- Building Information Management System -->
                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-lg transition-shadow duration-300">
                        <div class="text-center mb-4">
                            <img src="{{ asset('img/svg/landing-page/buildingIMS.svg') }}" alt="Building Icon" class="h-16 w-16 mx-auto mb-3">
                            <h5 class="text-lg font-semibold text-gray-800">Building Information Management System</h5>
                        </div>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 text-sm text-left">
                            <li>Maintains information about all existing and new buildings with their building footprints, sanitation system, socio-economic condition, etc</li>
                            <li>Maintains information about low-income communities with their geographic coverage and sanitation system</li>
                        </ul>
                    </div>

                    <!-- Property Tax Collection Support System -->
                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-lg transition-shadow duration-300">
                        <div class="text-center mb-4">
                            <img src="{{ asset('img/svg/landing-page/propertyTaxCollectionIMS.svg') }}" alt="Property Tax Icon" class="h-16 w-16 mx-auto mb-3">
                            <h5 class="text-lg font-semibold text-gray-800">Property Tax Collection Support System</h5>
                        </div>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 text-sm text-left">
                            <li>Enables import of property tax or other revenue data into IMIS for spatial visualization of buildings or containments with their tax or revenue collection status</li>
                        </ul>
                    </div>

                    <!-- Urban Management Decision Support System -->
                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-lg transition-shadow duration-300">
                        <div class="text-center mb-4">
                            <img src="{{ asset('img/svg/landing-page/urbanManagementDSS.svg') }}" alt="Urban Management Icon" class="h-16 w-16 mx-auto mb-3">
                            <h5 class="text-lg font-semibold text-gray-800">Urban Management Decision Support System</h5>
                        </div>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 text-sm text-left">
                            <li>Dashboard for monitoring the situation of sanitation and other elements required for planning, management and monitoring and evaluation of CWIS</li>
                            <li>Dashboards for monitoring KPIs and CWIS indicators</li>
                            <li>Tools for real-time monitoring of the sanitation service chain</li>
                            <li>Spatial analysis tools</li>
                            <li>Query and attribute analysis tools</li>
                            <li>Basic navigation tools for exploration, analysis, and visualization of spatial data within a GIS environment and tools for printing maps</li>
                        </ul>
                    </div>

                    <!-- Utility Information Management System -->
                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-lg transition-shadow duration-300">
                        <div class="text-center mb-4">
                            <img src="{{ asset('img/svg/landing-page/utilityIMS.svg') }}" alt="Utility Icon" class="h-16 w-16 mx-auto mb-3">
                            <h5 class="text-lg font-semibold text-gray-800">Utility Information Management System</h5>
                        </div>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 text-sm text-left">
                            <li>Maintains road network information</li>
                            <li>Maintains water supply network information</li>
                            <li>Maintains sewerage network information</li>
                            <li>Maintains drainage network information</li>
                        </ul>
                    </div>

                    <!-- Solid Waste Information Support System -->
                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-lg transition-shadow duration-300">
                        <div class="text-center mb-4">
                            <img src="{{ asset('img/svg/landing-page/swmPaymentStatus.svg') }}" alt="Solid Waste Icon" class="h-16 w-16 mx-auto mb-3">
                            <h5 class="text-lg font-semibold text-gray-800">Solid Waste Information Support System</h5>
                        </div>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 text-sm text-left">
                            <li>Enables import of solid waste management data into the system for spatial visualization of buildings with their solid waste management status</li>
                        </ul>
                    </div>

                    <!-- Water Supply Information Support System -->
                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-lg transition-shadow duration-300">
                        <div class="text-center mb-4">
                            <img src="{{ asset('img/svg/landing-page/watersupplyISS.svg') }}" alt="Water Supply Icon" class="h-16 w-16 mx-auto mb-3">
                            <h5 class="text-lg font-semibold text-gray-800">Water Supply Information Support System</h5>
                        </div>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 text-sm text-left">
                            <li>Enables import of water supply bill payment data into the system for spatial visualization of buildings with their bill payment status</li>
                        </ul>
                    </div>

                    <!-- Fecal Sludge Information Management System -->
                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-lg transition-shadow duration-300 lg:col-span-1">
                        <div class="text-center mb-4">
                            <img src="{{ asset('img/svg/landing-page/fecalSludgeIMS.svg') }}" alt="Fecal Sludge Icon" class="h-16 w-16 mx-auto mb-3">
                            <h5 class="text-lg font-semibold text-gray-800">Fecal Sludge Information Management System</h5>
                        </div>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 text-sm text-left">
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

    <!-- PUBLIC DASHBOARD -->
    <div id="dashboard" class="tab-content hidden p-5 md:p-10 bg-transparent min-h-[calc(100vh-100px)] animate-fadeIn">
        <div class="max-w-7xl mx-auto bg-white p-6 md:p-10 rounded-2xl shadow-2xl">
            <h2 class="text-[#1f3b7d] text-2xl md:text-3xl lg:text-4xl mb-8 border-b-4 border-[#0056b3] pb-4 font-bold">Public Dashboard</h2>

            <!-- Loading Spinner -->
            <div id="dashboard-loader" class="flex justify-center items-center py-20">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-[#0056b3] border-t-transparent"></div>
                <span class="ml-4 text-gray-600 text-lg">Loading Dashboard...</span>
            </div>

            <!-- Dashboard Content Container -->
            <div id="dashboard-content" class="hidden">
                <!-- Content will be loaded via AJAX -->
            </div>

            <!-- Error Message Container -->
            <div id="dashboard-error" class="hidden bg-red-50 border border-red-200 p-4 rounded-lg text-red-700">
                Failed to load dashboard. Please try again later.
            </div>
        </div>
    </div>

    <!-- FSM APPLICATION TAB -->
    <div id="fsm" class="tab-content hidden p-5 md:p-10 bg-transparent min-h-[calc(100vh-100px)] animate-fadeIn">
        <div class="bg-white p-5 md:p-8 rounded-2xl shadow-2xl w-full max-w-6xl mx-auto">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-center text-[#1f3b7d] mb-6 md:mb-8">FSM Application Form</h1>

            <!-- Alert Container for general errors -->
            <div id="fsm-alert-container"></div>

            <form id="fsm-application-form" class="needs-validation" novalidate>
                @csrf

                <!-- Tax Information Section -->
                <fieldset class="app_fieldset">
                    <legend>Tax Information</legend>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="has_tax_id" class="block text-gray-800 font-semibold mb-2 text-base">Do you have a Tax ID? <span class="text-red-500">*</span></label>
                            <select name="has_tax_id" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="has_tax_id" required>
                                <option value="">Please select</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                            <span class="error-message" id="error-has_tax_id"></span>
                        </div>
                    </div>

                    <div class="row" id="tax_id_group" style="display:none;">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="tax_id" class="block text-gray-800 font-semibold mb-2 text-base">Tax ID <span class="text-red-500 tax-required-star" style="display:none;">*</span></label>
                            <input type="text" name="tax_id" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="tax_id" placeholder="##-###-####-##">
                            <span class="error-message" id="error-tax_id"></span>
                        </div>
                    </div>
                </fieldset>

                <!-- Customer Information Section -->
                <fieldset class="app_fieldset">
                    <legend>Customer Information</legend>

                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="customer_name" class="block text-gray-800 font-semibold mb-2 text-base">Customer Name <span class="text-red-500">*</span></label>
                            <input type="text" name="customer_name" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="customer_name" placeholder="Customer Name" required>
                            <span class="error-message" id="error-customer_name"></span>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="customer_contact" class="block text-gray-800 font-semibold mb-2 text-base">Contact No. <span class="text-red-500">*</span></label>
                            <input type="tel" name="customer_contact" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="customer_contact" placeholder="01#########" required>
                            <span class="error-message" id="error-customer_contact"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="holding_owner_name" class="block text-gray-800 font-semibold mb-2 text-base">Holding Owner Name</label>
                            <input type="text" name="holding_owner_name" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="holding_owner_name" placeholder="Holding Owner Name">
                            <span class="error-message" id="error-holding_owner_name"></span>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label for="ward" class="block text-gray-800 font-semibold mb-2 text-base">Ward <span class="text-red-500">*</span></label>
                            <select name="ward" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="ward" required>
                                <option value="">Loading wards...</option>
                            </select>
                            <span class="error-message" id="error-ward"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="road_code" class="block text-gray-800 font-semibold mb-2 text-base">Road Name <small class="text-gray-500">(Optional)</small></label>
                            <select name="road_code" class="form-control w-full" id="road_code">
                                <option value="">Select Road</option>
                            </select>
                            <span class="error-message" id="error-road_code"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="address" class="block text-gray-800 font-semibold mb-2 text-base">Address <span class="text-red-500">*</span></label>
                            <textarea name="address" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y min-h-[100px] transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="address" rows="3" placeholder="Address" required></textarea>
                            <span class="error-message" id="error-address"></span>
                        </div>
                    </div>
                </fieldset>

                <!-- Service Info Section -->
                <fieldset class="app_fieldset">
                    <legend>Service Info</legend>

                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="proposed_emptying_date" class="block text-gray-800 font-semibold mb-2 text-base">Proposed Emptying Date <span class="text-red-500">*</span></label>
                            <input type="date" name="proposed_emptying_date" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="proposed_emptying_date" required>
                            <span class="error-message" id="error-proposed_emptying_date"></span>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="notes" class="block text-gray-800 font-semibold mb-2 text-base">Notes / Comments <span class="text-red-500">*</span></label>
                            <textarea name="notes" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y min-h-[100px] transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="notes" rows="3" placeholder="Additional Notes/Comments" required></textarea>
                            <span class="error-message" id="error-notes"></span>
                        </div>
                    </div>
                </fieldset>

                <!-- Location Section -->
                <fieldset class="app_fieldset">
                    <legend>Location</legend>

                    <div class="mb-3">
                        <div id="map" class="w-full rounded-lg shadow-sm border-2 border-gray-300"></div>
                    </div>

                    <!-- Hidden inputs for coordinates -->
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <span class="error-message" id="error-latitude"></span>
                    <span class="error-message" id="error-longitude"></span>
                </fieldset>

                <div class="text-center mt-4">
                    <button type="submit" id="fsm-submit-btn" class="px-8 py-3 bg-gradient-to-br from-[#007bff] to-[#0056b3] text-white border-none rounded-lg text-base md:text-lg font-semibold cursor-pointer transition-all duration-300 hover:from-[#0056b3] hover:to-[#003d82] hover:-translate-y-0.5 hover:shadow-xl hover:shadow-[#0056b3]/30">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="fsm-success-modal" class="fsm-modal-overlay" style="display: none;">
        <div class="fsm-modal-content">
            <div class="fsm-modal-icon">
                <i class="fas fa-check"></i>
            </div>
            <h3 class="text-2xl font-bold text-center text-gray-800 mb-3">Success!</h3>
            <p class="text-center text-gray-600 mb-4" id="fsm-success-message">Your FSM application has been submitted successfully!</p>
            <p class="text-center text-sm text-gray-500 mb-4">This modal will close in <span id="fsm-countdown">5</span> seconds...</p>
            <div class="text-center">
                <button onclick="closeFsmSuccessModal()" class="px-6 py-2 bg-gradient-to-br from-[#007bff] to-[#0056b3] text-white border-none rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:from-[#0056b3] hover:to-[#003d82]">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- FEEDBACK TAB -->
    <div id="feedback" class="tab-content hidden p-5 md:p-10 bg-transparent min-h-[calc(100vh-100px)] animate-fadeIn">
        <div class="max-w-3xl mx-auto bg-white p-6 md:p-10 rounded-2xl shadow-2xl">
            <h2 class="text-[#1f3b7d] text-2xl md:text-3xl lg:text-4xl mb-4 text-center font-bold">Feedback Form</h2>
            <p class="text-center text-gray-600 mb-6 md:mb-8 text-base md:text-lg">We value your feedback. Please share
                your comments, suggestions, or concerns with us:</p>
            <form method="POST" action="{{ route('contact.send') }}">
                @csrf
                <label for="name" class="block text-gray-800 font-semibold mt-4 mb-2 text-base">Full Name *</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required
                    class="w-full px-4 py-3 mb-4 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10">

                <label for="email" class="block text-gray-800 font-semibold mt-4 mb-2 text-base">Email Address *</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required
                    class="w-full px-4 py-3 mb-4 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10">

                <label for="subject" class="block text-gray-800 font-semibold mt-4 mb-2 text-base">Subject *</label>
                <input type="text" id="subject" name="subject" placeholder="Enter subject" required
                    class="w-full px-4 py-3 mb-4 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10">

                <label for="message" class="block text-gray-800 font-semibold mt-4 mb-2 text-base">Your Message
                    *</label>
                <textarea id="message" name="message" rows="5" placeholder="Share your feedback with us..." required
                    class="w-full px-4 py-3 mb-4 rounded-lg border-2 border-gray-300 text-base resize-y min-h-[120px] transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10"></textarea>

                <button type="submit"
                    class="w-full md:w-auto px-8 py-3 bg-gradient-to-br from-[#007bff] to-[#0056b3] text-white border-none rounded-lg text-base md:text-lg font-semibold cursor-pointer transition-all duration-300 mt-3 hover:from-[#0056b3] hover:to-[#003d82] hover:-translate-y-0.5 hover:shadow-xl hover:shadow-[#0056b3]/30">Submit
                    Feedback</button>
            </form>
        </div>
    </div>

    <!-- CONTACT TAB -->
    <div id="contact" class="tab-content hidden p-5 md:p-10 bg-transparent min-h-[calc(100vh-100px)] animate-fadeIn">
        <div class="max-w-6xl mx-auto bg-white p-6 md:p-10 rounded-2xl shadow-2xl">
            <h2 class="text-[#1f3b7d] text-2xl md:text-3xl lg:text-4xl mb-5 border-b-4 border-[#0056b3] pb-4">Contact Us
            </h2>
            <p class="text-gray-600 text-base md:text-lg leading-relaxed"><strong>Lakshmipur Paurashava</strong></p>
            <p class="text-gray-600 text-base md:text-lg leading-relaxed">Address: Lakshmipur Municipality Office,
                Lakshmipur, Bangladesh</p>
            <p class="text-gray-600 text-base md:text-lg leading-relaxed">Phone: [Contact Number]</p>
            <p class="text-gray-600 text-base md:text-lg leading-relaxed">Email: [Email Address]</p>
            <p class="text-gray-600 text-base md:text-lg leading-relaxed">Office Hours: Sunday to Thursday, 9:00 AM -
                5:00 PM</p>
            <p class="text-gray-600 text-base md:text-lg leading-relaxed mt-5">For any inquiries or assistance, please
                feel free to reach out to us during office hours.</p>
        </div>
    </div>


    <footer
        class="text-center py-5 text-sm md:text-base text-gray-800 bg-white shadow-[0_-2px_10px_rgba(0,0,0,0.05)] mt-10">
        <div class="px-5">
            @include('includes.branding')
        </div>
        <div class="pt-5">
            Implemented by <a href="https://streamstech.com" target="_blank" rel="noopener noreferrer"
                class="text-[#0056b3] font-semibold">Streams Tech Ltd.</a> |
            © {{ config('constants.SITE_NAME') }} {{ \Carbon\Carbon::now()->format('Y') }}. All rights reserved.
        </div>
    </footer>

    <script>
        // Set initial active tab on page load
    document.addEventListener('DOMContentLoaded', function() {
      const loginTab = document.getElementById('login');
      if (loginTab) loginTab.classList.remove('hidden');
      if (loginTab) loginTab.classList.add('block');
    });

    function openTab(tabId, el) {
      // Hide all tabs
      document.querySelectorAll('.tab-content').forEach(div => {
        div.classList.remove('block');
        div.classList.add('hidden');
      });

      // Show selected tab
      const selectedTab = document.getElementById(tabId);
      if (selectedTab) {
        selectedTab.classList.remove('hidden');
        selectedTab.classList.add('block');
      }

      // Update active nav item
      document.querySelectorAll('.nav-item').forEach(li => li.classList.remove('active-tab'));
      el.classList.add('active-tab');

      // Close mobile menu after selection
      if (window.innerWidth <= 768) {
        const nav = document.getElementById('navMenu');
        nav.classList.remove('menu-active');
        nav.classList.add('max-h-0');
      }

      // Load dashboard content if dashboard tab is opened
      if (tabId === 'dashboard') {
        loadPublicDashboard();
      }
    }

    function toggleMenu() {
      const nav = document.getElementById('navMenu');
      nav.classList.toggle('menu-active');

      if (nav.classList.contains('menu-active')) {
        nav.classList.remove('max-h-0');
        nav.classList.add('max-h-96');
      } else {
        nav.classList.remove('max-h-96');
        nav.classList.add('max-h-0');
      }
    }

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
      const nav = document.getElementById('navMenu');
      const toggle = event.target.closest('.menu-toggle');
      const header = document.querySelector('header');

      if (window.innerWidth <= 768 &&
          !header.contains(event.target) &&
          nav.classList.contains('menu-active')) {
        nav.classList.remove('menu-active');
        nav.classList.remove('max-h-96');
        nav.classList.add('max-h-0');
      }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
      const nav = document.getElementById('navMenu');
      if (window.innerWidth > 768) {
        nav.classList.remove('menu-active', 'max-h-0', 'max-h-96');
      } else if (!nav.classList.contains('menu-active')) {
        nav.classList.add('max-h-0');
      }
    });

    // Load Public Dashboard via AJAX
    function loadPublicDashboard() {
      const loader = document.getElementById('dashboard-loader');
      const content = document.getElementById('dashboard-content');
      const error = document.getElementById('dashboard-error');

      // Show loader, hide content and error
      loader.classList.remove('hidden');
      content.classList.add('hidden');
      error.classList.add('hidden');

      $.ajax({
        url: '{{ route("public-dashboard") }}',
        method: 'GET',
        dataType: 'html',
        success: function(response) {
          // Hide loader
          loader.classList.add('hidden');

          // Parse HTML and extract scripts
          var tempDiv = document.createElement('div');
          tempDiv.innerHTML = response;

          // Extract all script tags
          var scripts = tempDiv.querySelectorAll('script');
          var scriptsArray = Array.from(scripts);

          // Remove scripts from the HTML temporarily
          scriptsArray.forEach(function(script) {
            script.parentNode.removeChild(script);
          });

          // Insert the HTML without scripts
          content.innerHTML = tempDiv.innerHTML;
          content.classList.remove('hidden');

          // Execute scripts after a brief delay to ensure DOM is ready
          setTimeout(function() {
            scriptsArray.forEach(function(oldScript) {
              var newScript = document.createElement('script');
              if (oldScript.src) {
                newScript.src = oldScript.src;
              } else {
                newScript.textContent = oldScript.textContent;
              }
              document.body.appendChild(newScript);
            });

            console.log('Dashboard loaded with ' + scriptsArray.length + ' scripts executed');
          }, 100);

          // Initialize tooltips
          if ($.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip({ html: true });
          }
        },
        error: function() {
          // Hide loader
          loader.classList.add('hidden');

          // Show error
          error.classList.remove('hidden');
          console.error('Failed to load public dashboard');
        }
      });
    }

    // ========== FSM APPLICATION FORM FUNCTIONALITY ==========

    // Global variables for FSM form
    var fsmMap = null;
    var fsmMarker = null;
    var fsmWardsLoaded = false;
    var fsmFormInitialized = false;
    var fsmAutoDismissTimeout = null;

    // Function to close success modal
    function closeFsmSuccessModal() {
      $('#fsm-success-modal').fadeOut(300);
      if (fsmAutoDismissTimeout) {
        clearTimeout(fsmAutoDismissTimeout);
        fsmAutoDismissTimeout = null;
      }
    }

    // Function to show success modal with auto-dismiss
    function showFsmSuccessModal(message) {
      $('#fsm-success-message').text(message || 'Your FSM application has been submitted successfully!');
      $('#fsm-success-modal').fadeIn(300);

      // Auto-dismiss countdown
      var countdown = 5;
      $('#fsm-countdown').text(countdown);

      var countdownInterval = setInterval(function() {
        countdown--;
        $('#fsm-countdown').text(countdown);
        if (countdown <= 0) {
          clearInterval(countdownInterval);
        }
      }, 1000);

      // Auto-dismiss after 5 seconds
      fsmAutoDismissTimeout = setTimeout(function() {
        closeFsmSuccessModal();
      }, 5000);
    }

    // Function to load wards data
    function loadFsmWards() {
      if (fsmWardsLoaded) return;

      $.ajax({
        url: '{{ route("client-fsm-application.get-wards") }}',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response.success && response.wards) {
            var $wardSelect = $('#ward');
            $wardSelect.empty();
            $wardSelect.append('<option value="">Select Ward</option>');

            response.wards.forEach(function(ward) {
              $wardSelect.append('<option value="' + ward + '">' + ward + '</option>');
            });

            fsmWardsLoaded = true;
          }
        },
        error: function() {
          var $wardSelect = $('#ward');
          $wardSelect.empty();
          $wardSelect.append('<option value="">Failed to load wards</option>');
          console.error('Failed to load wards data');
        }
      });
    }

    // Function to clear all form errors
    function clearFsmFormErrors() {
      $('.error-message').text('').hide();
      $('.field-error').removeClass('field-error');
      $('#fsm-alert-container').empty();
    }

    // Function to display validation errors
    function displayFsmErrors(errors) {
      clearFsmFormErrors();

      $.each(errors, function(field, messages) {
        var $field = $('#' + field);
        var $errorSpan = $('#error-' + field);

        if ($field.length) {
          $field.addClass('field-error');

          // Clear error when user starts typing
          $field.one('input change', function() {
            $(this).removeClass('field-error');
            $errorSpan.text('').hide();
          });
        }

        if ($errorSpan.length && messages.length > 0) {
          $errorSpan.text(messages[0]).show();
        }
      });
    }

    // Function to reset FSM form
    function resetFsmForm() {
      $('#fsm-application-form')[0].reset();
      clearFsmFormErrors();

      // Reset Tax ID visibility
      $('#tax_id_group').slideUp(150);
      $('#tax_id').prop('required', false).removeAttr('aria-required');
      $('.tax-required-star').hide();

      // Reset Select2
      if ($('#road_code').data('select2')) {
        $('#road_code').val(null).trigger('change');
      }

      // Reset map marker to default position
      if (fsmMap && fsmMarker) {
        var defaultLat = 23.780887;
        var defaultLon = 90.279237;
        fsmMarker.setLatLng([defaultLat, defaultLon]);
        fsmMap.setView([defaultLat, defaultLon], 13);
        $('#latitude').val('');
        $('#longitude').val('');
      }
    }

    // Initialize FSM form functionality
    function initializeFsmForm() {
      if (fsmFormInitialized) return;

      // Tax ID visibility toggle
      $('#has_tax_id').on('change', function() {
        var $group = $('#tax_id_group');
        var $input = $('#tax_id');
        var $star = $('.tax-required-star');

        if ($(this).val() === 'yes') {
          $group.slideDown(150);
          $input.prop('required', true).attr('aria-required', 'true');
          $star.show();
        } else {
          $group.slideUp(150);
          $input.prop('required', false).removeAttr('aria-required');
          $star.hide();
          $input.val('');
        }
      });

      // Initialize Select2 for road names
      $('#road_code').select2({
        ajax: {
          url: '{{ route("client-fsm-application.get-road-names") }}',
          dataType: 'json',
          delay: 250,
          data: function(params) {
            return {
              search: params.term,
              page: params.page || 1
            };
          },
          processResults: function(data, params) {
            params.page = params.page || 1;
            return {
              results: data.results,
              pagination: {
                more: data.pagination && data.pagination.more
              }
            };
          },
          cache: true
        },
        placeholder: 'Street Name / Street Code',
        allowClear: true,
        closeOnSelect: true,
        width: '100%'
      });

      // Initialize Cleave.js for Tax ID
      new Cleave('#tax_id', {
        numericOnly: true,
        delimiter: '-',
        blocks: [2, 3, 4, 2],
        delimiterLazyShow: true
      });

      // Initialize Cleave.js for Contact
      new Cleave('#customer_contact', {
        numericOnly: true,
        blocks: [11]
      });

      // Auto-fill from Tax ID
      var taxIdInput = $('#tax_id');
      var debounceTimeout = null;
      var isLoadingData = false;

      function clearAutoPopulatedFields() {
        $('#customer_name').val('');
        $('#customer_contact').val('');
        $('#holding_owner_name').val('');
        $('#ward').val('');
        $('#road_code').val(null).trigger('change');
        $('#address').val('');
      }

      function hasMinimumLength(taxId) {
        var digitsOnly = taxId.replace(/[^0-9]/g, '');
        return digitsOnly.length >= 8;
      }

      function isValidTaxId(taxId) {
        var taxIdPattern = /^\d{2}-\d{3}-\d{4}-\d{2}$/;
        return taxIdPattern.test(taxId.trim());
      }

      function autoFillFromTaxId() {
        var taxId = taxIdInput.val().trim();

        if (!taxId || taxId.length < 10 || !hasMinimumLength(taxId)) {
          clearAutoPopulatedFields();
          return;
        }

        if (!isValidTaxId(taxId)) {
          if (taxId.length >= 13) {
            clearAutoPopulatedFields();
          }
          return;
        }

        if (isLoadingData) return;

        isLoadingData = true;

        $.ajax({
          url: '{{ route("client-fsm-application.get-building-data") }}',
          type: 'GET',
          data: { tax_id: taxId.trim() },
          success: function(response) {
            if (response.success && response.data) {
              var data = response.data;

              $('#customer_name').val(data.customer_name || '');
              $('#customer_contact').val(data.customer_contact || '');
              $('#holding_owner_name').val(data.holding_owner_name || '');
              $('#ward').val(data.ward || '');
              $('#address').val(data.address || '');

              if (data.road_code && data.road_name_text) {
                var $roadCode = $('#road_code');
                if (!$roadCode.find('option[value="' + data.road_code + '"]').length) {
                  var newOption = new Option(data.road_name_text, data.road_code, true, true);
                  $roadCode.append(newOption);
                }
                $roadCode.val(data.road_code).trigger('change');
              }
            } else {
              clearAutoPopulatedFields();
            }
          },
          error: function() {
            clearAutoPopulatedFields();
          },
          complete: function() {
            isLoadingData = false;
          }
        });
      }

      taxIdInput.on('input', function() {
        var taxId = taxIdInput.val().trim();

        if (!taxId || taxId.length < 10 || !hasMinimumLength(taxId)) {
          if (debounceTimeout) {
            clearTimeout(debounceTimeout);
            debounceTimeout = null;
          }
          clearAutoPopulatedFields();
          return;
        }

        if (debounceTimeout) {
          clearTimeout(debounceTimeout);
        }

        debounceTimeout = setTimeout(function() {
          autoFillFromTaxId();
          debounceTimeout = null;
        }, 400);
      });

      taxIdInput.on('blur', function() {
        if (debounceTimeout) {
          clearTimeout(debounceTimeout);
          debounceTimeout = null;
        }
        autoFillFromTaxId();
      });

      // Initialize Leaflet Map
      var defaultLat = 23.780887;
      var defaultLon = 90.279237;

      fsmMap = L.map('map').setView([defaultLat, defaultLon], 13);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(fsmMap);

      fsmMarker = L.marker([defaultLat, defaultLon], { draggable: true }).addTo(fsmMap);

      function updateLocation(lat, lon) {
        $('#latitude').val(Number(lat).toFixed(6));
        $('#longitude').val(Number(lon).toFixed(6));
      }

      fsmMarker.on('dragend', function(e) {
        var p = e.target.getLatLng();
        updateLocation(p.lat, p.lng);
      });

      fsmMap.on('click', function(e) {
        fsmMarker.setLatLng(e.latlng);
        updateLocation(e.latlng.lat, e.latlng.lng);
      });

      // Form submission
      $('#fsm-application-form').on('submit', function(e) {
        e.preventDefault();

        clearFsmFormErrors();

        var $submitBtn = $('#fsm-submit-btn');
        var originalText = $submitBtn.html();

        // Disable button and show loading state
        $submitBtn.prop('disabled', true).addClass('btn-loading').html('Submitting...');

        var formData = $(this).serialize();

        $.ajax({
          url: '{{ route("client-fsm-application.submit") }}',
          method: 'POST',
          data: formData,
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              // Show success modal
              showFsmSuccessModal(response.message);

              // Reset form
              resetFsmForm();
            } else {
              // Show error message
              if (response.message) {
                $('#fsm-alert-container').html(
                  '<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">' +
                  '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                  '<strong>Error!</strong> ' + response.message +
                  '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                  '<span aria-hidden="true">&times;</span></button></div>'
                );
              }
            }
          },
          error: function(xhr) {
            if (xhr.status === 422) {
              // Validation errors
              var errors = xhr.responseJSON.errors;
              displayFsmErrors(errors);

              // Scroll to first error
              var firstError = $('.field-error').first();
              if (firstError.length) {
                $('html, body').animate({
                  scrollTop: firstError.offset().top - 100
                }, 500);
              }
            } else {
              // General error
              $('#fsm-alert-container').html(
                '<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">' +
                '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                '<strong>Error!</strong> An unexpected error occurred. Please try again.' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span></button></div>'
              );
            }
          },
          complete: function() {
            // Re-enable button
            $submitBtn.prop('disabled', false).removeClass('btn-loading').html(originalText);
          }
        });
      });

      fsmFormInitialized = true;
    }

    // Document ready
    $(document).ready(function() {
      // Load wards data on page load
      loadFsmWards();

      // Initialize FSM form when FSM tab is opened
      var originalOpenTab = window.openTab;
      window.openTab = function(tabId, el) {
        originalOpenTab(tabId, el);

        if (tabId === 'fsm') {
          // Initialize form on first open
          if (!fsmFormInitialized) {
            setTimeout(function() {
              initializeFsmForm();
              // Invalidate map size after tab is visible
              if (fsmMap) {
                fsmMap.invalidateSize();
              }
            }, 100);
          } else {
            // Just invalidate map size if already initialized
            setTimeout(function() {
              if (fsmMap) {
                fsmMap.invalidateSize();
              }
            }, 100);
          }
        }
      };
    });
    </script>
</body>

</html>