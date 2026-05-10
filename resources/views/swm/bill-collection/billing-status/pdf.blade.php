<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Billing Status Report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
        }
        .subtitle {
            font-size: 10px;
            text-align: center;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 3px 4px;
            vertical-align: middle;
        }
        thead th {
            background: #f3f4f6;
            text-align: center;
            font-size: 9px;
        }
        thead tr.header-group th {
            background: #e5e7eb;
            font-size: 10px;
            font-weight: 700;
            padding-top: 5px;
            padding-bottom: 5px;
        }
        .text-right {
            text-align: right;
        }
        .amount {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }
        .text-center {
            text-align: center;
        }
        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    @php
        $formatAmount = static function ($value): string {
            if ($value === null || $value === '') {
                return '0.00';
            }

            $normalized = is_string($value) ? str_replace(',', '', $value) : $value;

            return number_format((float) $normalized, 2, '.', ',');
        };
    @endphp

    <div class="title">{{ __('SWM Billing Status Report') }}</div>
    <div class="subtitle">
        {{ __('Month Range') }}:
        {{ $monthFrom ? $monthFrom->format('M Y') : '-' }}
        -
        {{ $monthTo ? $monthTo->format('M Y') : '-' }}
    </div>
    <div class="subtitle">
        {{ __('Closing Due is the canonical outstanding balance through the selected end month.') }}
    </div>

    <table>
        <thead>
            <tr class="header-group">
                <th rowspan="2">{{ __('SL') }}</th>
                <th rowspan="2">{{ __('Holding Number') }}</th>
                <th rowspan="2">{{ __('Household ID') }}</th>
                <th rowspan="2">{{ __('Owner') }}</th>
                <th rowspan="2">{{ __("Father/Husband") }}</th>
                <th rowspan="2">{{ __('Sub Location') }}</th>
                <th rowspan="2">{{ __('Ward') }}</th>
                <th rowspan="2">{{ __('Contact') }}</th>
                <th colspan="9">{{ __('Amount Section (Taka)') }}</th>
            </tr>
            <tr>
                <th>{{ __('Fixed Fee') }}</th>
                <th>{{ __('Previous Due') }}</th>
                <th>{{ __('Current Due') }}</th>
                <th>{{ __('Payable') }}</th>
                <th>{{ __('Due Months') }}</th>
                <th>{{ __('Current Paid') }}</th>
                <th>{{ __('Prev Due Paid') }}</th>
                <th>{{ __('Collected') }}</th>
                <th>{{ __('Closing Due') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td class="text-center">{{ $row['sl'] }}</td>
                    <td class="nowrap">{{ $row['holding_number'] }}</td>
                    <td class="nowrap">{{ $row['household_id'] }}</td>
                    <td>{{ $row['household_owner_name'] }}</td>
                    <td>{{ $row['father_or_husband_name'] }}</td>
                    <td>{{ $row['sub_location'] }}</td>
                    <td>{{ $row['ward'] }}</td>
                    <td class="nowrap">{{ $row['contact_number'] }}</td>
                    <td class="amount">{{ $formatAmount($row['current_service_fee'] ?? null) }}</td>
                    <td class="amount">{{ $formatAmount($row['previous_due_amount'] ?? null) }}</td>
                    <td class="amount">{{ $formatAmount($row['due_current_month'] ?? null) }}</td>
                    <td class="amount">{{ $formatAmount($row['total_due_amount'] ?? null) }}</td>
                    <td>{{ $row['due_months_of'] }}</td>
                    <td class="amount">{{ $formatAmount($row['current_month_paid'] ?? null) }}</td>
                    <td class="amount">{{ $formatAmount($row['previous_due_paid'] ?? null) }}</td>
                    <td class="amount">{{ $formatAmount($row['revenue_collected'] ?? null) }}</td>
                    <td class="amount">{{ $formatAmount($row['remaining_due'] ?? null) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="18" class="text-center">{{ __('No data found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
