<!doctype html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>কঠিন বর্জ্য ব্যবস্থাপনা সংক্রান্ত বার্ষিক প্রতিবেদন</title>
    <style>
        @font-face {
            font-family: 'Noto Sans Bengali';
            src: url('file://{{ str_replace('\\', '/', public_path('fonts/NotoSansBengali-Regular.ttf')) }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @page { margin: 18mm 25.4mm; }
        body {
            font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Kalpurush', sans-serif;
            font-size: 12px;
            color: #111;
            line-height: 1.5;
            padding: 0;
        }
        body, table, th, td, p, h1, h2, span { font-size: 12px; }
        .report-header { text-align: center; line-height: 1.2; margin-bottom: 20px; }
        .report-header h1 { margin: 2px 0; font-weight: normal; line-height: 1.2; }
        .report-header .meta { margin: 0; }
        .report-header .meta-row { margin: 0; display: block; }
        h1 { text-align: center; margin: 0 0 6px; font-weight: normal; }
        h2 {
            margin: 10px 0 6px;
            padding-bottom: 0;
            text-decoration: none;
            font-weight: normal;
        }
        .meta { text-align: center; margin-bottom: 12px; }
        .meta-row { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin: 6px 0 10px; }
        th, td { border: 1px solid #444; padding: 4px 6px; vertical-align: top; }
        th { background: #f0f0f0; font-weight: normal; }
        th.th-stacked { text-align: center; line-height: 1.25; }
        th.th-stacked .th-marker { display: block; }
        th.th-stacked .th-label { display: block; }
        .field-grid {
            width: 100%;
            margin-bottom: 6px;
            table-layout: fixed;
            border-collapse: collapse;
        }
        .field-grid col.col-label { width: 49%; }
        .field-grid col.col-colon { width: 2%; }
        .field-grid col.col-value { width: 49%; }
        .field-grid .field-grid-width-ref td {
            height: 0;
            padding: 0;
            border: none;
            font-size: 0;
            line-height: 0;
            visibility: hidden;
            overflow: hidden;
        }
        .field-grid td { border: none; padding: 2px 0; vertical-align: top; line-height: 1.4; }
        .field-grid .field-label {
            width: 49%;
            text-align: left;
            padding: 2px 8px 2px 0;
            font-weight: normal;
            white-space: normal;
            vertical-align: top;
        }
        .field-grid .field-colon {
            width: 2%;
            text-align: center;
            padding: 2px;
            white-space: nowrap;
            vertical-align: top;
        }
        .field-grid .field-value {
            width: 49%;
            text-align: left;
            padding: 2px 0 2px 8px;
            white-space: normal;
            vertical-align: top;
        }
        .field-grid .field-value.text-block { white-space: pre-wrap; }
        .field-grid-auto {
            table-layout: auto;
            width: 100%;
        }
        .field-grid-auto .field-label {
            width: 1%;
            white-space: nowrap;
            padding-right: 4px;
        }
        .field-grid-auto .field-colon {
            width: 1%;
            white-space: nowrap;
            padding: 0 4px 0 0;
        }
        .field-grid-auto .field-value {
            width: auto;
            padding-left: 0;
        }
        .field-grid-auto .field-row-heading .field-label {
            padding-left: 0;
        }
        .field-row-sub .field-label { padding-left: 1.2em; }
        .meta-dual { border: none; margin-bottom: 10px; margin-top: 0; table-layout: fixed; }
        .meta-dual > tbody > tr > td { border: none; padding: 0 6px; vertical-align: middle; width: 50%; }
        .meta-dual .field-grid { margin-bottom: 0; }
        .section {
            page-break-inside: avoid;
            border-top: 1px solid #333;
            padding-top: 10px;
            margin-top: 12px;
        }
        .section:first-of-type { border-top: none; padding-top: 0; margin-top: 0; }
        .text-block { white-space: pre-wrap; min-height: 20px; }
        .subsection-note { color: #444; margin: 0 0 6px; padding-left: 0; }
        .field-row-heading .field-label { padding-bottom: 2px; }
        .na-text { margin: 4px 0 8px; padding-left: 0; }
        .field-grid td.subsection-note,
        .field-grid .na-inline {
            border: none;
            padding: 0 0 6px;
            text-align: left;
            vertical-align: top;
        }
        .field-grid .na-inline { padding-bottom: 8px; }
        .col-serial { width: 36px; }
        .col-org-name { width: 28%; }
        .col-desc { width: 62%; }
        .signature-block {
            margin-top: 72px;
            width: 100%;
            overflow: hidden;
        }
        .signature-block-inner {
            float: right;
            clear: both;
            text-align: center;
        }
        .signature-block-inner .signature-line {
            margin: 0 0 8px;
            padding: 0;
            line-height: 1.5;
        }
        .signature-block-inner .signature-line:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
@php
    $f = $form;
    $na = '';
    $yn = static function ($v) {
        if ($v === 'yes') return 'হ্যাঁ';
        if ($v === 'no') return 'না';
        if ($v === 'manual') return 'কায়িক';
        if ($v === 'mechanical') return 'যান্ত্রিক';
        if ($v === 'both') return 'উভয়';
        return $v ?? '';
    };
    $display = static function ($v) {
        if ($v === null || $v === '') {
            return '';
        }
        return $v;
    };
    $displayYn = static function ($v) use ($yn) {
        return $yn($v);
    };
    $bnDigits = static fn ($value) => strtr((string) $value, [
        '0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
        '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯',
    ]);
@endphp
<div class="report-header">
@if(!empty($f['org_name']))
    <p class="meta"><span class="meta-row">{{ $f['org_name'] }}</span></p>
@endif
    <h1>কঠিন বর্জ্য ব্যবস্থাপনা সংক্রান্ত বার্ষিক প্রতিবেদন</h1>
    <p class="meta"><span class="meta-row">পরিবেশ অধিদপ্তরের জন্য প্রস্তুতকৃত {{ $bnDigits($f['year'] ?? '') }} সালের প্রতিবেদন</span></p>
</div>
<table class="meta-dual">
    <tr>
        <td>
            <table class="field-grid field-grid-auto">
                <tr>
                    <td class="field-label">প্রতিবেদন নম্বর</td>
                    <td class="field-colon">:</td>
                    <td class="field-value">{{ $display($f['report_no'] ?? null) }}</td>
                </tr>
            </table>
        </td>
        <td>
            <table class="field-grid field-grid-auto">
                <tr>
                    <td class="field-label">তারিখ</td>
                    <td class="field-colon">:</td>
                    <td class="field-value">{{ $display($f['report_date'] ?? null) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="section">
    <h2>(১) প্রতিষ্ঠানের বিবরণ</h2>
    <table class="field-grid">
        <tr class="field-row">
            <td class="field-label">১.১ স্থানীয় সরকার প্রতিষ্ঠানের নাম</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['org_name'] ?? null) }}</td>
        </tr>
        <tr class="field-row">
            <td class="field-label">১.২ মোট জনসংখ্যা</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['population'] ?? null) }}</td>
        </tr>
        <tr class="field-row">
            <td class="field-label wrap-label">১.৩ ডাক যোগাযোগের ঠিকানা</td>
            <td class="field-colon">:</td>
            <td class="field-value text-block">{{ $display($f['address'] ?? null) }}</td>
        </tr>
        <tr class="field-row">
            <td class="field-label wrap-label">১.৪ কনজার্ভেন্সির দায়িত্বে নিয়োজিত প্রধান কর্মকর্তার নাম</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['chief_officer'] ?? null) }}</td>
        </tr>
        <tr class="field-row">
            <td class="field-label">১.৫ ফোন নম্বর</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['phone'] ?? null) }}</td>
        </tr>
        <tr class="field-row">
            <td class="field-label">১.৬ ইমেইল</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['email'] ?? null) }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>(২) কঠিন বর্জ্যের পরিমাণ ও সংশ্লিষ্ট তথ্যাদি</h2>
    <table class="field-grid">
        <colgroup>
            <col class="col-label">
            <col class="col-colon">
            <col class="col-value">
        </colgroup>
        <tr class="field-row">
            <td class="field-label">২.১ দৈনিক সৃজিত গড় কঠিন বর্জ্যের পরিমাণ (টন/দিন)</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['daily_avg'] ?? null) }}</td>
        </tr>
        <tr class="field-row">
            <td class="field-label">২.২ বাৎসরিক কঠিন বর্জ্যের পরিমাণ (টন)</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['annual_total'] ?? null) }}</td>
        </tr>
        <tr class="field-row">
            <td class="field-label">২.৩ বিধিবদ্ধ পদ্ধতিতে সংগৃহীত বাৎসরিক কঠিন বর্জ্যের পরিমাণ (টন)</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['collected_formal'] ?? null) }}</td>
        </tr>
        <tr class="field-row">
            <td class="field-label">২.৪ অনুমোদিত স্থানে স্তুপীকৃত কঠিন বর্জ্যের পরিমাণ (বাৎসরিক) (টন)</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['stockpiled'] ?? null) }}</td>
        </tr>
        <tr class="field-row">
            <td class="field-label">২.৫ অসংগৃহীত কঠিন বর্জ্যের পরিমাণ (বাৎসরিক) (টন)</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['uncollected'] ?? null) }}</td>
        </tr>
        <tr class="field-row">
            <td class="field-label">২.৬ ঝুঁকিপূর্ণ শিল্প বর্জ্য সংগ্রহের পরিমাণ (বাৎসরিক) (টন)</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['hazardous'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-heading">
            <td class="field-label" colspan="3">২.৭ বাৎসরিক বর্জ্য প্রক্রিয়াকরণের পরিমাণ (সরকারি/বেসরকারি উদ্যোগে)</td>
        </tr>
        <tr class="field-row field-row-sub">
            <td class="field-label">(অ) জৈবিক বর্জ্য প্রক্রিয়াকরণ/কম্পোস্টিং (টন)</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['proc_organic'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-sub">
            <td class="field-label">(আ) অজৈবিক অপচনযোগ্য বর্জ্য পুনর্ব্যবহারোপযোগীকরণ (টন)</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['proc_recycle'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-sub">
            <td class="field-label">(ই) বর্জ্য ইনসিনারেশন (টন)</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['proc_incineration'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-sub">
            <td class="field-label">(ঈ) উন্মুক্ত অবস্থায় বর্জ্য পোড়ানো (টন)</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['proc_openburn'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-sub">
            <td class="field-label">(উ) বর্জ্য ল্যান্ডফিলের পরিমাণ (টন)</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['proc_landfill'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-heading">
            <td class="field-label" colspan="3">২.৮ ল্যান্ডফিলের প্রকার</td>
        </tr>
    </table>
    @if(!empty($f['landfill_types']))
        <table>
            <thead><tr><th>ক্রম</th><th>ল্যান্ডফিল সাইটের নাম</th><th>প্রকার</th><th>ধারণ ক্ষমতা</th><th>ধারণ ক্ষমতার একক</th></tr></thead>
            <tbody>
            @foreach($f['landfill_types'] as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $display($row['name'] ?? null) }}</td>
                    <td>{{ $display($row['type'] ?? null) }}</td>
                    <td>{{ $display($row['capacity'] ?? null) }}</td>
                    <td>{{ $display($row['unit'] ?? null) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="na-text">{{ $na }}</p>
    @endif
    <table class="field-grid">
        <colgroup>
            <col class="col-label">
            <col class="col-colon">
            <col class="col-value">
        </colgroup>
        <tr class="field-grid-width-ref" aria-hidden="true">
            <td class="field-label">(অ) জৈবিক বর্জ্য প্রক্রিয়াকরণ/কম্পোস্টিং (টন)</td>
            <td class="field-colon">:</td>
            <td class="field-value">0</td>
        </tr>
        <tr class="field-row field-row-heading">
            <td class="field-label" colspan="3">২.৯ ল্যান্ডফিলের অন্যান্য তথ্যাদি</td>
        </tr>
        <tr class="field-row">
            <td class="field-label">(অ) ল্যান্ডফিল স্থানের সংখ্যা</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['num_landfill_sites'] ?? null) }}</td>
        </tr>
    </table>
    @if(!empty($f['landfill_sites']))
        <table>
            <thead>
                <tr>
                    <th class="th-stacked"><span class="th-label">ক্রম</span></th>
                    <th class="th-stacked"><span class="th-label">ল্যান্ডফিল সাইটের নাম</span></th>
                    <th class="th-stacked"><span class="th-marker">(আ)</span><span class="th-label">আয়তন</span></th>
                    <th class="th-stacked"><span class="th-marker">(আ)</span><span class="th-label">আয়তনের একক</span></th>
                    <th class="th-stacked"><span class="th-marker">(ই)</span><span class="th-label">Weighbridge</span></th>
                    <th class="th-stacked"><span class="th-marker">(ঈ)</span><span class="th-label">সীমানা প্রাচীর</span></th>
                    <th class="th-stacked"><span class="th-marker">(উ)</span><span class="th-label">আলোকিতকরণ</span></th>
                    <th class="th-stacked"><span class="th-marker">(ঊ)</span><span class="th-label">জনবল সংখ্যা</span></th>
                    <th class="th-stacked"><span class="th-marker">(ঋ)</span><span class="th-label">আচ্ছাদিতকরণ</span></th>
                    <th class="th-stacked"><span class="th-marker">(এ)</span><span class="th-label">গ্যাস নিয়ন্ত্রণ</span></th>
                    <th class="th-stacked"><span class="th-marker">(ঐ)</span><span class="th-label">চোয়ানি সংগ্রহ</span></th>
                </tr>
            </thead>
            <tbody>
            @foreach($f['landfill_sites'] as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $display($row['name'] ?? null) }}</td>
                    <td>{{ $display($row['area'] ?? null) }}</td>
                    <td>{{ $display($row['unit'] ?? null) }}</td>
                    <td>{{ $displayYn($row['wb'] ?? null) }}</td>
                    <td>{{ $displayYn($row['bw'] ?? null) }}</td>
                    <td>{{ $displayYn($row['lt'] ?? null) }}</td>
                    <td>{{ $display($row['pax'] ?? null) }}</td>
                    <td>{{ $displayYn($row['cv'] ?? null) }}</td>
                    <td>{{ $displayYn($row['gas'] ?? null) }}</td>
                    <td>{{ $displayYn($row['lc'] ?? null) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <table class="field-grid">
            <tr>
                <td colspan="3" class="na-inline">{{ $na }}</td>
            </tr>
        </table>
    @endif
    <table class="field-grid">
        <colgroup>
            <col class="col-label">
            <col class="col-colon">
            <col class="col-value">
        </colgroup>
        <tr class="field-row field-row-heading">
            <td class="field-label" colspan="3">(ও) ল্যান্ডফিলের যন্ত্রপাতির তথ্য</td>
        </tr>
        <tr>
            <td colspan="3" class="subsection-note">বুলডোজার, কম্প্যাক্টর এবং অনুরূপ অন্যান্য ধরনের যন্ত্রপাতি থাকিলে উহার নাম।</td>
        </tr>
    </table>
    @if(!empty($f['equipment']))
        <table>
            <thead><tr><th>ক্রম</th><th>ল্যান্ডফিল সাইটের নাম</th><th>যন্ত্রপাতির ধরন</th><th>সংখ্যা</th></tr></thead>
            <tbody>
            @foreach($f['equipment'] as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $display($row['name'] ?? null) }}</td>
                    <td>{{ $display($row['eq'] ?? null) }}</td>
                    <td>{{ $display($row['count'] ?? null) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <table class="field-grid">
            <tr>
                <td colspan="3" class="na-inline">{{ $na }}</td>
            </tr>
        </table>
    @endif
</div>

<div class="section">
    <h2>(৩) মজুদকরণ সুবিধা</h2>
    <table class="field-grid">
        <tr class="field-row">
            <td class="field-label">৩.১ বর্জ্য সংগ্রহকরণের আয়তন (টন)</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['collection_volume'] ?? null) }}</td>
        </tr>
        <tr class="field-row">
            <td class="field-label">৩.২ বাড়ির সংখ্যা</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['num_houses'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-heading">
            <td class="field-label" colspan="3">৩.৩ বাড়ি বাড়ি বর্জ্য সংগ্রহের জন্য কোনো বেসরকারি সংগঠন বা অন্য কাউকে নিয়োগ করা হয়ে থাকলে তার বিবরণ</td>
        </tr>
    </table>
    @if(!empty($f['door_orgs']))
        <table>
            <thead><tr><th class="col-serial">ক্রম</th><th class="col-org-name">সংগঠনের নাম</th><th class="col-desc">বিবরণ</th></tr></thead>
            <tbody>
            @foreach($f['door_orgs'] as $i => $row)
                <tr>
                    <td class="col-serial">{{ $i + 1 }}</td>
                    <td class="col-org-name">{{ $display($row['name'] ?? null) }}</td>
                    <td class="col-desc">{{ $display($row['desc'] ?? null) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="na-text">{{ $na }}</p>
    @endif
    <table class="field-grid">
        <tr class="field-row field-row-heading">
            <td class="field-label" colspan="3">৩.৪ বর্জ্যাধার (Bins)</td>
        </tr>
    </table>
    @if(!empty($f['bins']))
        <table>
            <thead><tr><th>ক্রম</th><th>প্রকার</th><th>ধারণ ক্ষমতা</th><th>ধারণ ক্ষমতার একক</th><th>সংখ্যা</th></tr></thead>
            <tbody>
            @foreach($f['bins'] as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $display($row['type'] ?? null) }}</td>
                    <td>{{ $display($row['size'] ?? null) }}</td>
                    <td>{{ $display($row['unit'] ?? null) }}</td>
                    <td>{{ $display($row['count'] ?? null) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="na-text">{{ $na }}</p>
    @endif
    <table class="field-grid">
        <tr class="field-row">
            <td class="field-label">৩.৫ সকল আধার ও সংগ্রহস্থল হতে প্রতিদিন বর্জ্য নিয়ে যাওয়া হয় কিনা</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $displayYn($f['daily_pickup'] ?? null) }}</td>
        </tr>
        <tr class="field-row">
            <td class="field-label">৩.৬ আধার হতে বর্জ্য কায়িক নাকি যান্ত্রিক উপায়ে উত্তোলন করা হয়</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $displayYn($f['lifting_method'] ?? null) }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>(৪) বর্জ্য পরিবহন</h2>
    @if(!empty($f['transport']))
    <table>
        <thead><tr><th>ক্রম</th><th>ধরন</th><th>বিদ্যমান সংখ্যা</th><th>প্রকৃত প্রয়োজন</th></tr></thead>
        <tbody>
        @foreach($f['transport'] as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $display($row['type'] ?? null) }}</td>
                <td>{{ $display($row['existing'] ?? null) }}</td>
                <td>{{ $display($row['required'] ?? null) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <p class="na-text">{{ $na }}</p>
    @endif
</div>

<div class="section">
    <h2>(৫) কঠিন বর্জ্য ব্যবস্থাপনার উন্নতি সাধনে কোনো প্রস্তাব থাকলে তা বিবৃত করুন</h2>
    <table class="field-grid">
        <tr>
            <td class="field-value text-block">{{ $display($f['proposals'] ?? null) }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>(৬) কোনো বেসরকারি ব্যক্তি বা প্রতিষ্ঠানকে কঠিন বর্জ্য প্রক্রিয়াকরণের দায়িত্ব প্রদান করা হয়ে থাকলে তার বিবরণ</h2>
    @if(!empty($f['contracts']))
    <table>
        <thead><tr><th>ক্রম</th><th>ব্যক্তি বা প্রতিষ্ঠানের নাম</th><th>প্রযুক্তির নাম</th><th>প্রক্রিয়াকরণের সময় ও পরিমাণ</th><th>নাম ও ঠিকানা</th></tr></thead>
        <tbody>
        @foreach($f['contracts'] as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $display($row['person'] ?? null) }}</td>
                <td>{{ $display($row['tech'] ?? null) }}</td>
                <td>{{ $display($row['dur'] ?? null) }}</td>
                <td>{{ $display($row['addr'] ?? null) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <table class="field-grid">
        <tr>
            <td class="field-value text-block">{{ $na }}</td>
        </tr>
    </table>
    @endif
</div>

<div class="section">
    <h2>(৭) নিম্নোক্ত কার্যাদি স্বাস্থ্যসম্মত উপায়ে সম্পাদন নিশ্চিতকরণের লক্ষ্যে গৃহীত পদক্ষেপ</h2>
    <table class="field-grid">
        <tr class="field-row field-row-heading field-row-sub">
            <td class="field-label" colspan="3">(অ) দুগ্ধ খামার</td>
        </tr>
        <tr class="field-row">
            <td class="field-value text-block" colspan="3">{{ $display($f['step_dairy'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-heading field-row-sub">
            <td class="field-label" colspan="3">(আ) পশু জবেহ</td>
        </tr>
        <tr class="field-row">
            <td class="field-value text-block" colspan="3">{{ $display($f['step_slaughter'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-heading field-row-sub">
            <td class="field-label" colspan="3">(ই) নির্মাণ-ভাঙন বর্জ্য</td>
        </tr>
        <tr class="field-row">
            <td class="field-value text-block" colspan="3">{{ $display($f['step_construction'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-heading field-row-sub">
            <td class="field-label" colspan="3">(ঈ) পার্ক, হাঁটার পথ, ইত্যাদি জবর দখল</td>
        </tr>
        <tr class="field-row">
            <td class="field-value text-block" colspan="3">{{ $display($f['step_encroachment'] ?? null) }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>(৮) বস্তি</h2>
    <table class="field-grid">
        <tr class="field-row">
            <td class="field-label">৮.১ মোট বস্তির সংখ্যা</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['total_slums'] ?? null) }}</td>
        </tr>
        <tr class="field-row">
            <td class="field-label">৮.২ স্যানিটারী ব্যবস্থা (Sanitation Facility) সম্পন্ন বস্তির সংখ্যা</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['slums_sanitation'] ?? null) }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>(৯) মোবাইল কোর্ট পরিচালনা করা হয়ে থাকলে তার বিবরণ</h2>
    @php
        $hasMobileCourt = false;
        foreach (['mc_cases', 'mc_convicted', 'mc_fines', 'mc_imprisoned'] as $mcKey) {
            $mcVal = $f[$mcKey] ?? null;
            if ($mcVal !== null && $mcVal !== '') {
                $hasMobileCourt = true;
                break;
            }
        }
    @endphp
    @if($hasMobileCourt)
    <table>
        <thead><tr><th>মোট মামলার সংখ্যা</th><th>দোষী সাব্যস্ত আসামীর সংখ্যা</th><th>আদায়কৃত জরিমানার পরিমাণ (টাকা)</th><th>কারাদণ্ডে দণ্ডিত আসামীর সংখ্যা</th></tr></thead>
        <tbody>
            <tr>
                <td>{{ $display($f['mc_cases'] ?? null) }}</td>
                <td>{{ $display($f['mc_convicted'] ?? null) }}</td>
                <td>{{ $display($f['mc_fines'] ?? null) }}</td>
                <td>{{ $display($f['mc_imprisoned'] ?? null) }}</td>
            </tr>
        </tbody>
    </table>
    @else
    <table class="field-grid">
        <tr>
            <td class="field-value text-block">{{ $na }}</td>
        </tr>
    </table>
    @endif
</div>

<div class="section">
    <h2>(১০) চিকিৎসা বর্জ্য ব্যবস্থাপনা</h2>
    <table class="field-grid">
        <tr class="field-row field-row-sub">
            <td class="field-label">(অ) সরকারি হাসপাতাল বা ক্লিনিক বা স্বাস্থ্য কেন্দ্র সংখ্যা</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['gov_hospitals'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-sub">
            <td class="field-label">(আ) সিটি কর্পোরেশন বা পৌরসভার হাসপাতাল বা ক্লিনিক সংখ্যা</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['city_hospitals'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-sub">
            <td class="field-label">(ই) বেসরকারি হাসপাতাল বা ক্লিনিক সংখ্যা</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $display($f['private_hospitals'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-sub">
            <td class="field-label">(ঈ) সকল হাসপাতাল বা ক্লিনিক ও স্বাস্থ্য কেন্দ্রের ক্ষেত্রে চিকিৎসা বর্জ্য (ব্যবস্থাপনা ও প্রক্রিয়াজাতকরণ) বিধিমালা, ২০০৮ এর বিধানাবলি যথাযথভাবে অনুসরণ করা হয় কিনা</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $displayYn($f['medical_compliance'] ?? null) }}</td>
        </tr>
        <tr class="field-row field-row-heading field-row-sub">
            <td class="field-label" colspan="3">(উ) চিকিৎসা বর্জ্য (ব্যবস্থাপনা ও প্রক্রিয়াজাতকরণ) বিধিমালা, ২০০৮ এর বিধানাবলি অনুসরণ করতে কোনো অসুবিধা হয়ে থাকলে তার বিবরণ</td>
        </tr>
        <tr class="field-row">
            <td class="field-value text-block" colspan="3">{{ $display($f['medical_issues'] ?? null) }}</td>
        </tr>
    </table>
</div>

<div class="signature-block">
    <div class="signature-block-inner">
        <p class="signature-line">স্বাক্ষর ও তারিখ</p>
        <p class="signature-line">নাম ও পদবি সম্বলিত সীল মোহর।</p>
    </div>
</div>
</body>
</html>
