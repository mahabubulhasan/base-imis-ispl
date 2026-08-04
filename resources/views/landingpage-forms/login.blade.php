<!-- LOGIN FORM - Integrated into Hero Section -->
<div class="login-card w-full max-w-md p-8 md:p-10 rounded-3xl shadow-2xl border border-white/20">
    <div class="text-center mb-10">
        <h2 class="text-3xl font-bold text-primary">{{ __('login.sign_in') }}</h2>
        <p class="text-slate-500 mt-2">{{ __('login.welcome_back') }}</p>
    </div>

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

    <form method="POST" action="{{ route('login.perform') }}" class="space-y-6">
        @csrf
        <div class="space-y-2">
            <label class="text-sm font-semibold text-slate-700 ml-1">{{ __('login.email_address') }}</label>
            <div class="relative">
                <span class="material-icons absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">email</span>
                <input type="text" name="username" placeholder="{{ __('login.email_placeholder') }}" required aria-label="Username"
                    value="{{ old('username') }}"
                    class="w-full pl-12 pr-4 py-4 rounded-2xl bg-slate-50 border-none ring-1 ring-slate-200 focus:ring-2 focus:ring-primary transition-all text-slate-900 @error('username') ring-red-500 @enderror" />
            </div>
            @error('username')
            <span class="text-red-500 text-sm ml-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="space-y-2">
            <label class="text-sm font-semibold text-slate-700 ml-1">{{ __('login.password') }}</label>
            <div class="relative">
                <span class="material-icons absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">lock</span>
                <input type="password" name="password" placeholder="{{ __('login.password_placeholder') }}" required aria-label="Password"
                    class="w-full pl-12 pr-4 py-4 rounded-2xl bg-slate-50 border-none ring-1 ring-slate-200 focus:ring-2 focus:ring-primary transition-all text-slate-900 @error('password') ring-red-500 @enderror" />
            </div>
            @error('password')
            <span class="text-red-500 text-sm ml-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" name="remember" value="1" class="w-5 h-5 rounded text-primary focus:ring-primary border-slate-300" />
                <span class="text-sm text-slate-600">{{ __('login.remember_me') }}</span>
            </label>
            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-primary hover:underline">{{ __('login.forgot_password') }}</a>
            @endif
        </div>

        <button type="submit" class="w-full bg-primary text-white py-4 rounded-2xl font-bold text-lg hover:shadow-lg hover:shadow-primary/30 active:scale-[0.98] transition-all uppercase tracking-wider">
            {{ __('login.sign_in_to_portal') }}
        </button>
    </form>
</div>
