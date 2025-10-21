@include('layouts.dashboard.chart-card',[
    'card_title' => __("Monthly Emptying Requests Processed by Service Providers"),
    'export_chart_btn_id' => "exportmonthlyAppRequestByoperators",
    'canvas_id' => "monthlyAppRequestByoperators"
])
@push('scripts')
<script>
var ctx = document.getElementById("monthlyAppRequestByoperators");
var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [<?php echo implode(',', $monthlyAppRequestByoperators['labels']); ?>],
    datasets: [
        @foreach($monthlyAppRequestByoperators['datasets'] as $dataset)
        {
            stack: <?php echo $dataset['stack']; ?>,
            label: <?php echo $dataset['label']; ?>,
            backgroundColor: <?php echo $dataset['color']; ?>,
            data: [<?php echo implode(',', $dataset['data']); ?>],
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
         labels: {
              boxWidth: 10
          }
      },
    scales: {
      xAxes: [{

           scaleLabel: {
                   display: true, // Enable the scale label
                   labelString: 'Month' // The label text
               },
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

                 },
              }

        }]
    },
    tooltips: {
        mode: 'index',
    }
  }
});

document.getElementById('exportmonthlyAppRequestByoperators').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#monthlyAppRequestByoperators');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = 'Monthly Emptying Requests Processed by Service Providers.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush
