@include('layouts.dashboard.chart-card',[
    'card_title' => __("Distribution of Water Supply Payment Dues"),
    'export_chart_btn_id' => "exportwaterSupplyPaymentChart",
    'canvas_id' => "waterSupplyPaymentChart"
])
@push('scripts')
<script>
var ctx = document.getElementById("waterSupplyPaymentChart");
var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [<?php echo implode(',', $waterSupplyPaymentChart['labels']); ?>],
    datasets: [
        {
            label: "Payment Due",
            backgroundColor: [<?php echo implode(',', $waterSupplyPaymentChart['background_colors']); ?>],
            hoverBackgroundColor: [<?php echo implode(',', $waterSupplyPaymentChart['colors']); ?>],
            borderWidth: 1,
            data: [<?php echo implode(',', $waterSupplyPaymentChart['values']); ?>],
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
document.getElementById('exportwaterSupplyPaymentChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#waterSupplyPaymentChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = 'Distribution of Water Supply Payment Dues.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush
