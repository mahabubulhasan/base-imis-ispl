<?php
use App\Helpers\LanguageSwitcher;

$greeting = __('Hi') . ', ' . config('constants.SITE_NAME') . ', ' . implode(', ', get_current_user_roles());
?>
<nav class="main-header navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav w-100 flex-nowrap">
        @if (request()->is('maps'))
            <a href="{{ url('/') }}" class="" >
                <span class="">
                    <img src="{{ asset('/img/logo-imis.png') }}" alt="Municipality Logo" id="map-logo"
                        style="line-height: .8; margin-right: 0.5rem; margin-top:8px; max-height:33px; width:70px">
                </span>
            </a>
        @endif

        @if (request()->is('maps'))
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button" onclick="hideImage()">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        @else
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button" onclick="toggleElements()">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        @endif

        <!-- Language Dropdown -->
        {!! LanguageSwitcher::language_switcher() !!}

        <li class="nav-item header-greeting-item flex-grow-1 min-w-0">
            <small class="header-greeting text-truncate d-block" title="{{ $greeting }}">{{ $greeting }}</small>
        </li>

        <li class="nav-item">
            <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                <i class="fas fa-th-large"></i>
            </a>
        </li>
    </ul>
</nav>

<script>
    function hideImage() {
        var logo = document.getElementById('map-logo');
        if (logo.style.display === 'none') {
            logo.style.display = 'inline';
        } else {
            logo.style.display = 'none';
            helloText.style.display = 'inline';
        }
    }
</script>
