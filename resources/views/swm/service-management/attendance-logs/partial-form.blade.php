@push('style')
<style>
@media (max-width: 991.98px) {
    .swm-attendance-form-mobile.app-mobile-form .select2-container {
        width: 100% !important;
    }
}
</style>
@endpush
@php
    $isEdit = isset($attendanceLog) && $attendanceLog;
    $orgFieldVal = old('organization_id', $isEdit ? $attendanceLog->organization_id : ($scopedOrganizationId ?? null));
    $workerFieldVal = old('worker_id', $isEdit ? $attendanceLog->worker_id : null);
    $entryVal = old('entry_at');
    if ($entryVal === null && $isEdit && $attendanceLog->entry_at) {
        $entryVal = $attendanceLog->entry_at->format('Y-m-d\TH:i');
    }
    $statusVal = old('attendance_status', $isEdit ? $attendanceLog->attendance_status : \App\Models\Swm\AttendanceLog::STATUS_PRESENT);
    $checkInVal = old('check_in_at');
    if ($checkInVal === null && $isEdit && $attendanceLog->check_in_at) {
        $checkInVal = $attendanceLog->check_in_at->format('Y-m-d\TH:i');
    }
    $checkOutVal = old('check_out_at');
    if ($checkOutVal === null && $isEdit && $attendanceLog->check_out_at) {
        $checkOutVal = $attendanceLog->check_out_at->format('Y-m-d\TH:i');
    }
