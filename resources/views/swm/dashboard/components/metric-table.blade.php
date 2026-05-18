<div class="swm-metric-table-wrap">
    @if(!empty($title))
        <h4 class="subsection-title">
            <i class="fas fa-table"></i> {{ $title }}
        </h4>
    @endif
    <div class="table-responsive">
        <table class="table table-sm table-striped table-bordered swm-metric-table mb-0">
            <thead>
                <tr>
                    @foreach($columns ?? [] as $column)
                        <th scope="col">{{ $column['label'] ?? '' }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows ?? [] as $row)
                    <tr>
                        @foreach($columns ?? [] as $column)
                            <td>{{ $row[$column['key'] ?? ''] ?? '' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns ?? []) }}" class="text-muted text-center">
                            {{ __('No records found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
