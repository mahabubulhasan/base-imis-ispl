@include('layouts.dashboard.chart-card', [
    'card_title' => __("Performance of Municipal Treatment Plants for Last Five Years"),
    'export_chart_btn_id' => "exportTreatmentPlantChart",
    'canvas_id' => "treatmentPlantChart"
])

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Assuming the data comes from the $treatmentPlantTest variable
    var sqlResult = @json($treatmentPlantTest);

    // Extract unique treatment plant names and years from the data
    var treatmentPlantNames = [...new Set(sqlResult.map(item => item.treatment_plant_name))];
    var years = [...new Set(sqlResult.map(item => item.year))];

    // Define a fixed set of colors for each treatment plant
    var fixedColors = ["#ffb964", "#023047", "#219EBC", "#8ECAE6", "#9D0208", "#FFD166", "#06D6A0", "#FF6B6B"];
    var belowStandardColor = "#CCCCCC"; // Grey color for below standard

    // Organize data for Chart.js: Create one dataset for standardmeet and one for belowstandard for each treatment plant
    var datasets = [];
    treatmentPlantNames.forEach((plantName, index) => {
    // Get the standardmeet and belowstandard values for each year
    var standardMeetData = years.map(year => {
        const dataItem = sqlResult.find(item => item.treatment_plant_name === plantName && item.year === year);
        return dataItem ? dataItem.standardmeet : 0; // Use 0 if no data
    });

    var belowStandardData = years.map(year => {
        const dataItem = sqlResult.find(item => item.treatment_plant_name === plantName && item.year === year);
        return dataItem ? dataItem.standardnotmeet : 0; // Use 0 if no data
    });

    // Add the standardmeet dataset
    datasets.push({
        label: plantName, // Use only the plant name in the legend for standardmeet
        backgroundColor: fixedColors[index % fixedColors.length], // Color for standardmeet
        data: standardMeetData, // Data for standardmeet
        stack: plantName, // Stack under the treatment plant
    });

    // Check if belowStandardData contains any non-zero values before adding the dataset
    if (belowStandardData.some(value => value > 0)) {
        datasets.push({
            label: plantName + ' - Non Compliance', // Include the plant name with '- Below Standard' in the legend
            backgroundColor: belowStandardColor, // Grey color for below standard
            borderColor: fixedColors[index % fixedColors.length], // Border color matches the plant's color
            borderWidth: 2, // Set border width to make it distinguishable
            data: belowStandardData, // Data for belowstandard
            stack: plantName, // Stack under the treatment plant
        });
    }
});


    // Combine all datasets for chart data
    var chartData = {
        labels: years, // X-axis labels (the years)
        datasets: datasets, // Stacked datasets for all treatment plants
    };

    // Chart options
    var options = {
        scales: {
            x: {
                stacked: true,  // Stack the bars horizontally
            },
            y: {
                stacked: true,  // Stack the bars vertically
            },
        },
        scales: {
      xAxes: [{
                scaleLabel: {
                    display: true, // Enable the scale label
                    labelString: 'Year' // The label text
                }
            }],},
        responsive: true,
        legend: {
            display: true,
            position: 'bottom',
            align: 'start',
            labels: {
                boxWidth: 10,
            },
        },
    };

    // Initialize the chart
    var ctx = document.getElementById('treatmentPlantChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: options,
    });
});

document.getElementById('exportTreatmentPlantChart').addEventListener("click", downloadIMG);

// Function to download the chart as an image
function downloadIMG() {
    var newCanvas = document.querySelector('#treatmentPlantChart');
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href = newCanvasImg;
    a.download = 'Performance of Municipal Treatment Plants for Last Five Years.png';
    a.click();
}


</script>
@endpush
