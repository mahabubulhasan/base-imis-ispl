<section class="section">
    <div class="section-header"><h2>(১) প্রতিষ্ঠানের বিবরণ</h2></div>
    <div class="section-body">
        <div class="grid">
            <div class="field">
                <label><span class="num">১.১</span><span class="text">স্থানীয় সরকার প্রতিষ্ঠানের নাম</span>@include('swm.dashboard.compliance-report.partials.auto-tip')</label>
                <input name="org_name" type="text" class="auto-filled" value="{{ $inst['org_name'].' '.config('app.city_suffix_bn', 'পৌরসভা') ?? '' }}" />
            </div>
            <div class="field">
                <label><span class="num">১.২</span><span class="text">মোট জনসংখ্যা</span>@include('swm.dashboard.compliance-report.partials.auto-tip')</label>
                <input name="population" type="number" min="0" class="auto-filled" value="{{ $inst['population'] ?? '' }}" />
            </div>
            <div class="field" style="grid-column:1/-1">
                <label><span class="num">১.৩</span><span class="text">ডাক যোগাযোগের ঠিকানা</span></label>
                <textarea name="address"></textarea>
            </div>
            <div class="field">
                <label><span class="num">১.৪</span><span class="text">কনজার্ভেন্সির দায়িত্বে নিয়োজিত প্রধান কর্মকর্তার নাম</span></label>
                <input name="chief_officer" type="text" />
            </div>
            <div class="field">
                <label><span class="num">১.৫</span><span class="text">ফোন নম্বর</span></label>
                <input name="phone" type="tel" />
            </div>
            <div class="field" style="grid-column:1/-1">
                <label><span class="num">১.৬</span><span class="text">ইমেইল</span></label>
                <input name="email" type="email" />
            </div>
        </div>
    </div>
</section>
