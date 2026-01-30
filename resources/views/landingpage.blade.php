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

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('layout/css/styles.css') }}">
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

    <!-- PUBLIC DASHBOARD -->
    <div id="about" class="tab-content hidden p-5 md:p-10 bg-transparent min-h-[calc(100vh-100px)] animate-fadeIn">
        <div class="max-w-6xl mx-auto bg-white p-6 md:p-10 rounded-2xl shadow-2xl">
            <h2 class="text-[#1f3b7d] text-2xl md:text-3xl lg:text-4xl mb-5 border-b-4 border-[#0056b3] pb-4">About</h2>
            About sectoin text goes here.
        </div>
    </div>

    <!-- PUBLIC DASHBOARD -->
    <div id="dashboard" class="tab-content hidden p-5 md:p-10 bg-transparent min-h-[calc(100vh-100px)] animate-fadeIn">
        <div class="max-w-6xl mx-auto bg-white p-6 md:p-10 rounded-2xl shadow-2xl">
            <h2 class="text-[#1f3b7d] text-2xl md:text-3xl lg:text-4xl mb-5 border-b-4 border-[#0056b3] pb-4">Public
                Dashboard</h2>
            <p class="text-gray-600 text-base md:text-lg leading-relaxed">Welcome to the {{
                config('constants.SITE_NAME') }} Public Dashboard. This section will display general statistics,
                announcements, and municipal data to keep citizens informed about local services and developments.</p>
        </div>
    </div>

    <!-- FSM APPLICATION TAB -->
    <div id="fsm" class="tab-content hidden p-5 md:p-10 bg-transparent min-h-[calc(100vh-100px)] animate-fadeIn">
        <div class="bg-white p-5 md:p-8 rounded-2xl shadow-2xl w-full max-w-6xl mx-auto">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-center text-[#1f3b7d] mb-6 md:mb-8">FSM
                Application Form</h1>
            <div class="text-center">
                <p class="text-gray-600 mb-6">Click the button below to access the FSM Application Form:</p>
                <a href="{{ route('client-fsm-application.form') }}"
                    class="inline-block px-8 py-3 bg-gradient-to-br from-[#007bff] to-[#0056b3] text-white border-none rounded-lg text-base md:text-lg font-semibold cursor-pointer transition-all duration-300 hover:from-[#0056b3] hover:to-[#003d82] hover:-translate-y-0.5 hover:shadow-xl hover:shadow-[#0056b3]/30">
                    Open FSM Application Form
                </a>
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
</body>

</html>