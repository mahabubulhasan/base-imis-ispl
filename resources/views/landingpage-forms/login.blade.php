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
                <h3 class="text-[#0056b3] text-xl md:text-2xl mb-5 md:mb-6 text-center font-semibold">Login
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
                        <span class="relative">SUBMIT</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
