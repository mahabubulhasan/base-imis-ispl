<section class="section">
    <div class="section-header"><h2>(৫) কঠিন বর্জ্য ব্যবস্থাপনার উন্নতি সাধনে কোনো প্রস্তাব থাকলে তা বিবৃত করুন</h2></div>
    <div class="section-body">
        <textarea name="proposals" rows="5"></textarea>
    </div>
</section>

<section class="section">
    <div class="section-header"><h2>(৬) কোনো বেসরকারি ব্যক্তি বা প্রতিষ্ঠানকে কঠিন বর্জ্য প্রক্রিয়াকরণের দায়িত্ব প্রদান করা হয়ে থাকলে তার বিবরণ</h2></div>
    <div class="section-body">
        <div class="tbl-wrap">
            <table class="tbl" id="tbl-contracts">
                <thead>
                    <tr>
                        <th style="width:36px;">ক্রম</th>
                        <th>ব্যক্তি বা প্রতিষ্ঠানের নাম</th>
                        <th>প্রযুক্তির নাম</th>
                        <th>প্রক্রিয়াকরণের সময় ও পরিমাণ</th>
                        <th>নাম ও ঠিকানা</th>
                        <th class="col-act"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div class="tbl-toolbar"><button type="button" class="add-row-btn" id="doe-add-contract">+ চুক্তি যোগ করুন</button></div>
    </div>
</section>

<section class="section">
    <div class="section-header"><h2>(৭) নিম্নোক্ত কার্যাদি স্বাস্থ্যসম্মত উপায়ে সম্পাদন নিশ্চিতকরণের লক্ষ্যে গৃহীত পদক্ষেপ</h2></div>
    <div class="section-body">
        <div class="grid cols-1">
            @foreach([
                ['step_dairy', '(অ)', 'দুগ্ধ খামার'],
                ['step_slaughter', '(আ)', 'পশু জবেহ'],
                ['step_construction', '(ই)', 'নির্মাণ-ভাঙন বর্জ্য'],
                ['step_encroachment', '(ঈ)', 'পার্ক, হাঁটার পথ, ইত্যাদি জবর দখল'],
            ] as [$name, $num, $label])
            <div class="field">
                <label><span class="num">{{ $num }}</span><span class="text">{{ $label }}</span></label>
                <textarea name="{{ $name }}" rows="2"></textarea>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="section-header"><h2>(৮) বস্তি</h2></div>
    <div class="section-body">
        <div class="grid">
            <div class="field">
                <label><span class="num">৮.১</span><span class="text">মোট বস্তির সংখ্যা</span>@include('swm.dashboard.compliance-report.partials.auto-tip')</label>
                <input name="total_slums" type="number" min="0" class="auto-filled" value="{{ $lic['total_slums'] ?? '' }}" />
            </div>
            <div class="field">
                <label><span class="num">৮.২</span><span class="text">স্যানিটারী ব্যবস্থা (Sanitation Facility) সম্পন্ন বস্তির সংখ্যা</span>@include('swm.dashboard.compliance-report.partials.auto-tip')</label>
                <input name="slums_sanitation" type="number" min="0" class="auto-filled" value="{{ $lic['slums_sanitation'] ?? '' }}" />
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-header"><h2>(৯) মোবাইল কোর্ট পরিচালনা করা হয়ে থাকলে তার বিবরণ</h2></div>
    <div class="section-body">
        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>মোট মামলার সংখ্যা</th>
                        <th>দোষী সাব্যস্ত আসামীর সংখ্যা</th>
                        <th>আদায়কৃত জরিমানার পরিমাণ (টাকা)</th>
                        <th>কারাদণ্ডে দণ্ডিত আসামীর সংখ্যা</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="number" min="0" name="mc_cases" /></td>
                        <td><input type="number" min="0" name="mc_convicted" /></td>
                        <td><input type="number" min="0" step="0.01" name="mc_fines" /></td>
                        <td><input type="number" min="0" name="mc_imprisoned" /></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-header"><h2>(১০) চিকিৎসা বর্জ্য ব্যবস্থাপনা</h2></div>
    <div class="section-body">
        <div class="grid">
            <div class="field">
                <label><span class="num">(অ)</span><span class="text">সরকারি হাসপাতাল বা ক্লিনিক বা স্বাস্থ্য কেন্দ্র সংখ্যা</span></label>
                <input name="gov_hospitals" type="number" min="0" />
            </div>
            <div class="field">
                <label><span class="num">(আ)</span><span class="text">সিটি কর্পোরেশন বা পৌরসভার হাসপাতাল বা ক্লিনিক সংখ্যা</span></label>
                <input name="city_hospitals" type="number" min="0" />
            </div>
            <div class="field doe-field-full-row">
                <label><span class="num">(ই)</span><span class="text">বেসরকারি হাসপাতাল বা ক্লিনিক সংখ্যা</span></label>
                <input name="private_hospitals" type="number" min="0" />
            </div>
            <div class="field doe-field-full-row">
                <label><span class="num">(ঈ)</span><span class="text">সকল হাসপাতাল বা ক্লিনিক ও স্বাস্থ্য কেন্দ্রের ক্ষেত্রে চিকিৎসা বর্জ্য (ব্যবস্থাপনা ও প্রক্রিয়াজাতকরণ) বিধিমালা, ২০০৮ এর বিধানাবলি যথাযথভাবে অনুসরণ করা হয় কিনা</span></label>
                <select name="medical_compliance">
                    <option value="">-- নির্বাচন করুন --</option>
                    <option value="yes">হ্যাঁ</option>
                    <option value="no">না</option>
                </select>
            </div>
            <div class="field doe-field-full-row">
                <label><span class="num">(উ)</span><span class="text">চিকিৎসা বর্জ্য (ব্যবস্থাপনা ও প্রক্রিয়াজাতকরণ) বিধিমালা, ২০০৮ এর বিধানাবলি অনুসরণ করতে কোনো অসুবিধা হয়ে থাকলে তার বিবরণ</span></label>
                <textarea name="medical_issues" rows="3"></textarea>
            </div>
        </div>
    </div>
</section>
