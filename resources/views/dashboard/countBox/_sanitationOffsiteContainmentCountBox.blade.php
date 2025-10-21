<!-- small box -->
<div class="info-box sanitation-system-info">
  <span class="info-box-icon bg-info"><i class="fa fa-building"></i></span>
  <div class="info-box-content">
    <span class="info-box-text">
      <h3>{{ number_format($sanitationSystemOther) }}</h3> <!-- Display count if needed -->
    </span>
    <span class="info-box-number">{{__('Others')}}</span>
  </div>
  
  <span class="top-right-icon" data-tooltip="Culture & Religious<br>Agricultural & Farm">
    <i class="fa-solid fa-circle-info"></i>
    <div class="custom-tooltip">
        @foreach($sanitationSystemOthername as $system)
          {{ __($system->sanitation_system) }}<br>
        @endforeach
      </div>
  </span>
</div>
