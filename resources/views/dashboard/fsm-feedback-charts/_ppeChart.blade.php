@include('layouts.dashboard.chart-card',[
    'card_title' => __("Sanitation Worker Compliance with PPE Guidelines"),
    'export_chart_btn_id' => "exportppe",
    'canvas_id' => "ppe"
])
@push('scripts')
    <script>
        var ctx = document.getElementById("ppe");
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', $ppe['labels']); ?>],
                datasets: [
                    {
                        label: "Impact in Creating Public Awareness",
                        backgroundColor: [<?php echo implode(',', $ppe['colors']); ?>],
                        hoverBackgroundColor: [<?php echo implode(',', $ppe['colors']); ?>],
                        data: [<?php echo implode(',', $ppe['values']); ?>],
                    }
                ]
            },
            options: {
                animation:{
                    animateScale:true
                }
            }
        });
        document.getElementById('exportppe').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#ppe');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = 'Sanitation Worker Compliance with PPE Guidelines.png';

    // Trigger the download
    a.click();
      }
    </script>
@endpush
