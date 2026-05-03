@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::open(['route' => 'swm.workers.store', 'class' => 'form-horizontal']) !!}
		@include('swm.service-providers.workers.partial-form')
	{!! Form::close() !!}
</div>
@stop

@if(empty($scopedOrganizationId))
@push('scripts')
<script>
$(function () {
    var $org = $('#organization_id');
    var $preview = $('#worker_id_no_preview');
    if (!$org.length || !$preview.length) {
        return;
    }
    var url = @json(route('swm.workers.next_worker_id'));
    function refreshWorkerIdPreview() {
        var id = $org.val();
        if (!id) {
            $preview.val('');
            return;
        }
        $.getJSON(url, { organization_id: id })
            .done(function (res) {
                $preview.val(res.worker_id_no || '');
            });
    }
    $org.on('change', refreshWorkerIdPreview);
});
</script>
@endpush
@endif
