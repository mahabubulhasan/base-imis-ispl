@include('layouts.dashboard.chart-card',[
    'card_title' => __("Property Tax Payment Due Across Time Periods"),
    'export_chart_btn_id' => "exporttaxRevenueChart",
    'canvas_id' => "taxRevenueChart"
])
@push('scripts')
<script>
var ctx = document.getElementById("taxRevenueChart");
var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [<?php echo implode(',', $taxRevenueChart['labels']); ?>],
    datasets: [
        {
            label: "Payment Due",
            backgroundColor: [<?php echo implode(',', $taxRevenueChart['background_colors']); ?>],
            hoverBackgroundColor: [<?php echo implode(',', $taxRevenueChart['colors']); ?>],
            borderWidth: 1,
            data: [<?php echo implode(',', $taxRevenueChart['values']); ?>],
        }
    ]
},
  options: {
    animation:{
      animateScale:true
    },
    responsive: true,
      legend: {
         labels: {
              boxWidth: 10
          }
      },
    scales: {
      xAxes: [{
                scaleLabel: {
                    display: true, // Enable the scale label
                    labelString: 'Duration of Payment Due' // The label text
                }
            }],
        yAxes: [{
            ticks: {
                beginAtZero: true,
                userCallback: function(label, index, labels) {
                     // when the floored value is the same as the value we have a whole number
                     if (Math.floor(label) === label) {
                         return label;
                     }

                 }
            }
        }]
    }
  }
});
document.getElementById('exporttaxRevenueChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#taxRevenueChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = 'Property Tax Payment Due Across Time Periods.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush
