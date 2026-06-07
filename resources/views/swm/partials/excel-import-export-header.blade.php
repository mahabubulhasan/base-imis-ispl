@can($addPermission ?? 'Add')
<a href="{{ $addRoute }}" class="btn btn-info">{{ $addLabel }}</a>
@endcan
@isset($importRoute)
@can($importPermission)
<a href="{{ $importRoute }}" class="btn btn-info">{{ __('Import from Excel') }}</a>
@endcan
@endisset
@isset($templateRoute)
@can($exportPermission)
<a href="{{ $templateRoute }}" class="btn btn-info">{{ __('Download Excel Template') }}</a>
@endcan
@endisset
@can($exportPermission)
<a href="#" id="{{ $exportId ?? 'export' }}" class="btn btn-info">{{ __('Export to Excel') }}</a>
@endcan
