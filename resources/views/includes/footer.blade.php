<!-- Branding -->
<div class="main-footer">
    @include('includes.branding')
</div>
<!-- End Branding -->

<footer class="main-footer py-2 d-flex flex-column flex-xl-row justify-content-between align-items-start align-items-xl-center gap-2">
    <strong class="pb-0 text-left text-xl-right flex-shrink-0">
        &copy; {{ config('constants.SITE_NAME') }}. All rights reserved.
    </strong>
    <div class="text-sm text-left text-sm-right w-100">
        Developed by <a href="https://streamstech.com" class="text-primary font-semibold hover:underline" target="_blank" rel="noopener noreferrer"
        class="text-primary font-semibold hover:underline">Streams Tech Ltd.</a>
    </div>
</footer>
<aside class="control-sidebar control-sidebar-dark" >
    <div class="p-3" >
    <h4>{{ Auth::user()->name }}</h4>
    <hr class="mb-2">
    <div class="mb-4">
                <p>
                    {{implode(', ', get_current_user_roles())}}<br>
                    <small>Added at {{ Carbon\Carbon::parse(Auth::user()->created_at)->format('d F Y') }} </small>
                </p>
                <hr/>
            <div class="row">
            <div class="col-sm-6">
                {{--@if(Auth::user()->id != 1)
                <a href="{{ route('users.show', ['user' => Auth::user()->id]) }}" class="btn btn-block btn-dark">Profile</a>
                @endif--}}
            </div>
            <div class="col-sm-6"><a href="{{ route('logout.perform') }}" class="btn btn-block btn-dark" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></div>
            <form id="logout-form" action="{{ route('logout.perform') }}" method="POST" style="display: none;">
                  {{ csrf_field() }}
            </form>
          </div>
    </div>
    </div>

</aside>