@endphp
<div class="swm-attendance-form-mobile app-mobile-form">
<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('entry_at', __('Entry Date and Time'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            <input type="datetime-local" name="entry_at" id="entry_at" class="form-control" value="{{ $entryVal }}" />
        </div>
    </div>
    @if($scopedOrganizationId)
        {!! Form::hidden('organization_id', $scopedOrganizationId) !!}
        <div class="form-group row">
            {!! Form::label('organization_display', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                <p class="form-control-plaintext">{{ $organizations[$scopedOrganizationId] ?? '' }}</p>
            </div>
        </div>
    @else
        <div class="form-group row required">
            {!! Form::label('organization_id', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::select('organization_id', $organizations, $orgFieldVal, ['class' => 'form-control chosen-select', 'id' => 'organization_id', 'placeholder' => __('Select Organization')]) !!}
            </div>
        </div>
    @endif

    <div class="form-group row required">
        {!! Form::label('worker_id', __('Worker Name-ID'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            <select name="worker_id" id="worker_id" class="form-control" style="width:100%" data-placeholder="{{ __('Search worker') }}" @if(!$orgFieldVal) disabled @endif></select>
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('department', __('Department'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::text('department', old('department', $isEdit ? $attendanceLog->department : null), ['class' => 'form-control', 'id' => 'department', 'placeholder' => __('Department'), 'autocomplete' => 'off']) !!}
        </div>
    </div>

    <div class="form-group row">
        <label for="work_type_display" class="col-sm-3 control-label">{{ __('Worker Type') }}</label>
        <div class="col-sm-9">
            <input type="text"
                id="work_type_display"
                class="form-control bg-light"
                value="{{ old('work_type_display', $isEdit ? ($attendanceLog->work_type_name ?? '') : '') }}"
                readonly
                tabindex="-1"
                autocomplete="off" />
        </div>
    </div>

    <div class="form-group row">
        <label for="supervisor_display" class="col-sm-3 control-label">{{ __("Supervisor's Name") }}</label>
        <div class="col-sm-9">
            <input type="text"
                id="supervisor_display"
                class="form-control bg-light"
                value="{{ old('supervisor_display', $isEdit ? ($attendanceLog->supervisor_name ?? '') : '') }}"
                readonly
                tabindex="-1"
                autocomplete="off" />
        </div>
    </div>

    <div class="form-group row">
        <label for="attendance_vehicle_id" class="col-sm-3 control-label">{{ __('Vehicle (optional)') }}</label>
        <div class="col-sm-9">
            <select id="attendance_vehicle_id" class="form-control" style="width:100%" data-placeholder="{{ __('Search vehicle by number') }}" @if(!$orgFieldVal) disabled @endif></select>
            <small class="form-text text-muted">{{ __('If the vehicle has a default dumping landfill, landfill and waste types are shown below.') }}</small>
        </div>
    </div>

    <div class="form-group row">
        <label for="attendance_landfill_display" class="col-sm-3 control-label">{{ __('Default dumping landfill') }}</label>
        <div class="col-sm-9">
            <input type="text"
                id="attendance_landfill_display"
                class="form-control bg-light"
                value=""
                readonly
                tabindex="-1"
                autocomplete="off" />
        </div>
    </div>

    <div class="form-group row">
        <label for="attendance_waste_types_display" class="col-sm-3 control-label">{{ __('Landfill waste types') }}</label>
        <div class="col-sm-9">
            <textarea id="attendance_waste_types_display"
                class="form-control bg-light"
                rows="2"
                readonly
                tabindex="-1"
                autocomplete="off"></textarea>
        </div>
    </div>

    

    <div class="form-group row required">
        {!! Form::label('attendance_status', __('Attendance Status'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::select('attendance_status', $statusOptions, $statusVal, ['class' => 'form-control', 'id' => 'attendance_status']) !!}
        </div>
    </div>

    <div id="present-times-group" class="form-group row" style="{{ $statusVal === \App\Models\Swm\AttendanceLog::STATUS_PRESENT ? '' : 'display:none' }}">
        {!! Form::label('check_in_at', __('Check-in Time'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-md-4 mb-2 mb-md-0">
            <input type="datetime-local" name="check_in_at" id="check_in_at" class="form-control" value="{{ $checkInVal }}" />
        </div>
        <div class="w-100 d-none d-md-block"></div>
        {!! Form::label('check_out_at', __('Check-out Time'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-md-4">
            <input type="datetime-local" name="check_out_at" id="check_out_at" class="form-control" value="{{ $checkOutVal }}" />
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('remarks', __('Remarks'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::textarea('remarks', old('remarks', $isEdit ? $attendanceLog->remarks : null), ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Remarks')]) !!}
        </div>
    </div>
</div>
<div class="card-footer">
    <a href="{{ route('swm.attendance-logs.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div>
</div>

@push('scripts')
<script>
$(function () {
    var workersUrl = @json(route('swm.attendance-logs.suggestions.workers'));
    var workerContextUrl = @json(route('swm.attendance-logs.worker-context'));
    var attendanceVehiclesUrl = @json(route('swm.attendance-logs.suggestions.vehicles'));
    var attendanceVehicleContextUrl = @json(route('swm.attendance-logs.vehicle-context'));
    var csrf = @json(csrf_token());
    var initialWorkerId = @json($workerFieldVal ? (string) $workerFieldVal : null);
    var initialWorkerText = @json($isEdit && isset($attendanceLog) && $attendanceLog->worker ? ($attendanceLog->worker->name . ($attendanceLog->worker->worker_id_no ? ' — ' . $attendanceLog->worker->worker_id_no : '')) : null);

    function orgId() {
        @if($scopedOrganizationId)
        return String(@json((string) $scopedOrganizationId));
        @else
        var v = $('#organization_id').val();
        return v ? String(v) : '';
        @endif
    }

    function syncPresentTimes() {
        var st = $('#attendance_status').val();
        if (st === @json(\App\Models\Swm\AttendanceLog::STATUS_PRESENT)) {
            $('#present-times-group').show();
        } else {
            $('#present-times-group').hide();
            $('#check_in_at').val('');
            $('#check_out_at').val('');
        }
    }

    $('#attendance_status').on('change', syncPresentTimes);
    syncPresentTimes();

    function destroyWorkerSelect2() {
        var $w = $('#worker_id');
        if ($w.data('select2')) {
            $w.select2('destroy');
        }
    }

    function fetchWorkerContext() {
        var oid = orgId();
        var wid = $('#worker_id').val();
        if (!oid || !wid) {
            $('#work_type_display').val('');
            $('#supervisor_display').val('');
            $('#department').val('');
            return;
        }
        $.ajax({
            url: workerContextUrl,
            dataType: 'json',
            data: { organization_id: oid, worker_id: wid },
            headers: { 'X-CSRF-TOKEN': csrf }
        }).done(function (data) {
            if (!data || data.error) {
                return;
            }
            $('#work_type_display').val(data.work_type_name != null ? String(data.work_type_name) : '');
            $('#supervisor_display').val(data.supervisor_name != null ? String(data.supervisor_name) : '');
            $('#department').val(data.department != null ? String(data.department) : '');
        }).fail(function (xhr) {
            if (window.console && console.warn) {
                console.warn('worker-context failed', xhr.status, xhr.responseJSON || xhr.responseText);
            }
        });
    }

    function initWorkerSelect2() {
        destroyWorkerSelect2();
        var oid = orgId();
        var $w = $('#worker_id');
        $w.prop('disabled', !oid);
        if (!oid) {
            return;
        }
        $w.select2({
            placeholder: $w.data('placeholder'),
            allowClear: true,
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: workersUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        organization_id: oid,
                        q: params.term || ''
                    };
                },
                processResults: function (data) {
                    return { results: data.results || [] };
                },
                headers: { 'X-CSRF-TOKEN': csrf }
            }
        });

        if (initialWorkerId && initialWorkerText) {
            var opt = new Option(initialWorkerText, initialWorkerId, true, true);
            $w.append(opt).trigger('change');
            initialWorkerId = null;
            initialWorkerText = null;
        }
    }

    function destroyAttendanceVehicleSelect2() {
        var $v = $('#attendance_vehicle_id');
        if ($v.data('select2')) {
            $v.select2('destroy');
        }
    }

    function applyAttendanceVehicleContext(data) {
        if (!data || data.error) {
            return;
        }
        $('#attendance_landfill_display').val(data.landfill_name != null ? String(data.landfill_name) : '');
        var lines = [];
        if (Array.isArray(data.waste_types)) {
            data.waste_types.forEach(function (t) {
                if (t && t.name != null && String(t.name).length) {
                    lines.push(String(t.name));
                }
            });
        }
        $('#attendance_waste_types_display').val(lines.join(', '));
    }

    function fetchAttendanceVehicleContext() {
        var oid = orgId();
        var vid = $('#attendance_vehicle_id').val();
        if (!oid || !vid) {
            return;
        }
        $.ajax({
            url: attendanceVehicleContextUrl,
            dataType: 'json',
            data: { organization_id: oid, vehicle_id: vid },
            headers: { 'X-CSRF-TOKEN': csrf }
        }).done(applyAttendanceVehicleContext).fail(function (xhr) {
            if (window.console && console.warn) {
                console.warn('attendance vehicle-context failed', xhr.status, xhr.responseJSON || xhr.responseText);
            }
        });
    }

    function initAttendanceVehicleSelect2() {
        destroyAttendanceVehicleSelect2();
        var oid = orgId();
        var $v = $('#attendance_vehicle_id');
        $v.prop('disabled', !oid);
        $('#attendance_landfill_display').val('');
        $('#attendance_waste_types_display').val('');
        if (!oid) {
            return;
        }
        $v.select2({
            placeholder: $v.data('placeholder'),
            allowClear: true,
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: attendanceVehiclesUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        organization_id: oid,
                        q: params.term || ''
                    };
                },
                processResults: function (data) {
                    return { results: data.results || [] };
                },
                headers: { 'X-CSRF-TOKEN': csrf }
            }
        });
    }

    initWorkerSelect2();
    initAttendanceVehicleSelect2();

    $('#worker_id').on('change', function () {
        fetchWorkerContext();
    });

    $('#attendance_vehicle_id').on('change', function () {
        var vid = $('#attendance_vehicle_id').val();
        if (!vid) {
            $('#attendance_landfill_display').val('');
            $('#attendance_waste_types_display').val('');
            return;
        }
        fetchAttendanceVehicleContext();
    });

    $('#attendance_vehicle_id').on('select2:clear', function () {
        $('#attendance_landfill_display').val('');
        $('#attendance_waste_types_display').val('');
    });

    $('#worker_id').on('select2:clear', function () {
        $('#work_type_display').val('');
        $('#supervisor_display').val('');
        $('#department').val('');
    });

    @if(!$scopedOrganizationId)
    $('#organization_id').on('change', function () {
        $('#work_type_display').val('');
        $('#supervisor_display').val('');
        $('#department').val('');
        initWorkerSelect2();
        initAttendanceVehicleSelect2();
        $('#worker_id').val(null).trigger('change');
        $('#attendance_vehicle_id').val(null).trigger('change');
        $('#attendance_landfill_display').val('');
        $('#attendance_waste_types_display').val('');
    });
    @endif
});
</script>
@endpush
