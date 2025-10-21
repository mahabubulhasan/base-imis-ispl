@include ('layouts.dashboard.card',
 ['number' => number_format($emptyingServiceCount) ,
  'heading' => __("Applications Responded"),
  'image' => asset('img/svg/imis-icons/applications-responded.svg')])
