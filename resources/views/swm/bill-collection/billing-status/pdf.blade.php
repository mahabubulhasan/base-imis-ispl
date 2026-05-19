<!doctype html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>বাসাবাড়ীর বর্জ্য ব্যবস্থাপনা সেবামূল্য আদায় সীট</title>
    <style>
        @font-face {
            font-family: 'Noto Sans Bengali';
            src: url('file://{{ str_replace('\\', '/', public_path('fonts/NotoSansBengali-Regular.ttf')) }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @page {
            margin: 12mm 8mm;
        }
        body {
            font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Kalpurush', 'Arial Unicode MS', sans-serif;
            font-size: 10px;
            color: #111827;
        }
        .pdf-header {
            width: 100%;
            margin-bottom: 10px;
        }
        .pdf-header td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .pdf-logo {
            width: 90px;
            max-height: 80px;
        }
        .pdf-header-title {
            text-align: center;
            line-height: 1.45;
        }
        .pdf-header-title .org {
            font-size: 14px;
            font-weight: bold;
        }
        .pdf-header-title .report {
            font-size: 12px;
            font-weight: bold;
            margin-top: 4px;
        }
        .pdf-month {
            font-size: 11px;
            margin-bottom: 10px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.data-table th,
        table.data-table td {
            border: 1px solid #d1d5db;
            padding: 3px 4px;
            vertical-align: middle;
        }
        table.data-table thead th {
            background: #f3f4f6;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
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
        $logoPath = public_path(config('constants.LOGO_URL', 'img/stl/logo-chapainawabganj.png'));
        $logoDataUri = is_file($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;

        $monthFromYm = $monthFrom ? $monthFrom->format('Y-m') : null;
        $monthToYm = $monthTo ? $monthTo->format('Y-m') : null;
        $monthLabelSame = $monthFrom && $monthTo && $monthFromYm === $monthToYm;
        if ($monthFrom && $monthTo) {
            $monthLabel = $monthLabelSame
                ? $monthFrom->format('F Y')
                : $monthFrom->format('F Y').' - '.$monthTo->format('F Y');
        } elseif ($monthFrom) {
            $monthLabel = $monthFrom->format('F Y');
        } elseif ($monthTo) {
            $monthLabel = $monthTo->format('F Y');
        } else {
            $monthLabel = '-';
        }
    @endphp

    <table class="pdf-header" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 100px;">
                @if($logoDataUri)
                    <img src="{{ $logoDataUri }}" alt="" class="pdf-logo">
                @endif
            </td>
            <td class="pdf-header-title">
                <div class="org">চাঁপাইনবাবগঞ্জ পৌরসভা</div>
                <div class="report">বাসাবাড়ীর বর্জ্য ব্যবস্থাপনা সেবামূল্য আদায় সীট</div>
            </td>
            <td style="width: 100px;"></td>
        </tr>
    </table>

    <div class="pdf-month">মাসঃ {{ $monthLabel }}</div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ক্রমিক নং</th>
                <th>হোল্ডিং নং</th>
                <th>বাসার আইডি</th>
                <th>বাসার মালিকের নাম</th>
                <th>পিতা/স্বামীর নাম</th>
                <th>পাড়া/মহল্লা</th>
                <th>ওয়ার্ড নং</th>
                <th>মোবাইল নং</th>
                <th>নির্ধারিত সেবামূল্য</th>
                <th>বিগত মাসসমূহ বকেয়া</th>
                <th>বকেয়া মাসসমূহ</th>
                <th>চলতি</th>
                <th>আদায়যোগ্য মোট সেবামূল্য</th>
                <th>আদায়কৃত চলতি</th>
                <th>আদায়কৃত বকেয়া</th>
                <th>মোট আদায়</th>
                <th>আদায় শেষে বকেয়া</th>
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
                    <td class="amount">{{ $row['current_service_fee'] ?? currency(0) }}</td>
                    <td class="amount">{{ $row['previous_due_amount'] ?? currency(0) }}</td>
                    <td>{{ $row['due_months_of'] }}</td>
                    <td class="amount">{{ $row['due_current_month'] ?? currency(0) }}</td>
                    <td class="amount">{{ $row['total_due_amount'] ?? currency(0) }}</td>
                    <td class="amount">{{ $row['current_month_paid'] ?? currency(0) }}</td>
                    <td class="amount">{{ $row['previous_due_paid'] ?? currency(0) }}</td>
                    <td class="amount">{{ $row['revenue_collected'] ?? currency(0) }}</td>
                    <td class="amount">{{ $row['remaining_due'] ?? currency(0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="17"></td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
