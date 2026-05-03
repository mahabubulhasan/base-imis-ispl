@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.complaints.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    </div>
    <div class="form-horizontal">
        <div class="card-body">
            <div class="form-group row">
                {!! Form::label('complaint_id', __('Complaint ID'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $complaint->complaint_id, ['class' => 'form-control']) !!}</div>
                {!! Form::label('date_time', __('Date and Time'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, optional($complaint->date_time)->format('Y-m-d H:i'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                {!! Form::label('holding_number', __('Holding number'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $complaint->holding_number, ['class' => 'form-control']) !!}</div>
                {!! Form::label('customer_id', __('Household ID'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $complaint->customer_id, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                {!! Form::label('name', __('Name'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $complaint->name, ['class' => 'form-control']) !!}</div>
                {!! Form::label('contact_number', __('Contact number'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $complaint->contact_number, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                {!! Form::label('ward_no', __('Ward No.'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $complaint->ward_no, ['class' => 'form-control']) !!}</div>
                {!! Form::label('incident_date', __('Incident Date'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, optional($complaint->incident_date)->format('Y-m-d'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                {!! Form::label('complaint_type', __('Complaint Type'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, __(config('swm_complaints.complaint_types')[$complaint->complaint_type] ?? $complaint->complaint_type), ['class' => 'form-control']) !!}</div>
                {!! Form::label('submitted_through', __('Complaint Submitted through'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, __(config('swm_complaints.submitted_through')[$complaint->submitted_through] ?? $complaint->submitted_through), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                {!! Form::label('complaint_status', __('Complaint Status'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, __(config('swm_complaints.complaint_statuses')[$complaint->complaint_status] ?? $complaint->complaint_status), ['class' => 'form-control']) !!}</div>
                {!! Form::label('resolution_time_days', __('Resolution Time (days)'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $complaint->resolution_time_days, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                {!! Form::label('duplicate_complaint', __('Duplicate Complaint'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $complaint->duplicate_complaint ? __('Yes') : __('No'), ['class' => 'form-control']) !!}</div>
                {!! Form::label('duplicate_reference', __('Duplicate Reference'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $complaint->duplicate_reference, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                {!! Form::label('priority_level', __('Priority Level'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $complaint->priority_level, ['class' => 'form-control']) !!}</div>
                {!! Form::label('assigned_to', __('Assigned To (Worker/Driver)'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $complaint->assigned_to, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                {!! Form::label('complaint_details', __('Complaint Details'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-9">{!! Form::textarea(null, $complaint->complaint_details, ['class' => 'form-control', 'rows' => 4, 'readonly' => true]) !!}</div>
            </div>
            <div class="form-group row">
                {!! Form::label('photo_attachment', __('Photo Attachment'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-9 form-control">
                    @if(!empty($complaint->photo_attachment_path))
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($complaint->photo_attachment_path) }}" target="_blank">
                            {{ __('View attachment') }}
                        </a>
                    @else
                        {{ __('N/A') }}
                    @endif
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('notes', __('Notes'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-9">{!! Form::textarea(null, $complaint->notes, ['class' => 'form-control', 'rows' => 3, 'readonly' => true]) !!}</div>
            </div>
        </div>
    </div>
</div>
@stop
