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
        @page { margin: 14mm 12mm; }
        body {
            font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Kalpurush', sans-serif;
            font-size: 11px;
            color: #111;
            line-height: 1.4;
        }
        h1 { font-size: 16px; text-align: center; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 14px 0 6px; border-bottom: 1px solid #333; padding-bottom: 2px; }
        h3 { font-size: 12px; margin: 10px 0 4px; }
        .meta { text-align: center; margin-bottom: 12px; font-size: 11px; }
        .meta-row { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin: 6px 0 10px; }
        th, td { border: 1px solid #444; padding: 4px 6px; vertical-align: top; }
        th { background: #f0f0f0; font-weight: bold; }
        .field-grid { width: 100%; margin-bottom: 8px; }
        .field-grid td { border: none; padding: 3px 6px; }
        .label { width: 55%; font-weight: 600; }
        .value { width: 45%; }
        .section { page-break-inside: avoid; }
        .text-block { white-space: pre-wrap; min-height: 24px; }
    </style>
</head>
<body>
@php
    $f = $form;
    $yn = static function ($v) {
        if ($v === 'yes') return 'হ্যাঁ';
        if ($v === 'no') return 'না';
        if ($v === 'manual') return 'কায়িক';
        if ($v === 'mechanical') return 'যান্ত্রিক';
        if ($v === 'both') return 'উভয়';
        return $v ?? '';
    };
@endphp

<h1>কঠিন বর্জ্য ব্যবস্থাপনা সংক্রান্ত বার্ষিক প্রতিবেদন</h1>
<p class="meta">ছক-২ [বিধি ১৩(১)] — বর্ষ: {{ $f['year'] ?? '' }}</p>
<table class="field-grid">
    <tr><td class="label">প্রতিবেদন নম্বর</td><td class="value">{{ $f['report_no'] ?? '' }}</td></tr>
    <tr><td class="label">তারিখ</td><td class="value">{{ $f['report_date'] ?? '' }}</td></tr>
</table>

<div class="section">
    <h2>(১) প্রতিষ্ঠানের বিবরণ</h2>
    <table class="field-grid">
        <tr><td class="label">১.১ স্থানীয় সরকার প্রতিষ্ঠানের নাম</td><td class="value">{{ $f['org_name'] ?? '' }}</td></tr>
        <tr><td class="label">১.২ মোট জনসংখ্যা</td><td class="value">{{ $f['population'] ?? '' }}</td></tr>
        <tr><td class="label">১.৩ ডাক যোগাযোগের ঠিকানা</td><td class="value">{{ $f['address'] ?? '' }}</td></tr>
        <tr><td class="label">১.৪ প্রধান কর্মকর্তার নাম</td><td class="value">{{ $f['chief_officer'] ?? '' }}</td></tr>
        <tr><td class="label">১.৫ ফোন নম্বর</td><td class="value">{{ $f['phone'] ?? '' }}</td></tr>
        <tr><td class="label">১.৬ ইমেইল ও ঠিকানা</td><td class="value">{{ $f['email'] ?? '' }}</td></tr>
    </table>
</div>

<div class="section">
    <h2>(২) দৈনিক সৃজিত গড় কঠিন বর্জ্যের পরিমাণ</h2>
    <table class="field-grid">
        <tr><td class="label">২.০ দৈনিক সৃজিত গড় (টন/দিন)</td><td class="value">{{ $f['daily_avg'] ?? '' }}</td></tr>
        <tr><td class="label">২.১ বাৎসরিক কঠিন বর্জ্য (টন)</td><td class="value">{{ $f['annual_total'] ?? '' }}</td></tr>
        <tr><td class="label">২.২ বিধিবদ্ধ পদ্ধতিতে সংগৃহীত বাৎসরিক (টন)</td><td class="value">{{ $f['collected_formal'] ?? '' }}</td></tr>
        <tr><td class="label">২.৩ অনুমোদিত স্থানে স্তূপীকৃত বাৎসরিক (টন)</td><td class="value">{{ $f['stockpiled'] ?? '' }}</td></tr>
        <tr><td class="label">২.৪ অসংগৃহীত বাৎসরিক (টন)</td><td class="value">{{ $f['uncollected'] ?? '' }}</td></tr>
        <tr><td class="label">২.৫ ঝুঁকিপূর্ণ শিল্প বর্জ্য (টন)</td><td class="value">{{ $f['hazardous'] ?? '' }}</td></tr>
    </table>
    <h3>২.৬ বাৎসরিক বর্জ্য প্রক্রিয়াকরণ (টন)</h3>
    <table class="field-grid">
        <tr><td class="label">(অ) জৈবিক/কম্পোস্টিং</td><td class="value">{{ $f['proc_organic'] ?? '' }}</td></tr>
        <tr><td class="label">(আ) পুনর্ব্যবহার</td><td class="value">{{ $f['proc_recycle'] ?? '' }}</td></tr>
        <tr><td class="label">(ই) ইনসিনারেশন</td><td class="value">{{ $f['proc_incineration'] ?? '' }}</td></tr>
        <tr><td class="label">(ঈ) উন্মুক্ত পোড়ানো</td><td class="value">{{ $f['proc_openburn'] ?? '' }}</td></tr>
        <tr><td class="label">(উ) ল্যান্ডফিল</td><td class="value">{{ $f['proc_landfill'] ?? '' }}</td></tr>
    </table>
    @if(!empty($f['landfill_types']))
    <h3>২.৭ ল্যান্ডফিলের প্রকার</h3>
    <table>
        <thead><tr><th>ক্রম</th><th>সাইটের নাম</th><th>প্রকার</th><th>ধারণ ক্ষমতা</th><th>একক</th></tr></thead>
        <tbody>
        @foreach($f['landfill_types'] as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['name'] ?? '' }}</td>
                <td>{{ $row['type'] ?? '' }}</td>
                <td>{{ $row['capacity'] ?? '' }}</td>
                <td>{{ $row['unit'] ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
    <h3>২.৮ ল্যান্ডফিলের অন্যান্য তথ্যাদি</h3>
    <p>ল্যান্ডফিল স্থানের সংখ্যা: {{ $f['num_landfill_sites'] ?? '' }}</p>
    @if(!empty($f['landfill_sites']))
    <table>
        <thead>
            <tr>
                <th>ক্রম</th><th>নাম</th><th>আয়তন</th><th>একক</th><th>ওজন সেতু</th><th>সীমানা প্রাচীর</th>
                <th>আলোকিতকরণ</th><th>জনবল</th><th>আচ্ছাদিতকরণ</th><th>গ্যাস নিয়ন্ত্রণ</th><th>চোয়ানি সংগ্রহ</th>
            </tr>
        </thead>
        <tbody>
        @foreach($f['landfill_sites'] as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['name'] ?? '' }}</td>
                <td>{{ $row['area'] ?? '' }}</td>
                <td>{{ $row['unit'] ?? 'একর' }}</td>
                <td>{{ $yn($row['wb'] ?? '') }}</td>
                <td>{{ $yn($row['bw'] ?? '') }}</td>
                <td>{{ $yn($row['lt'] ?? '') }}</td>
                <td>{{ $row['pax'] ?? '' }}</td>
                <td>{{ $yn($row['cv'] ?? '') }}</td>
                <td>{{ $yn($row['gas'] ?? '') }}</td>
                <td>{{ $yn($row['lc'] ?? '') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
    @if(!empty($f['equipment']))
    <h3>২.৮ (ঊ) যন্ত্রপাতির তথ্য</h3>
    <table>
        <thead><tr><th>ক্রম</th><th>সাইট</th><th>ধরন</th><th>সংখ্যা</th></tr></thead>
        <tbody>
        @foreach($f['equipment'] as $i => $row)
            <tr><td>{{ $i + 1 }}</td><td>{{ $row['name'] ?? '' }}</td><td>{{ $row['eq'] ?? '' }}</td><td>{{ $row['count'] ?? '' }}</td></tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>

<div class="section">
    <h2>(৩) মজুদকরণ সুবিধা</h2>
    <table class="field-grid">
        <tr><td class="label">৩.১ বর্জ্য সংগ্রহকরণের আয়তন (টন)</td><td class="value">{{ $f['collection_volume'] ?? '' }}</td></tr>
        <tr><td class="label">৩.২ বাড়ির সংখ্যা</td><td class="value">{{ $f['num_houses'] ?? '' }}</td></tr>
        <tr><td class="label">৩.৫ প্রতিদিন বর্জ্য লইয়া যায় কিনা</td><td class="value">{{ $yn($f['daily_pickup'] ?? '') }}</td></tr>
        <tr><td class="label">৩.৬ উত্তোলন পদ্ধতি</td><td class="value">{{ $yn($f['lifting_method'] ?? '') }}</td></tr>
    </table>
    @if(!empty($f['door_orgs']))
    <h3>৩.৩ বেসরকারি সংগঠন</h3>
    <table>
        <thead><tr><th>ক্রম</th><th>নাম</th><th>বিবরণ</th></tr></thead>
        <tbody>
        @foreach($f['door_orgs'] as $i => $row)
            <tr><td>{{ $i + 1 }}</td><td>{{ $row['name'] ?? '' }}</td><td>{{ $row['desc'] ?? '' }}</td></tr>
        @endforeach
        </tbody>
    </table>
    @endif
    @if(!empty($f['bins']))
    <h3>৩.৪ বর্জ্যাধার (Bins)</h3>
    <table>
        <thead><tr><th>ক্রম</th><th>প্রকার</th><th>ধারণ ক্ষমতা</th><th>একক</th><th>সংখ্যা</th></tr></thead>
        <tbody>
        @foreach($f['bins'] as $i => $row)
            <tr><td>{{ $i + 1 }}</td><td>{{ $row['type'] ?? '' }}</td><td>{{ $row['size'] ?? '' }}</td><td>{{ $row['unit'] ?? '' }}</td><td>{{ $row['count'] ?? '' }}</td></tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>

@if(!empty($f['transport']))
<div class="section">
    <h2>(৪) বর্জ্য পরিবহন</h2>
    <table>
        <thead><tr><th>ক্রম</th><th>ধরন</th><th>বিদ্যমান সংখ্যা</th><th>প্রকৃত প্রয়োজন</th></tr></thead>
        <tbody>
        @foreach($f['transport'] as $i => $row)
            <tr><td>{{ $i + 1 }}</td><td>{{ $row['type'] ?? '' }}</td><td>{{ $row['existing'] ?? '' }}</td><td>{{ $row['required'] ?? '' }}</td></tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="section">
    <h2>(৫) উন্নয়ন প্রস্তাবনা</h2>
    <div class="text-block">{{ $f['proposals'] ?? '' }}</div>
</div>

@if(!empty($f['contracts']))
<div class="section">
    <h2>(৬) বেসরকারি প্রক্রিয়াকরণ চুক্তি</h2>
    <table>
        <thead><tr><th>ক্রম</th><th>নাম</th><th>প্রযুক্তি</th><th>সময় ও পরিমাণ</th><th>ঠিকানা</th></tr></thead>
        <tbody>
        @foreach($f['contracts'] as $i => $row)
            <tr><td>{{ $i + 1 }}</td><td>{{ $row['person'] ?? '' }}</td><td>{{ $row['tech'] ?? '' }}</td><td>{{ $row['dur'] ?? '' }}</td><td>{{ $row['addr'] ?? '' }}</td></tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="section">
    <h2>(৭) স্বাস্থ্যসম্মত পদক্ষেপ</h2>
    <table class="field-grid">
        <tr><td class="label">দুগ্ধ খামার</td><td class="value text-block">{{ $f['step_dairy'] ?? '' }}</td></tr>
        <tr><td class="label">পশু জবেহ</td><td class="value text-block">{{ $f['step_slaughter'] ?? '' }}</td></tr>
        <tr><td class="label">নির্মাণ-ভাঙন বর্জ্য</td><td class="value text-block">{{ $f['step_construction'] ?? '' }}</td></tr>
        <tr><td class="label">জবর দখল</td><td class="value text-block">{{ $f['step_encroachment'] ?? '' }}</td></tr>
    </table>
</div>

<div class="section">
    <h2>(৮) বস্তি</h2>
    <table class="field-grid">
        <tr><td class="label">৮.১ মোট বস্তির সংখ্যা</td><td class="value">{{ $f['total_slums'] ?? '' }}</td></tr>
        <tr><td class="label">৮.২ স্যানিটারী ব্যবস্থা সম্পন্ন</td><td class="value">{{ $f['slums_sanitation'] ?? '' }}</td></tr>
    </table>
</div>

<div class="section">
    <h2>(৯) মোবাইল কোর্ট পরিচালনা</h2>
    <table>
        <thead><tr><th>মোট মামলা</th><th>দোষী সাব্যস্ত</th><th>জরিমানা (টাকা)</th><th>কারাদণ্ড</th></tr></thead>
        <tbody>
            <tr>
                <td>{{ $f['mc_cases'] ?? '' }}</td>
                <td>{{ $f['mc_convicted'] ?? '' }}</td>
                <td>{{ $f['mc_fines'] ?? '' }}</td>
                <td>{{ $f['mc_imprisoned'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <h2>(১০) চিকিৎসা বর্জ্য ব্যবস্থাপনা</h2>
    <table class="field-grid">
        <tr><td class="label">১০ (অ) সরকারি হাসপাতাল/ক্লিনিক</td><td class="value">{{ $f['gov_hospitals'] ?? '' }}</td></tr>
        <tr><td class="label">১০ (আ) পৌরসভা/সিটি কর্পোরেশন</td><td class="value">{{ $f['city_hospitals'] ?? '' }}</td></tr>
        <tr><td class="label">১০ (ই) বেসরকারি</td><td class="value">{{ $f['private_hospitals'] ?? '' }}</td></tr>
        <tr><td class="label">১০ (ঈ) বিধিমালা ২০০৮ অনুসরণ</td><td class="value">{{ $yn($f['medical_compliance'] ?? '') }}</td></tr>
        <tr><td class="label">১০ (উ) অসুবিধার বিবরণ</td><td class="value text-block">{{ $f['medical_issues'] ?? '' }}</td></tr>
    </table>
</div>

<br><br>
<table class="field-grid">
    <tr><td class="label">স্বাক্ষর ও তারিখ</td><td class="value" style="height:40px;"></td></tr>
    <tr><td class="label">নাম ও পদবি (সীল মোহর)</td><td class="value" style="height:40px;"></td></tr>
</table>
</body>
</html>
