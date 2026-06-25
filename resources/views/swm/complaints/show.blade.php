@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.complaints.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
        @can('Edit SW Complaint')
        <a href="{{ route('swm.complaints.edit', $complaint->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
        @endcan
    </div>
    <div class="form-horizontal swm-complaint-form-mobile app-mobile-form">
        <div class="card-body">
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Complaint ID') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $complaint->complaint_id, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Date and Time') }}</label>
                <div class="col-sm-3">{!! Form::label(null, optional($complaint->date_time)->format('Y-m-d H:i'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Holding No.') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $complaint->holding_number, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Household ID') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $complaint->customer_id, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Name') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $complaint->name, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Contact No.') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $complaint->contact_number, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Ward No.') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $complaint->ward_no, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Incident Date') }}</label>
                <div class="col-sm-3">{!! Form::label(null, optional($complaint->incident_date)->format('Y-m-d'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Complaint Type') }}</label>
                <div class="col-sm-3">{!! Form::label(null, __(config('swm_complaints.complaint_types')[$complaint->complaint_type] ?? $complaint->complaint_type), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Complaint Submitted Through') }}</label>
                <div class="col-sm-3">{!! Form::label(null, __(config('swm_complaints.submitted_through')[$complaint->submitted_through] ?? $complaint->submitted_through), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Complaint Status') }}</label>
                <div class="col-sm-3">
                    @php
                        $statusLabel = __(config('swm_complaints.complaint_statuses')[$complaint->complaint_status] ?? $complaint->complaint_status);
                        if ($complaint->complaint_status === 'others' && ! empty($complaint->complaint_status_other)) {
                            $statusLabel = $statusLabel.': '.$complaint->complaint_status_other;
                        }
                    @endphp
                    {!! Form::label(null, $statusLabel, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Resolution Time (Days)') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $complaint->resolution_time_days, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Priority Level (1-5)') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $complaint->priority_level, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Assigned To') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $complaint->assigned_to, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Complaint Details') }}</label>
                <div class="col-sm-3">{!! Form::textarea(null, $complaint->complaint_details, ['class' => 'form-control', 'rows' => 2, 'readonly' => true]) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Photo Attachment') }}</label>
                <div class="col-sm-3">
                    @if(!empty($complaint->photo_attachment_path))
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($complaint->photo_attachment_path) }}" target="_blank" class="form-control d-block">
                            {{ __('View Attachment') }}
                        </a>
                    @else
                        {!! Form::label(null, __('N/A'), ['class' => 'form-control']) !!}
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Notes') }}</label>
                <div class="col-sm-3">{!! Form::textarea(null, $complaint->notes, ['class' => 'form-control', 'rows' => 2, 'readonly' => true]) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Duplicate Complaint') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $complaint->duplicate_complaint ? __('Yes') : __('No'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Duplicate Complaint ID') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $complaint->duplicate_reference, ['class' => 'form-control']) !!}</div>
            </div>
        </div>
    </div>
</div>
@stop
