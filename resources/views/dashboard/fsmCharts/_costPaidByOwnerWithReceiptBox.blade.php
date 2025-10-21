@include ('layouts.dashboard.card',
['number' => number_format($costPaidByOwnerWithReceipt,0) ,
 'heading' => __("Revenue Generated"),
 'image' => asset('img/svg/imis-icons/payment-financial-transaction.svg')])

