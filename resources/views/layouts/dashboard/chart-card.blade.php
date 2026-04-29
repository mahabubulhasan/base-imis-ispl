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
        @if($with_chart_loader ?? false)
        <div class="position-relative" style="min-height:250px">
            <div id="swm-chart-loader-{{ $canvas_id }}"
                 class="swm-chart-card-loader position-absolute d-flex align-items-center justify-content-center w-100 h-100 bg-white"
                 style="top:0;left:0;z-index:3;opacity:0.96;border-radius:0.25rem"
                 data-swm-chart-loader="{{ $canvas_id }}"
                 role="status"
                 aria-live="polite"
                 aria-busy="true">
                <div class="text-center px-3">
                    <div class="spinner-border text-info mb-2" style="width:2.5rem;height:2.5rem" aria-hidden="true"></div>
                    <div class="small text-muted">{{ __('Loading…') }}</div>
                </div>
            </div>
            <canvas id="{{ $canvas_id }}" style="height:250px"></canvas>
        </div>
        @else
        <canvas id="{{ $canvas_id }}" style="height:250px"></canvas>
        @endif
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->
