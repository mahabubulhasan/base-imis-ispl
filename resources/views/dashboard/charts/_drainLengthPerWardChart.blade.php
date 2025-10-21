<!-- Last Modified Date: 16-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->
@include('layouts.dashboard.chart-card',[
    'card_title' =>__('Wardwise Drain Length (m)') ,
    'export_chart_btn_id' => "exportdrainLengthPerWardChart",
    'canvas_id' => "drainLengthPerWardChart"
])
@push('scripts')
    <script>
        var ctx = document.getElementById("drainLengthPerWardChart");
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', $drainLengthPerWardChart['labels']); ?>],
                datasets: [
                    {
                        label: "Wardwise Drain Length(m)",
                        backgroundColor: "rgb(157,208,246)",
                        borderColor: "rgb(157,208,246)",
                        borderWidth: 1,
                        hoverBackgroundColor: "rgb(157,208,246)",
                        hoverBorderColor: "rgb(157,208,246)",
                        data: [<?php echo implode(',', $drainLengthPerWardChart['values']); ?>],
                    }
                ]
            },
            options: {
                animation:{
                    animateScale:true
                },
                scales: {
                    xAxes: [{

            scaleLabel: {
                            display: true,
                            labelString: 'Wards'
                        },
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
        document.getElementById('exportdrainLengthPerWardChart').addEventListener("click", downloadIMG);
        //donwload pdf from original canvas
        function downloadIMG() {
            var newCanvas = document.querySelector('#drainLengthPerWardChart');

            //create image from dummy canvas
            var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
            var a = document.createElement('a');
            a.href =newCanvas.toDataURL("image/png", 1.0);

            a.download = 'Drain Length.png';

            // Trigger the download
            a.click();
        }
    </script>
@endpush
