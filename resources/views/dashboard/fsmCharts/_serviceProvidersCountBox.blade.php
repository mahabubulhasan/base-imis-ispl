@include ('layouts.dashboard.card', [
    'number' => number_format($serviceProviderCount),
    'heading' => __("Service Providers"), // Removed Blade syntax
    'image' => asset('img/svg/imis-icons/serviceProvider.svg'),
])
