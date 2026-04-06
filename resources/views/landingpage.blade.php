<!-- Last Modified: April 6, 2026
Developed By: Streams Tech Ltd.
Description: Modern municipal portal with hero section, glassmorphic design, and tab-based content sections -->
<!DOCTYPE html>
<html lang="en" class="scroll-smooth overflow-x-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('constants.SITE_NAME') }} - IMIS Portal</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />

    <!-- Chart.js for Dashboard Charts (v2.9.4) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>

    <!-- jQuery for AJAX calls in forms -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 for enhanced dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Cleave.js for input masking -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.0.2/cleave.min.js"></script>

    <!-- Leaflet JS for maps -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#0D47A1",
                        "background-light": "#F8FAFC",
                        "accent-blue": "#3b82f6",
                        "accent-green": "#22c55e",
                        "accent-purple": "#a855f7",
                        "accent-orange": "#f97316",
                        "accent-pink": "#ec4899",
                        "accent-teal": "#14b8a6",
                    },
                    fontFamily: {
                        display: ["Plus Jakarta Sans", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "0.75rem",
                    },
                },
            },
        };
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .hero-overlay {
            background: linear-gradient(rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.85));
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .infographic-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .infographic-card:hover {
            transform: translateY(-4px);
        }

        .stat-icon {
            opacity: 0.15;
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 80px !important;
        }

        /* FSM Form Custom Styles */
        .app_fieldset {
            border: 1px solid #e2e8f0;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.25rem;
            background: #ffffff;
        }

        .app_fieldset>legend {
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

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .fsm-modal-icon i {
            color: white;
            font-size: 2.5rem;
        }

        /* Tab animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-in-out;
        }

        /* Vue Multiselect Custom Styles */
        .multiselect__tags {
            border: 2px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            padding: 0.625rem 2.5rem 0 0.75rem !important;
            min-height: 3rem !important;
            font-size: 1rem !important;
            transition: all 0.3s ease !important;
        }

        .multiselect__tags:hover {
            border-color: #9ca3af !important;
        }

        .multiselect__tags:focus-within {
            border-color: #0056b3 !important;
            box-shadow: 0 0 0 4px rgba(0, 86, 179, 0.1) !important;
            outline: none !important;
        }

        .multiselect-error .multiselect__tags {
            border-color: #ef4444 !important;
        }

        .multiselect__input,
        .multiselect__single {
            font-size: 1rem !important;
            padding: 0.25rem 0 !important;
            margin-bottom: 0 !important;
            line-height: 1.5 !important;
            --tw-ring-color: #fff !important;
        }

        .multiselect__placeholder {
            font-size: 1rem !important;
            color: #9ca3af !important;
            padding-top: 0.25rem !important;
            margin-bottom: 0 !important;
        }

        .multiselect__select {
            height: 3rem !important;
            padding: 0 0.5rem !important;
        }

        .multiselect__content-wrapper {
            border: 2px solid #0056b3 !important;
            border-radius: 0.5rem !important;
            margin-top: 0.25rem !important;
            background: #d1d5db !important;
        }

        .multiselect__option {
            font-size: 1rem !important;
            padding: 0.75rem 1rem !important;
            min-height: auto !important;
        }

        .multiselect__option--highlight {
            background: #0056b3 !important;
        }

        .multiselect__option--selected {
            background: #e0f2fe !important;
            color: #0369a1 !important;
            font-weight: 500 !important;
        }

        .multiselect__option--selected.multiselect__option--highlight {
            background: #0056b3 !important;
            color: white !important;
        }

        @media (max-width: 576px) {
            .app_fieldset {
                padding: 0.75rem;
            }

            .app_fieldset>legend {
                font-size: 0.95rem;
            }
        }
    </style>
</head>

<body class="font-display bg-background-light text-slate-900 min-h-screen overflow-x-hidden">

    <!-- Fixed Navigation Bar -->
    <nav class="fixed top-0 w-full z-50 glass-nav border-b border-slate-200 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="/" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
                    <img alt="{{ config('constants.SITE_NAME') }} Logo" class="h-12 w-auto rounded-full shadow-sm"
                        src="{{ asset(config('constants.LOGO_URL')) }}" />
                    <span class="text-xl font-bold tracking-tight text-primary">{{ config('constants.SITE_NAME')
                        }}</span>
                </a>
                <div class="hidden md:flex items-center space-x-8">
                    <a class="text-sm font-medium hover:text-primary transition-colors cursor-pointer"
                        href="/#/about">About</a>
                    <a class="text-sm font-medium hover:text-primary transition-colors cursor-pointer"
                        href="/#/dashboard">Public Dashboard</a>
                    <a class="text-sm font-medium hover:text-primary transition-colors cursor-pointer" href="/#/fsm">FSM
                        Application</a>
                    <a class="text-sm font-medium hover:text-primary transition-colors cursor-pointer"
                        href="/#/feedback">Feedback</a>
                    <a class="text-sm font-medium hover:text-primary transition-colors cursor-pointer"
                        href="/#/contact">Contact</a>
                    <a class="text-sm font-medium hover:text-primary transition-colors cursor-pointer"
                        href="/#/">Sign In</a>
                </div>
                <button class="md:hidden p-2 rounded-full hover:bg-slate-100 transition-colors" onclick="toggleMenu()"
                    aria-label="Toggle menu">
                    <span class="material-icons text-xl">menu</span>
                </button>
            </div>
            <!-- Mobile Menu -->
            <div id="mobileMenu" class="hidden md:hidden pb-4 border-t border-slate-200">
                <a class="block px-4 py-2 text-sm font-medium hover:bg-slate-100 rounded" href="/#/about">About</a>
                <a class="block px-4 py-2 text-sm font-medium hover:bg-slate-100 rounded" href="/#/dashboard">Public
                    Dashboard</a>
                <a class="block px-4 py-2 text-sm font-medium hover:bg-slate-100 rounded" href="/#/fsm">FSM
                    Application</a>
                <a class="block px-4 py-2 text-sm font-medium hover:bg-slate-100 rounded"
                    href="/#/feedback">Feedback</a>
                <a class="block px-4 py-2 text-sm font-medium hover:bg-slate-100 rounded" href="/#/contact">Contact</a>
                <a class="block px-4 py-2 text-sm font-medium hover:bg-slate-100 rounded" href="/#/">Sign In</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Login -->
    <main class="relative min-h-screen flex items-center pt-20">
        <div class="absolute inset-0 z-0">
            <img alt="Municipal Building" class="w-full h-full object-cover"
                src="{{ asset(config('constants.BACKGROUND_IMAGE_URL')) }}" />
            <div class="absolute inset-0 hero-overlay"></div>
        </div>
        <div id="vue_app" class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <router-view></router-view>
        </div>
    </main>

    <script type="text/x-template" id="loginPage">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left Column: Hero Content -->
            <div class="max-w-xl">
                <h1 class="text-4xl md:text-5xl lg:text-5xl font-extrabold text-slate-900 leading-tight mb-6">
                    Integrated Municipal Information System <span class="text-primary">(IMIS)</span>
                </h1>
                <p class="text-lg text-slate-600 mb-4 leading-relaxed">
                    This application was implemented under the following project:
                </p>
                <div class="text-sm text-slate-700 space-y-1 mb-8 bg-gradient-to-r from-blue-50/40 to-transparent p-5 rounded-lg border-l-4 border-primary/30">
                    <div>
                        <span class="font-semibold text-slate-900">Project:</span>
                        <span class="text-slate-700"> Inclusive and Integrated Sanitation and Hygiene Project in 10 Priority Towns in Bangladesh</span>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-900">Implementing Organization:</span>
                        <span class="text-slate-700"> Department of Public Health Engineering (DPHE)</span>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-900">Funded by:</span>
                        <span class="text-slate-700"> Government of Bangladesh, Islamic Development Bank, and Gates Foundation</span>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-900">Technical Partners:</span>
                        <div class="text-slate-700 ml-1 mt-2 space-y-1">
                            <div class="flex items-start"><span class="mr-2">•</span><span>Global Water and Sanitation Center (GWSC) under the Asian Institute of Technology (AIT), Thailand</span></div>
                            <div class="flex items-start"><span class="mr-2">•</span><span>Innovative Solution Pvt. Limited, Nepal</span></div>
                            <div class="flex items-start"><span class="mr-2">•</span><span>Streams Tech Ltd., Bangladesh</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Login Card -->
            <div class="flex justify-center lg:justify-end">
                @include('landingpage-forms.login')
            </div>
        </div>
    </script>
    <script type="text/x-template" id="contactPage">
        @include('landingpage-forms.contact')
    </script>
    <script type="text/x-template" id="aboutPage">
        @include('landingpage-forms.about')
    </script>
    <script type="text/x-template" id="dashboardPage">
        @include('landingpage-forms.dashboard')
    </script>
    <script type="text/x-template" id="fsmPage">
        @include('landingpage-forms.fsm')
    </script>
    <script type="text/x-template" id="feedbackPage">
        @include('landingpage-forms.feedback')
    </script>

    <!-- Footer -->
    <footer
        class="text-center py-8 text-sm md:text-base text-gray-800 bg-white border-t border-slate-200 shadow-[0_-2px_10px_rgba(0,0,0,0.05)] mt-10">
        <div class="px-5">
            @include('includes.branding')
        </div>
        <div class="pt-5 mt-4 border-t border-slate-200 mx-3">
            <div class="mx-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-left">
                <div>
                    © {{ config('constants.SITE_NAME') }}. All rights reserved.
                </div>
                <div class="sm:text-right">
                    Implemented by <a href="https://streamstech.com" target="_blank" rel="noopener noreferrer"
                        class="text-primary font-semibold hover:underline">Streams Tech Ltd.</a>
                </div>
            </div>
        </div>
    </footer>
    <script>
        function toggleMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenu) {
                mobileMenu.classList.toggle('hidden');
            }
        }

        function loadPublicDashboard() {
            const dashboardContent = document.getElementById('dashboard-content');
            if (!dashboardContent || dashboardContent.className.includes('dashboard-loaded')) return;

            $.get("{{ route('public-dashboard') }}", function(data) {
                dashboardContent.innerHTML = data;
                dashboardContent.classList.add('dashboard-loaded');
                // Re-execute scripts from dashboard
                const newScripts = dashboardContent.querySelectorAll('script');
                newScripts.forEach(script => {
                    const newScript = document.createElement('script');
                    if (script.textContent) {
                        newScript.textContent = script.textContent;
                    }
                    if (script.src) {
                        newScript.src = script.src;
                    }
                    dashboardContent.appendChild(newScript);
                });
            }).catch(function(error) {
                console.error('Error loading dashboard:', error);
            });
        }
    </script>

    <link rel="stylesheet" href="https://unpkg.com/vue-multiselect@3.4.0/dist/vue-multiselect.min.css">
    <script type="importmap">
    {
        "imports": {
            "vue": "https://unpkg.com/vue@3/dist/vue.esm-browser.js",
            "vue-router": "https://unpkg.com/vue-router@4/dist/vue-router.esm-browser.js",
            "@vue/devtools-api": "https://unpkg.com/@vue/devtools-api@8/dist/vue-devtools-api.esm-browser.js",
            "vue-multiselect": "https://unpkg.com/vue-multiselect@3.4.0/dist/vue-multiselect.esm.js"
        }
    }
    </script>
    <script type="module">
        import { createApp, ref, watch, computed, onMounted } from 'vue';
        import { createRouter, createWebHashHistory, createWebHistory } from 'vue-router';
        import Multiselect from 'vue-multiselect';

    const Login = {
        template: '#loginPage'
    };
    const Contact = {
        template: '#contactPage'
    };
    const About = {
        template: '#aboutPage'
    }
    const Dashboard = {
        template: '#dashboardPage',
        mounted() {
            loadPublicDashboard();
        }
    }
    const FsmApplication = {
        template: '#fsmPage',
        setup() {
            // Form fields
            const hasTaxId = ref('');
            const taxId = ref('');
            const customerName = ref('');
            const customerContact = ref('');
            const holdingOwnerName = ref('');
            const ward = ref('');
            const address = ref('');
            const proposedEmptyingDate = ref('');
            const notes = ref('');

            // State
            const wards = ref([]);
            const fieldErrors = ref({});
            const isSubmitting = ref(false);
            const showModal = ref(false);
            const successMessage = ref('');
            const countdown = ref(5);
            const wardsLoaded = ref(false);
            const lastAutoSelectedWard = ref('');

            let countdownTimer = null;

            // Computed
            const showTaxIdField = computed(() => hasTaxId.value === 'yes');

            // Format tax ID: ##-###-####-##
            function formatTaxId(value) {
                const digitsOnly = value.replace(/[^0-9]/g, '');
                const blocks = [2, 3, 4, 2];
                let formatted = '';
                let index = 0;

                for (let i = 0; i < blocks.length && index < digitsOnly.length; i++) {
                    if (i > 0 && formatted.length > 0) {
                        formatted += '-';
                    }
                    formatted += digitsOnly.substr(index, blocks[i]);
                    index += blocks[i];
                }

                return formatted;
            }

            // Format phone: 11 digits only
            function formatPhone(value) {
                const digitsOnly = value.replace(/[^0-9]/g, '');
                return digitsOnly.substring(0, 11);
            }

            // Validate tax ID format
            function isValidTaxId(taxIdValue) {
                const taxIdPattern = /^\d{2}-\d{3}-\d{4}-\d{2}$/;
                return taxIdPattern.test(taxIdValue.trim());
            }

            // Check minimum length before full format validation
            function hasMinimumLength(taxIdValue) {
                const digitsOnly = taxIdValue.replace(/[^0-9]/g, '');
                return digitsOnly.length >= 8;
            }

            function extractWardFromTaxId(taxIdValue) {
                if (!taxIdValue || !isValidTaxId(taxIdValue)) {
                    return '';
                }

                return taxIdValue.substring(0, 2);
            }

            function findMatchingWardOption(wardCode) {
                if (!wardCode) {
                    return '';
                }

                if (wards.value.includes(parseInt(wardCode, 10))) {
                    return wardCode;
                }

                return '';
            }

            // Load wards
            async function loadWards() {
                if (wardsLoaded.value) return;

                try {
                    const res = await fetch('{{ route("client-fsm-application.get-wards") }}', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();

                    if (data.success && data.wards) {
                        wards.value = data.wards;
                        wardsLoaded.value = true;
                    }
                } catch (err) {
                    console.error('Failed to load wards:', err);
                }
            }

            // Clear field error
            function clearFieldError(fieldName) {
                if (fieldErrors.value[fieldName]) {
                    delete fieldErrors.value[fieldName];
                    fieldErrors.value = { ...fieldErrors.value };
                }
            }

            // Reset form
            function resetForm() {
                hasTaxId.value = '';
                taxId.value = '';
                customerName.value = '';
                customerContact.value = '';
                holdingOwnerName.value = '';
                ward.value = '';
                address.value = '';
                proposedEmptyingDate.value = '';
                notes.value = '';
                fieldErrors.value = {};
                lastAutoSelectedWard.value = '';
            }

            // Handle form submission
            async function handleSubmit() {
                // Clear previous errors
                fieldErrors.value = {};

                // Validate required fields
                if (!hasTaxId.value) {
                    fieldErrors.value.has_tax_id = 'Please select if you have a Tax ID';
                }
                if (hasTaxId.value === 'yes' && !taxId.value) {
                    fieldErrors.value.tax_id = 'Tax ID is required';
                } else if (hasTaxId.value === 'yes' && !hasMinimumLength(taxId.value)) {
                    fieldErrors.value.tax_id = 'Tax ID is incomplete';
                } else if (hasTaxId.value === 'yes' && !isValidTaxId(taxId.value)) {
                    fieldErrors.value.tax_id = 'Tax ID format must be 00-000-0000-00';
                }
                if (!customerName.value) {
                    fieldErrors.value.customer_name = 'Customer Name is required';
                }
                if (!customerContact.value) {
                    fieldErrors.value.customer_contact = 'Contact No. is required';
                }
                if (!ward.value) {
                    fieldErrors.value.ward = 'Ward is required';
                }
                if (!address.value) {
                    fieldErrors.value.address = 'Address is required';
                }
                if (!proposedEmptyingDate.value) {
                    fieldErrors.value.proposed_emptying_date = 'Proposed Emptying Date is required';
                }

                // If errors exist, stop submission
                if (Object.keys(fieldErrors.value).length > 0) {
                    return;
                }

                isSubmitting.value = true;

                const formData = new FormData();
                formData.append('has_tax_id', hasTaxId.value);
                formData.append('tax_id', taxId.value);
                formData.append('customer_name', customerName.value);
                formData.append('customer_contact', customerContact.value);
                formData.append('holding_owner_name', holdingOwnerName.value);
                formData.append('ward', ward.value);
                formData.append('address', address.value);
                formData.append('proposed_emptying_date', proposedEmptyingDate.value);
                formData.append('notes', notes.value);

                try {
                    const res = await fetch('{{ route("client-fsm-application.submit") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        successMessage.value = data.message || 'Your FSM application has been submitted successfully!';
                        showModal.value = true;
                        resetForm();
                    } else if (res.status === 422 && data.errors) {
                        // Validation errors
                        fieldErrors.value = {};
                        Object.keys(data.errors).forEach(key => {
                            fieldErrors.value[key] = data.errors[key][0];
                        });
                    } else {
                        // General error
                        alert(data.message || 'An error occurred. Please try again.');
                    }
                } catch (err) {
                    console.error('Submission error:', err);
                    alert('An unexpected error occurred. Please try again.');
                } finally {
                    isSubmitting.value = false;
                }
            }

            // Close modal
            function closeModal() {
                showModal.value = false;
            }

            // Watch hasTaxId to clear tax ID when switching to "No"
            watch(hasTaxId, (newVal) => {
                if (newVal !== 'yes') {
                    taxId.value = '';
                    ward.value = '';
                    lastAutoSelectedWard.value = '';
                    clearFieldError('tax_id');
                }
                clearFieldError('has_tax_id');
            });

            watch(taxId, (newVal) => {
                clearFieldError('tax_id');

                if (hasTaxId.value !== 'yes') {
                    return;
                }

                const parsedWard = extractWardFromTaxId(newVal);
                if(parsedWard === '') {
                    return;
                }
                const matchedWard = findMatchingWardOption(parsedWard);

                if (matchedWard) {
                    ward.value = +matchedWard;
                    lastAutoSelectedWard.value = +matchedWard;
                    return;
                }

                if (lastAutoSelectedWard.value && ward.value === lastAutoSelectedWard.value) {
                    ward.value = '';
                }
                lastAutoSelectedWard.value = '';
            });

            // Watch showModal for countdown
            watch(showModal, (val) => {
                if (val) {
                    countdown.value = 5;
                    countdownTimer = setInterval(() => {
                        countdown.value--;
                        if (countdown.value <= 0) {
                            closeModal();
                        }
                    }, 1000);
                } else {
                    if (countdownTimer) {
                        clearInterval(countdownTimer);
                        countdownTimer = null;
                    }
                }
            });

            // Clear field errors when typing
            watch(customerName, () => clearFieldError('customer_name'));
            watch(customerContact, () => clearFieldError('customer_contact'));
            watch(holdingOwnerName, () => clearFieldError('holding_owner_name'));
            watch(ward, () => clearFieldError('ward'));
            watch(address, () => clearFieldError('address'));
            watch(proposedEmptyingDate, () => clearFieldError('proposed_emptying_date'));
            watch(notes, () => clearFieldError('notes'));

            // Load wards on mount
            onMounted(() => {
                loadWards();
            });

            return {
                hasTaxId,
                taxId,
                customerName,
                customerContact,
                holdingOwnerName,
                ward,
                address,
                proposedEmptyingDate,
                notes,
                wards,
                fieldErrors,
                isSubmitting,
                showModal,
                successMessage,
                countdown,
                showTaxIdField,
                formatTaxId,
                formatPhone,
                handleSubmit,
                closeModal
            };
        }
    }
    const Feedback = {
        template: '#feedbackPage',
        setup() {
            const applicationId = ref('');
            const customerName = ref('');
            const customerNumber = ref('');
            const customerGender = ref('');
            const serviceProviderName = ref('');
            const serviceProviderContact = ref('');

            const safetyMeasures = ref([]);

            const fsmQualityLevel = ref(null);
            const serviceDeliveryEfficiency = ref(null);
            const overallSatisfaction = ref(null);
            const serviceQualityPrice = ref(null);

            const dissatisfactionCommentQ2 = ref('');
            const dissatisfactionCommentQ3 = ref('');
            const dissatisfactionCommentQ4 = ref('');
            const dissatisfactionCommentQ5 = ref('');

            const website = ref('');
            const formLoadedAt = ref(Math.floor(Date.now() / 1000));

            const errorMessage = ref('');
            const fieldErrors = ref({});
            const isSubmitting = ref(false);
            const isFetchingData = ref(false);
            const showModal = ref(false);
            const countdown = ref(5);
            let countdownTimer = null;

            async function fetchApplicationData() {
                if (!applicationId.value) return;

                isFetchingData.value = true;
                errorMessage.value = ''; // Clear previous errors

                try {
                    const res = await fetch(`/feedback-application-data?application_id=${encodeURIComponent(applicationId.value)}`);
                    const data = await res.json();
                    if (data.success) {
                        customerName.value = data.data.customer_name || '';
                        customerNumber.value = data.data.customer_contact || '';
                        customerGender.value = data.data.customer_gender || '';
                        serviceProviderName.value = data.data.service_provider_name || '';
                        serviceProviderContact.value = data.data.service_provider_contact || '';
                    } else {
                        errorMessage.value = data.message;
                        customerName.value = customerNumber.value = customerGender.value = '';
                        serviceProviderName.value = serviceProviderContact.value = '';
                    }
                } catch (err) {
                    console.error('Error:', err);
                    errorMessage.value = 'Error fetching application data. Please try again.';
                } finally {
                    isFetchingData.value = false;
                }
            }

            function resetForm() {
                applicationId.value = '';
                customerName.value = '';
                customerNumber.value = '';
                customerGender.value = '';
                serviceProviderName.value = '';
                serviceProviderContact.value = '';

                safetyMeasures.value = [];

                fsmQualityLevel.value = null;
                serviceDeliveryEfficiency.value = null;
                overallSatisfaction.value = null;
                serviceQualityPrice.value = null;

                dissatisfactionCommentQ2.value = '';
                dissatisfactionCommentQ3.value = '';
                dissatisfactionCommentQ4.value = '';
                dissatisfactionCommentQ5.value = '';

                website.value = '';
                formLoadedAt.value = Math.floor(Date.now() / 1000);
            }

            async function handleSubmit() {
                errorMessage.value = '';
                fieldErrors.value = {}; // Clear previous errors

                // Field-by-field validation
                if (!applicationId.value) {
                    fieldErrors.value.application_id = 'Application Number is required';
                }
                if (!customerName.value) {
                    fieldErrors.value.customer_name = 'Service Receiver Name is required';
                }
                if (!customerNumber.value) {
                    fieldErrors.value.customer_number = 'Service Receiver Contact is required';
                }
                if (safetyMeasures.value.length === 0) {
                    fieldErrors.value.safety_measures = 'Please select at least one safety measure';
                }
                if (!fsmQualityLevel.value) {
                    fieldErrors.value.fsm_quality_level = 'Please rate the attitude of emptiers';
                }
                if (!serviceDeliveryEfficiency.value) {
                    fieldErrors.value.service_delivery_efficiency = 'Please rate the response time';
                }
                if (!overallSatisfaction.value) {
                    fieldErrors.value.overall_satisfaction = 'Please rate overall satisfaction';
                }
                if (!serviceQualityPrice.value) {
                    fieldErrors.value.service_quality_price = 'Please rate price satisfaction';
                }

                // If any field errors exist, stop submission
                if (Object.keys(fieldErrors.value).length > 0) {
                    errorMessage.value = 'Please fix the errors before submitting';
                    return;
                }

                isSubmitting.value = true;
                const payload = new FormData();
                payload.append('application_id', applicationId.value);
                payload.append('customer_name', customerName.value);
                payload.append('customer_number', customerNumber.value);
                payload.append('customer_gender', customerGender.value);
                payload.append('service_provider_name', serviceProviderName.value);
                payload.append('service_provider_contact', serviceProviderContact.value);

                payload.append('safety_measures', safetyMeasures.value.join(', '));

                payload.append('fsm_quality_level', fsmQualityLevel.value || '');
                payload.append('service_delivery_efficiency', serviceDeliveryEfficiency.value || '');
                payload.append('overall_satisfaction', overallSatisfaction.value || '');
                payload.append('service_quality_price', serviceQualityPrice.value || '');
                payload.append('dissatisfaction_comment_q2', dissatisfactionCommentQ2.value);
                payload.append('dissatisfaction_comment_q3', dissatisfactionCommentQ3.value);
                payload.append('dissatisfaction_comment_q4', dissatisfactionCommentQ4.value);
                payload.append('dissatisfaction_comment_q5', dissatisfactionCommentQ5.value);

                payload.append('website', website.value);
                payload.append('form_loaded_at', formLoadedAt.value);

                try {
                    const res = await fetch('/public-feedback', {
                        method: 'POST',
                        body: payload,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        showModal.value = true;
                        resetForm();
                    } else {
                        errorMessage.value = data.message || 'An error occurred. Please try again.';
                    }
                } catch (err) {
                    console.error(err);
                    errorMessage.value = 'An error occurred while submitting feedback. Please try again.';
                } finally {
                    isSubmitting.value = false;
                }
            }

            function closeFeedbackModal() {
                showModal.value = false;
            }

            watch(showModal, (val) => {
                if (val) {
                    countdown.value = 5;
                    countdownTimer = setInterval(() => {
                        countdown.value--;
                        if (countdown.value <= 0) {
                            closeFeedbackModal();
                        }
                    }, 1000);
                } else {
                    if (countdownTimer) {
                        clearInterval(countdownTimer);
                        countdownTimer = null;
                    }
                }
            });

            return {
                applicationId,
                customerName,
                customerNumber,
                customerGender,
                serviceProviderName,
                serviceProviderContact,
                safetyMeasures,
                fsmQualityLevel,
                serviceDeliveryEfficiency,
                overallSatisfaction,
                serviceQualityPrice,
                dissatisfactionCommentQ2,
                dissatisfactionCommentQ3,
                dissatisfactionCommentQ4,
                dissatisfactionCommentQ5,
                website,
                formLoadedAt,
                errorMessage,
                fieldErrors,
                isSubmitting,
                isFetchingData,
                showModal,
                countdown,
                fetchApplicationData,
                handleSubmit,
                closeFeedbackModal
            };
        }
    }

    const router = createRouter({
        history: createWebHashHistory(),      // hash history mode
        routes: [
            { path: '/', component: Login},
            { path: '/about', component: About},
            { path: '/dashboard', component: Dashboard},
            { path: '/fsm', component: FsmApplication},
            { path: '/feedback', component: Feedback},
            { path: '/contact', component: Contact }
        ]
    });

    const app = createApp();
    app.use(router);
    app.mount('#vue_app');
    </script>
    @stack('scripts')
</body>

</html>