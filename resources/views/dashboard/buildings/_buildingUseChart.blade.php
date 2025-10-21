@include('layouts.dashboard.chart-card',[
     'card_title' => __("Building Use Composition"),

    'export_chart_btn_id' => "exportbuildingUseChart",
    'canvas_id' => "buildingUseChart"
])





@push('scripts')
<script>
var ctx = document.getElementById("buildingUseChart");
var myChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: [<?php echo implode(',', $buildingUseChart['labels']); ?>],
    datasets: [
        {
            label: " Building Use Composition",
            backgroundColor: [<?php echo implode(',', $buildingUseChart['colors']); ?>],
            hoverBackgroundColor: [<?php echo implode(',', $buildingUseChart['colors']); ?>],
            borderWidth: 1,
            data: [<?php echo implode(',', $buildingUseChart['values']); ?>],
        }
    ]
},
  options: {
    animation:{
      animateScale:true
    },
    responsive: true,
      legend: {
         display: true,
         position: 'right',
         align: 'middle',
         labels: {
              boxWidth: 10
          }
      },
  }
});
document.getElementById('exportbuildingUseChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#buildingUseChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = 'Building Structures by building use.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush
