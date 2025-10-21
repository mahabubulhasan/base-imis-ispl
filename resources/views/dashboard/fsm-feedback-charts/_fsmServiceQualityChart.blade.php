@include('layouts.dashboard.chart-card',[
    'card_title' => __("Customer Satisfaction with FSM Service Quality"),
    'export_chart_btn_id' => "exportfsmSrvcQltyChart",

    'canvas_id' => "fsmSrvcQltyChart"
])

@push('scripts')
<script>
var ctx = document.getElementById("fsmSrvcQltyChart");
var myChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: [<?php echo implode(',', $fsmSrvcQltyChart['labels']); ?>],
    datasets: [
        {
            label: "Building Structures by building use",
            backgroundColor: [<?php echo implode(',', $fsmSrvcQltyChart['colors']); ?>],
            hoverBackgroundColor: [<?php echo implode(',', $fsmSrvcQltyChart['hoverBackgroundColor']); ?>],
            data: [<?php echo implode(',', $fsmSrvcQltyChart['values']); ?>],
        }
    ]
},
  options: {
    animation:{
      animateScale:true
    }
  }
});
document.getElementById('exportfsmSrvcQltyChart').addEventListener("click", downloadIMG);
document.getElementById('year');
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#fsmSrvcQltyChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = ' Customer Satisfaction with FSM Service Quality.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush
