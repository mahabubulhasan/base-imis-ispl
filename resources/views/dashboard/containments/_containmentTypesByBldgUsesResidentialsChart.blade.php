<!-- Last Modified Date: 12-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@include('layouts.dashboard.chart-card',[

    'card_title' => __("Wardwise Distribution of Containment Types in Residential Buildings"),
    'export_chart_btn_id' => "exportcontainmentTypesByBldgUsesResidentialsChart",
    'canvas_id' => "containmentTypesByBldgUsesResidentialsChart"
])
@push('scripts')
<script>
var ctx = document.getElementById("containmentTypesByBldgUsesResidentialsChart");
var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [<?php echo implode(',', $containmentTypesByBldgUsesResidentialsChart['labels']); ?>],
    datasets: [
        @foreach($containmentTypesByBldgUsesResidentialsChart['datasets'] as $dataset)
        {
            label: <?php echo $dataset['label']; ?>,
            backgroundColor: <?php echo $dataset['color']; ?>,
            data: [<?php echo implode(',', $dataset['data']); ?>],
            values:[<?php echo implode(',', $dataset['value']); ?>]
        },
        @endforeach
    ]
},
  options: {
    animation:{
      animateScale:true
    },
    responsive: true,
      legend: {
         display: true,
         position: 'bottom',
         align: 'start',
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
          },
        stacked: true,
        ticks: {
                beginAtZero: true
            }
      }],
      yAxes: [{
        stacked: true,
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
    },
    tooltips: {
        mode: 'index',
        callbacks: {
            label: function (tooltipItem, data) {
                var allData = data.datasets[tooltipItem.datasetIndex].data;
                var allValues = data.datasets[tooltipItem.datasetIndex].values;
                var tooltipLabel = data.datasets[tooltipItem.datasetIndex].label;
                var tooltipData = allData[tooltipItem.index];
                var tooltipValue = allValues[tooltipItem.index];
                return tooltipLabel + ": " + tooltipData.toFixed(2) + "% : "+tooltipValue;
            },
        }
    }
  }
});
document.getElementById('exportcontainmentTypesByBldgUsesResidentialsChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#containmentTypesByBldgUsesResidentialsChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = 'Wardwise Distribution of Containment Types in Residential Buildings.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush
