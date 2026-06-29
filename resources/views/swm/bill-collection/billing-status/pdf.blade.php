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
            size: A4 landscape;
            margin: 15mm;
        }
        body {
            font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Kalpurush', 'Arial Unicode MS', sans-serif;
            font-size: 10px;
            color: #111827;
        }
        .pdf-header {
            width: 100%;
            text-align: center;
            margin-bottom: 8px;
        }
        .pdf-header-logo-wrap {
            margin-bottom: 4px;
        }
        .pdf-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            display: inline-block;
        }
        .pdf-header-title {
            text-align: center;
            line-height: 1.35;
        }
        .pdf-header-title .org {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .pdf-header-title .report {
            font-size: 12px;
            font-weight: bold;
        }
        .pdf-month {
            font-size: 11px;
            text-align: center;
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
            font-size: 10px;
        }
        table.data-table thead th {
            background: #f3f4f6;
            text-align: center;
            font-weight: bold;
        }

        table.data-table.page-break-before {
            page-break-before: always;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
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
        .sub-location-col {
            width: 70mm;
        }
        .due-months-col {
            width: 38mm;
            white-space: nowrap;
        }
        .pdf-footer {
            position: fixed;
            left: 0;
            bottom: -14mm;
            font-size: 8px;
            color: #6b7280;
            text-align: left;
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

    <div class="pdf-header">
        @if($logoDataUri)
            <div class="pdf-header-logo-wrap">
                <img src="{{ $logoDataUri }}" alt="" class="pdf-logo">
            </div>
        @endif
        <div class="pdf-header-title">
            <div class="org">চাঁপাইনবাবগঞ্জ পৌরসভা</div>
            <div class="report">বাসাবাড়ীর বর্জ্য ব্যবস্থাপনা সেবামূল্য আদায় সীট</div>
        </div>
    </div>

    <div class="pdf-month">মাসঃ {{ $monthLabel }}</div>

    @php
        $estimateRowLines = function ($row) {
            $dueParts = array_values(array_filter(array_map('trim', explode(',', (string) ($row['due_months_of'] ?? '')))));
            $dueLines = (int) ceil(count($dueParts) / 2);
            $wrapLines = function ($text, $charsPerLine) {
                $len = mb_strlen(trim((string) $text));
                return $len > 0 ? (int) ceil($len / $charsPerLine) : 1;
            };

            return max(
                1,
                $dueLines,
                $wrapLines($row['household_owner_name'] ?? '', 16),
                $wrapLines($row['father_or_husband_name'] ?? '', 16),
                $wrapLines($row['sub_location'] ?? '', 38)
            );
        };

        $rowOverheadLines = 0.6; // cell padding + border, in line units
        $firstPageBudget = 54;   // body lines available on page 1 (after title block)
        $otherPageBudget = 60;   // body lines available on later pages

        $pageChunks = [];
        $currentChunk = [];
        $usedLines = 0.0;
        $budget = $firstPageBudget;
        foreach ($rows as $row) {
            $need = $estimateRowLines($row) + $rowOverheadLines;
            if (! empty($currentChunk) && ($usedLines + $need) > $budget) {
                $pageChunks[] = $currentChunk;
                $currentChunk = [];
                $usedLines = 0.0;
                $budget = $otherPageBudget;
            }
            $currentChunk[] = $row;
            $usedLines += $need;
        }
        if (! empty($currentChunk)) {
            $pageChunks[] = $currentChunk;
        }
        if (empty($pageChunks)) {
            $pageChunks[] = [];
        }
    @endphp

    @foreach($pageChunks as $chunkIndex => $chunkRows)
    <table class="data-table{{ $chunkIndex > 0 ? ' page-break-before' : '' }}">
        <thead>
            <tr>
                <th rowspan="2">ক্রমিক নং</th>
                <th rowspan="2">হোল্ডিং নং</th>
                <th rowspan="2">খানার আইডি</th>
                <th rowspan="2">খানা প্রধানের নাম</th>
                <th rowspan="2">পিতা/স্বামীর নাম</th>
                <th rowspan="2" class="sub-location-col">পাড়া/মহল্লা</th>
                <th rowspan="2">ওয়ার্ড নং</th>
                <th rowspan="2">মোবাইল নং</th>
                <th colspan="9">বিলিং সারসংক্ষেপ (টাকায়)</th>
            </tr>
            <tr>
                <th>নির্ধারিত সেবামূল্য</th>
                <th>বিগত মাসসমূহ বকেয়া</th>
                <th class="due-months-col">বকেয়া মাসসমূহ</th>
                <th>চলতি</th>
                <th>আদায়যোগ্য মোট সেবামূল্য</th>
                <th>আদায়কৃত চলতি</th>
                <th>আদায়কৃত বকেয়া</th>
                <th>মোট বিল আদায়</th>
                <th>আদায় শেষে বকেয়া</th>
            </tr>
        </thead>
        <tbody>
            @forelse($chunkRows as $row)
                <tr>
                    <td class="text-left">{{ $row['sl'] }}</td>
                    <td class="text-left nowrap">{{ $row['holding_number'] }}</td>
                    <td class="text-left nowrap">{{ $row['household_id'] }}</td>
                    <td class="text-left">{{ $row['household_owner_name'] }}</td>
                    <td class="text-left">{{ $row['father_or_husband_name'] }}</td>
                    <td class="text-left sub-location-col">{{ $row['sub_location'] }}</td>
                    <td class="text-left">{{ $row['ward'] }}</td>
                    <td class="text-left nowrap">{{ $row['contact_number'] }}</td>
                    <td class="amount">{{ $row['current_service_fee'] ?? currency(0) }}</td>
                    <td class="amount">{{ $row['previous_due_amount'] ?? currency(0) }}</td>
                    <td class="text-left due-months-col">
                        @php
                            $dueMonthsParts = array_values(array_filter(array_map('trim', explode(',', (string) ($row['due_months_of'] ?? '')))));
                            $dueMonthsLines = [];
                            for ($i = 0; $i < count($dueMonthsParts); $i += 2) {
                                $dueMonthsLines[] = implode(', ', array_slice($dueMonthsParts, $i, 2));
                            }
                        @endphp
                        {!! implode('<br>', $dueMonthsLines) !!}
                    </td>
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
    @endforeach

    <div class="pdf-footer">{{__('This billing report is generated using IMIS application.')}}</div>
</body>
</html>
