<!-- Last Modified Date: 23-12-2024
Developed By: Streams Tech Ltd. -->
<!DOCTYPE html>
<html lang="en" class="overflow-x-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

    @include('landingpage-forms.login')
    @include('landingpage-forms.about')
    @include('landingpage-forms.dashboard')
    @include('landingpage-forms.fsm')
    @include('landingpage-forms.feedback')
    @include('landingpage-forms.contact')


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
    </script>
    @stack('scripts')
</body>

</html>