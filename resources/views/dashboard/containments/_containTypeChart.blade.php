@include('layouts.dashboard.chart-card',[

    'card_title' => __("Proportion of Different Containment Types"),
    'export_chart_btn_id' => "exportcontainTypeChart",
    'canvas_id' => "containTypeChart"
])
@push('scripts')
<script>
var ctx = document.getElementById("containTypeChart");
var myChart = new Chart(ctx, {
  type: 'pie',
  data: {
    labels: [<?php echo implode(',', $containTypeChart['labels']); ?>],
    datasets: [
        {
            label: "Proportion of Different Containment Type chart",
            backgroundColor: [<?php echo implode(',', $containTypeChart['colors']); ?>],
            hoverBackgroundColor: [<?php echo implode(',', $containTypeChart['colors']); ?>],
            borderWidth: 1,
            data: [<?php echo implode(',', $containTypeChart['values']); ?>],
        }
    ]
},
  options: {
    scales: {
            y: {
                ticks: {
                    // Include a dollar sign in the ticks
                    callback: function(value, index, ticks) {
                      return '$' + Chart.Ticks.formatters.numeric.apply(this, [value, index, ticks]);
                    }
                }
            },
    animation:{
      animateScale:true
    }
  },
  responsive: true,
      legend: {
         display: true,
         position: 'right',
         align: 'middle',
         labels: {
              boxWidth: 10,

          }
      }}
});
document.getElementById('exportcontainTypeChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#containTypeChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = 'Proportion of Different Containment Type.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush
