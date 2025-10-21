@include ('layouts.dashboard.card', [
    'number' => number_format($sludgeCollectionEmptyingServices, 0),

    'heading' => __("Volume of Sludge Emptied (m³)"),
    'image' => asset('img/svg/imis-icons/desludgingVehicle.svg'),
])
