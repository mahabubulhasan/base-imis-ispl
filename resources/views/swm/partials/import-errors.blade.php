@if(session('import_errors'))
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach(session('import_errors') as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif
