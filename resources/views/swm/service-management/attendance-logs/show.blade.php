@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.attendance-logs.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
        @can('Edit SW Attendance Log')
        <a href="{{ route('swm.attendance-logs.edit', $attendanceLog->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
        @endcan
    </div>
    <div class="swm-attendance-form-mobile app-mobile-form">
        <div class="card-body">
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Attendance Log ID') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $attendanceLog->id }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Organization') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $attendanceLog->organization?->name }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Department') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $attendanceLog->department ?: '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Worker Name-ID') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">
                    @if($attendanceLog->worker)
                        {{ $attendanceLog->worker->name }}{{ $attendanceLog->worker->worker_id_no ? ' — '.$attendanceLog->worker->worker_id_no : '' }}
                    @endif
                </p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Worker Type') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $attendanceLog->work_type_name ?: '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __("Supervisor's Name") }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $attendanceLog->supervisor_name ?: '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Entry Date and Time') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $attendanceLog->entry_at?->format('Y-m-d H:i') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Attendance Status') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ \App\Models\Swm\AttendanceLog::statusOptions()[$attendanceLog->attendance_status] ?? $attendanceLog->attendance_status }}</p></div>
            </div>
            @if($attendanceLog->attendance_status === \App\Models\Swm\AttendanceLog::STATUS_PRESENT)
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Check-in Time') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $attendanceLog->check_in_at?->format('Y-m-d H:i') ?: '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Check-out Time') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $attendanceLog->check_out_at?->format('Y-m-d H:i') ?: '—' }}</p></div>
            </div>
            @endif
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Remarks') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $attendanceLog->remarks ?: '—' }}</p></div>
            </div>
        </div>
    </div>
</div>
@stop
