<section class="section">
    <div class="section-header"><h2>(২) কঠিন বর্জ্যের পরিমাণ ও সংশ্লিষ্ট তথ্যাদি</h2></div>
    <div class="section-body">
        <div class="subsection">
            <div class="grid">
                <div class="field">
                    <label><span class="num">২.১</span><span class="text">দৈনিক সৃজিত গড় কঠিন বর্জ্যের পরিমাণ</span>@include('swm.dashboard.compliance-report.partials.auto-tip')</label>
                    <div class="unit-suffix"><input name="daily_avg" type="number" min="0" step="0.01" class="auto-filled" value="{{ $wq['daily_avg'] ?? '' }}"/><span class="u">টন/দিন</span></div>
                </div>
                <div class="field">
                    <label><span class="num">২.২</span><span class="text">বাৎসরিক কঠিন বর্জ্যের পরিমাণ</span>@include('swm.dashboard.compliance-report.partials.auto-tip')</label>
                    <div class="unit-suffix"><input name="annual_total" type="number" min="0" step="0.01" class="auto-filled" value="{{ $wq['annual_total'] ?? '' }}"/><span class="u">টন</span></div>
                </div>
                <div class="field">
                    <label><span class="num">২.৩</span><span class="text">বিধিবদ্ধ পদ্ধতিতে সংগৃহীত বাৎসরিক কঠিন বর্জ্যের পরিমাণ</span>@include('swm.dashboard.compliance-report.partials.auto-tip')</label>
                    <div class="unit-suffix"><input name="collected_formal" type="number" min="0" step="0.01" class="auto-filled" value="{{ $wq['collected_formal'] ?? '' }}"/><span class="u">টন</span></div>
                </div>
                <div class="field">
                    <label><span class="num">২.৪</span><span class="text">অনুমোদিত স্থানে স্তুপীকৃত কঠিন বর্জ্যের পরিমাণ (বাৎসরিক)</span>@include('swm.dashboard.compliance-report.partials.auto-tip')</label>
                    <div class="unit-suffix"><input name="stockpiled" type="number" min="0" step="0.01" class="auto-filled" value="{{ $wq['stockpiled'] ?? '' }}"/><span class="u">টন</span></div>
                </div>
                <div class="field">
                    <label><span class="num">২.৫</span><span class="text">অসংগৃহীত কঠিন বর্জ্যের পরিমাণ (বাৎসরিক)</span>@include('swm.dashboard.compliance-report.partials.auto-tip')</label>
                    <div class="unit-suffix"><input name="uncollected" type="number" min="0" step="0.01" class="auto-filled" value="{{ $wq['uncollected'] ?? '' }}"/><span class="u">টন</span></div>
                </div>
                <div class="field">
                    <label><span class="num">২.৬</span><span class="text">ঝুঁকিপূর্ণ শিল্প বর্জ্য সংগ্রহের পরিমাণ (বাৎসরিক)</span></label>
                    <div class="unit-suffix"><input name="hazardous" type="number" min="0" step="0.01"/><span class="u">টন</span></div>
                </div>
            </div>
        </div>
        <div class="subsection">
            <h3 class="subsection-title">২.৭ বাৎসরিক বর্জ্য প্রক্রিয়াকরণের পরিমাণ (সরকারি/বেসরকারি উদ্যোগে)</h3>
            <div class="grid">
                @foreach([
                    ['proc_organic', '(অ)', 'জৈবিক বর্জ্য প্রক্রিয়াকরণ/কম্পোস্টিং'],
                    ['proc_recycle', '(আ)', 'অজৈবিক অপচনযোগ্য বর্জ্য পুনর্ব্যবহারোপযোগীকরণ'],
                    ['proc_incineration', '(ই)', 'বর্জ্য ইনসিনারেশন'],
                    ['proc_openburn', '(ঈ)', 'উন্মুক্ত অবস্থায় বর্জ্য পোড়ানো'],
                    ['proc_landfill', '(উ)', 'বর্জ্য ল্যান্ডফিলের পরিমাণ'],
                ] as [$key, $num, $label])
                <div class="field">
                    <label><span class="num">{{ $num }}</span><span class="text">{{ $label }}</span>@include('swm.dashboard.compliance-report.partials.auto-tip')</label>
                    <div class="unit-suffix"><input name="{{ $key }}" type="number" min="0" step="0.01" class="auto-filled" value="{{ $wp[$key] ?? '' }}"/><span class="u">টন</span></div>
                </div>
                @endforeach
            </div>
        </div>

        @include('swm.dashboard.compliance-report.sections.landfill')
    </div>
</section>
