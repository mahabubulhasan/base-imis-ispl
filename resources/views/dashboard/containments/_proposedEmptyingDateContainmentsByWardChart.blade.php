<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@include('layouts.dashboard.chart-card',[
    'card_title' => __("Wardwise Distribution of Emptying Requests for the Next Four Weeks"),
    'export_chart_btn_id' => "exportproposedEmptiedDateContainmentsByWardChart",
    'canvas_id' => "proposedEmptiedDateContainmentsByWardChart"
])
@push('scripts')
<script>
var ctx = document.getElementById("proposedEmptiedDateContainmentsByWardChart");
var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [<?php echo implode(',', $proposedEmptiedDateContainmentsByWardChart['labels']); ?>],
    datasets: [
        {
            label: "No. of requests",
            backgroundColor: "rgba(54, 162, 235,0.5)",
            hoverBackgroundColor: "rgba(54, 162, 235,0.7)",
            data: [<?php echo implode(',', $proposedEmptiedDateContainmentsByWardChart['values']); ?>],
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
                display: true,
                labelString: 'Wards',
        //fontSize: 10,
      },}],
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
document.getElementById('exportproposedEmptiedDateContainmentsByWardChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#proposedEmptiedDateContainmentsByWardChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = 'Wardwise Distribution of Emptying Requests for the Next Four Weeks.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush
