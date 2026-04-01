<!-- // Last Modified: 12-03-2026
// Developed By: Streams Tech Ltd.
// Description: Displays details for a single pending application record. -->
@extends('layouts.dashboard')
@section('title', $pageTitle)

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ $indexAction }}" class="btn btn-info">{{ __('Back') }}</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <tbody>
                    @foreach ($detailRows as $label => $value)
                        <tr>
                            <th style="width: 25%;">{{ $label }}</th>
                            <td>{{ $value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
