@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.complaints.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    </div>
    <div class="card-body">
        <ul>
            @foreach($complaint->revisionHistory as $history)
                @if($history->key == 'created_at' && !$history->old_value)
                    @if($history->userResponsible())
                    <li>{{ $history->userResponsible()->name }} {{ __('Created This Resource At') }} {{ $history->newValue() }}</li>
                    @endif
                @else
                    @if($history->userResponsible())
                    <li>{{ $history->userResponsible()->name }} {{ __('Changed') }} {{ $history->fieldName() }} {{ __('From') }} {{ $history->oldValue() }} {{ __('To') }} {{ $history->newValue() }} {{ __('On') }} {{ $history->created_at }}</li>
                    @endif
                @endif
            @endforeach
        </ul>
    </div>
</div>
@stop
