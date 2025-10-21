@include('layouts.dashboard.chart-card',[
    'card_title' => __("Containment Types Categorized by Building Usage"),
    'export_chart_btn_id' => "exportcontainmentTypesByBldgUsesChart",
    'canvas_id' => "containmentTypesByBldgUsesChart"
])
@push('scripts')
<script>
    var ctx = document.getElementById("containmentTypesByBldgUsesChart");
    var myChart = new Chart(ctx, {
      type: 'horizontalBar',
      data: {
        labels: [<?php echo implode(',', $containmentTypesByBldgUsesChart['labels']); ?>],
        datasets: [
            @foreach($containmentTypesByBldgUsesChart['datasets'] as $dataset)
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
      indexAxis: 'y',

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
        stacked: true,
        scaleLabel: {
                    display: true, // Enable the scale label
                    labelString: 'Sanitation System' // The label text
                },
        ticks: {
                beginAtZero: true
            }
      }],
      yAxes: [{
        stacked: true,
        ticks: {
                beginAtZero: true,

            },
            display: true,
            position: 'right',
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
  document.getElementById('exportcontainmentTypesByBldgUsesChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#containmentTypesByBldgUsesChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = 'Containment Types Categorized by Building Usage.png';

    // Trigger the download
    a.click();
      }


</script>

@endpush
