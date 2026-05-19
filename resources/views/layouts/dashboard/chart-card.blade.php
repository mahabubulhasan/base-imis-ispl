@once
@push('scripts')
<script>
(function () {
    var colors = @json(config('swm_dashboard.chart_colors', [
        'yes' => 'rgba(54, 162, 235, 0.75)',
        'no' => 'rgba(251, 176, 64, 0.85)',
    ]));
    var fallbackPalette = [
        colors.yes,
        colors.no,
        'rgba(153, 202, 60, 0.85)',
        'rgba(90, 155, 212, 0.65)',
        'rgba(255, 99, 132, 0.65)',
        'rgba(153, 102, 255, 0.65)',
    ];

    window.dashboardChartColors = colors;

    window.dashboardColorForLabel = function (label, index) {
        var key = String(label || '').trim().toLowerCase();
        if (key === 'yes') {
            return colors.yes;
        }
        if (key === 'no') {
            return colors.no;
        }
        return fallbackPalette[(index || 0) % fallbackPalette.length];
    };
})();
</script>
@endpush
@endonce

<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title">{{ $card_title }}</h3>
        <div class="card-tools">
            <!-- Buttons, labels, and many other things can be placed here! -->
            
            {{-- <select id="{{ $year_id }}">
                            <option value="">All Years</option>
                            <option value="2019">2019</option>
                            <option value="2020">2020</option>
                            <option value="2021">2021</option>
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                          </select>
                 --}}
            <!-- This will cause the card to collapse when clicked -->
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            <!-- This will cause the card to maximize when clicked -->
            <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
            <!-- This will download the chart as an image -->
            <button id="{{ $export_chart_btn_id }}" type="button" class="btn btn-box-tool"><i class="fa-solid fa-image"> </i></button>
        </div>
        <!-- /.card-tools -->
    </div>
    <!-- /.card-header -->
    <div class="card-body collapse show">
        <canvas id="{{ $canvas_id }}" style="height:250px"></canvas>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
