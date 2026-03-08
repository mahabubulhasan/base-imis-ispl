<!-- Last Modified: February 11, 2026
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
    <nav class="fixed top-0 w-full z-50 glass-nav border-b border-slate-200">
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
                <div class="flex flex-wrap gap-4 hidden">
                    <button onclick="document.getElementById('dashboardBtn').click()" class="bg-primary text-white px-8 py-4 rounded-xl font-bold hover:shadow-lg hover:shadow-primary/25 transition-all flex items-center group">
                        Explore Dashboard
                        <span class="material-icons ml-2 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </button>
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
        <div class="pt-5">
            Implemented by <a href="https://streamstech.com" target="_blank" rel="noopener noreferrer"
                class="text-primary font-semibold hover:underline">Streams Tech Ltd.</a> |
            © {{ config('constants.SITE_NAME') }} {{ \Carbon\Carbon::now()->format('Y') }}. All rights reserved.
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

    <!-- somewhere in the <body> (above the closing </body>) -->
    <script type="importmap">
        {
        "imports": {
            "vue": "https://unpkg.com/vue@3/dist/vue.esm-browser.js",
            "vue-router": "https://unpkg.com/vue-router@4/dist/vue-router.esm-browser.js",
            "@vue/devtools-api": "https://unpkg.com/@vue/devtools-api@8/dist/vue-devtools-api.esm-browser.js"
        }
    }
    </script>
    <script type="module">
        import { createApp, ref, watch } from 'vue';
        import { createRouter, createWebHashHistory, createWebHistory } from 'vue-router';

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
        template: '#fsmPage'
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
            const advertisingMedia = ref([]);

            const fsmQualityLevel = ref(null);
            const serviceDeliveryEfficiency = ref(null);
            const overallSatisfaction = ref(null);
            const serviceQualityPrice = ref(null);

            const dissatisfactionCommentQ2 = ref('');
            const dissatisfactionCommentQ3 = ref('');
            const dissatisfactionCommentQ4 = ref('');
            const dissatisfactionCommentQ5 = ref('');

            const paymentMechanismSatisfied = ref(null);
            const paymentMechanismComments = ref('');

            const applyInFuture = ref(null);
            const applyInFutureComments = ref('');

            const recommendService = ref(null);
            const recommendServiceComments = ref('');

            const website = ref('');
            const formLoadedAt = ref(Math.floor(Date.now() / 1000));

            const errorMessage = ref('');
            const isSubmitting = ref(false);
            const showModal = ref(false);
            const countdown = ref(5);
            let countdownTimer = null;

            async function fetchApplicationData() {
                if (!applicationId.value) return;
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
                advertisingMedia.value = [];

                fsmQualityLevel.value = null;
                serviceDeliveryEfficiency.value = null;
                overallSatisfaction.value = null;
                serviceQualityPrice.value = null;

                dissatisfactionCommentQ2.value = '';
                dissatisfactionCommentQ3.value = '';
                dissatisfactionCommentQ4.value = '';
                dissatisfactionCommentQ5.value = '';

                paymentMechanismSatisfied.value = null;
                paymentMechanismComments.value = '';

                applyInFuture.value = null;
                applyInFutureComments.value = '';

                recommendService.value = null;
                recommendServiceComments.value = '';

                website.value = '';
                formLoadedAt.value = Math.floor(Date.now() / 1000);
            }

            async function handleSubmit() {
                errorMessage.value = '';

                // simple validation
                if (!applicationId.value) { errorMessage.value = 'Please enter Application Number'; return; }
                if (!customerName.value) { errorMessage.value = 'Please enter Service Receiver Name'; return; }
                if (!customerNumber.value) { errorMessage.value = 'Please enter Service Receiver Contact'; return; }
                if (safetyMeasures.value.length === 0) { errorMessage.value = 'Please select at least one safety measure'; return; }
                if (advertisingMedia.value.length === 0) { errorMessage.value = 'Please select how you heard about the service'; return; }

                isSubmitting.value = true;
                const payload = new FormData();
                payload.append('application_id', applicationId.value);
                payload.append('customer_name', customerName.value);
                payload.append('customer_number', customerNumber.value);
                payload.append('customer_gender', customerGender.value);
                payload.append('service_provider_name', serviceProviderName.value);
                payload.append('service_provider_contact', serviceProviderContact.value);

                payload.append('safety_measures', safetyMeasures.value.join(', '));
                payload.append('advertising_media', advertisingMedia.value.join(', '));

                payload.append('fsm_quality_level', fsmQualityLevel.value || '');
                payload.append('service_delivery_efficiency', serviceDeliveryEfficiency.value || '');
                payload.append('overall_satisfaction', overallSatisfaction.value || '');
                payload.append('service_quality_price', serviceQualityPrice.value || '');
                payload.append('dissatisfaction_comment_q2', dissatisfactionCommentQ2.value);
                payload.append('dissatisfaction_comment_q3', dissatisfactionCommentQ3.value);
                payload.append('dissatisfaction_comment_q4', dissatisfactionCommentQ4.value);
                payload.append('dissatisfaction_comment_q5', dissatisfactionCommentQ5.value);

                payload.append('payment_mechanism_satisfied', paymentMechanismSatisfied.value || '');
                payload.append('payment_mechanism_comments', paymentMechanismComments.value);
                payload.append('apply_in_future', applyInFuture.value || '');
                payload.append('apply_in_future_comments', applyInFutureComments.value);
                payload.append('recommend_service', recommendService.value || '');
                payload.append('recommend_service_comments', recommendServiceComments.value);

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
                advertisingMedia,
                fsmQualityLevel,
                serviceDeliveryEfficiency,
                overallSatisfaction,
                serviceQualityPrice,
                dissatisfactionCommentQ2,
                dissatisfactionCommentQ3,
                dissatisfactionCommentQ4,
                dissatisfactionCommentQ5,
                paymentMechanismSatisfied,
                paymentMechanismComments,
                applyInFuture,
                applyInFutureComments,
                recommendService,
                recommendServiceComments,
                website,
                formLoadedAt,
                errorMessage,
                isSubmitting,
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