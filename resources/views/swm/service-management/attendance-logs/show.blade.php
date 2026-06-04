@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
@php
    $workerLabel = '';
    if ($attendanceLog->worker) {
        $workerLabel = $attendanceLog->worker->name;
        if ($attendanceLog->worker->worker_id_no) {
            $workerLabel .= ' — ' . $attendanceLog->worker->worker_id_no;
        }
    }
    $statusLabel = \App\Models\Swm\AttendanceLog::statusOptions()[$attendanceLog->attendance_status] ?? $attendanceLog->attendance_status;
@endphp
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.attendance-logs.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
        @can('Edit SW Attendance Log')
        <a href="{{ route('swm.attendance-logs.edit', $attendanceLog->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
        @endcan
    </div>
    <div class="form-horizontal swm-attendance-form-mobile app-mobile-form">
        <div class="card-body">
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Attendance Log ID') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $attendanceLog->id, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Entry Date and Time') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $attendanceLog->entry_at?->format('Y-m-d H:i'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Organization') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $attendanceLog->organization?->name, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Department') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $attendanceLog->department ?: '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Worker Name-ID') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $workerLabel ?: '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Worker Type') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $attendanceLog->work_type_name ?: '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __("Supervisor's Name") }}</label>
                <div class="col-sm-3">{!! Form::label(null, $attendanceLog->supervisor_name ?: '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Attendance Status') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $statusLabel, ['class' => 'form-control']) !!}</div>
            </div>
            @if($attendanceLog->attendance_status === \App\Models\Swm\AttendanceLog::STATUS_PRESENT)
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Check-in Time') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $attendanceLog->check_in_at?->format('Y-m-d H:i') ?: '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Check-out Time') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $attendanceLog->check_out_at?->format('Y-m-d H:i') ?: '—', ['class' => 'form-control']) !!}</div>
            </div>
            @endif
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Remarks') }}</label>
                <div class="col-sm-3">{!! Form::textarea(null, $attendanceLog->remarks ?: '—', ['class' => 'form-control', 'rows' => 2, 'readonly' => true]) !!}</div>
            </div>
        </div>
    </div>
</div>
@stop
