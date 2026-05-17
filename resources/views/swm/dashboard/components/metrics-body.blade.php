@foreach($module['submodules'] ?? [] as $submodule)
    @include('swm.dashboard.components.submodule', ['submodule' => $submodule])
@endforeach
