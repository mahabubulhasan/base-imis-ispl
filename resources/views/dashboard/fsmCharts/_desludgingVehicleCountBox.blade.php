@include('layouts.dashboard.card', [
    'number' => number_format($desludgingVehicleCount, 0),

    'heading' => __("Desludging Vehicles"), // Removed Blade syntax
    'image' => asset('img/svg/imis-icons/desludgingVehicle.svg'),

])
