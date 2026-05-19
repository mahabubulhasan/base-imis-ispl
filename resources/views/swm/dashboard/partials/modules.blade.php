@foreach($dashboard['modules'] ?? [] as $key => $modulePayload)
    @php $viewName = $dashboard['moduleViews'][$key] ?? null; @endphp
    @if($viewName)
        @include($viewName, ['module' => $modulePayload])
    @endif
@endforeach
