@include ('layouts.dashboard.card',
 ['number' => number_format($sludgeCollectionsCount,0),
  'heading' => __('Volume of Sludge disposed on FSTP (m³)'),
  'image' => asset('img/svg/imis-icons/treatment-plants.svg')])
