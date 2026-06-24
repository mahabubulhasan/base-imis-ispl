<section class="section">
    <div class="section-header"><h2>(৩) মজুদকরণ সুবিধা</h2></div>
    <div class="section-body">
        <div class="subsection">
            <div class="grid">
                <div class="field">
                    <label><span class="num">৩.১</span><span class="text">বর্জ্য সংগ্রহকরণের আয়তন</span>@include('swm.dashboard.compliance-report.partials.auto-tip')</label>
                    <div class="unit-suffix"><input name="collection_volume" type="number" min="0" step="0.01" class="auto-filled" value="{{ $storage['collection_volume'] ?? '' }}"/><span class="u">টন</span></div>
                </div>
                <div class="field">
                    <label><span class="num">৩.২</span><span class="text">বাড়ির সংখ্যা</span>@include('swm.dashboard.compliance-report.partials.auto-tip', ['tip' => __('Waste bins placed at buildings (year-end snapshot)')])</label>
                    <input name="num_houses" type="number" min="0" class="auto-filled" value="{{ $storage['num_houses'] ?? '' }}" />
                </div>
            </div>
        </div>
        <div class="subsection">
            <h3 class="subsection-title">৩.৩ বাড়ি বাড়ি বর্জ্য সংগ্রহের জন্য কোনো বেসরকারি সংগঠন বা অন্য কাউকে নিয়োগ করা হয়ে থাকলে তার বিবরণ</h3>
            <div class="tbl-wrap">
                <table class="tbl tbl-aligned" id="tbl-door-orgs">
                    <thead>
                        <tr>
                            <th style="width:36px;">ক্রম</th>
                            <th>সংগঠনের নাম</th>
                            <th>বিবরণ</th>
                            <th class="col-act"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($r['private_organizations'] ?? [] as $i => $org)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><input type="text" name="door_orgs[{{ $i }}][name]" value="{{ $org['name'] }}" class="auto-filled" /></td>
                            <td><textarea name="door_orgs[{{ $i }}][desc]" rows="2">{{ $org['description'] }}</textarea></td>
                            <td class="col-act"><button type="button" class="del-btn doe-del-row">✕</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="tbl-toolbar"><button type="button" class="add-row-btn" id="doe-add-door-org">+ সংগঠন যোগ করুন</button></div>
        </div>
        <div class="subsection">
            <h3 class="subsection-title">৩.৪ বর্জ্যাধার (Bins) @include('swm.dashboard.compliance-report.partials.auto-tip')</h3>
            <div class="tbl-wrap">
                <table class="tbl tbl-aligned" id="tbl-bins">
                    <thead>
                        <tr>
                            <th style="width:36px;">ক্রম</th>
                            <th>প্রকার</th>
                            <th>ধারণ ক্ষমতা</th>
                            <th class="col-num">ধারণ ক্ষমতার একক</th>
                            <th class="col-num">সংখ্যা</th>
                            <th class="col-act"></th>
                        </tr>
                    </thead>
                    <tbody>{{-- rows built by doe-compliance-report.js from bins / waste_bin_catalog --}}</tbody>
                </table>
            </div>
            <div class="tbl-toolbar"><button type="button" class="add-row-btn" id="doe-add-bin">+ প্রকার যোগ করুন</button></div>
        </div>
        <div class="subsection">
            <div class="grid">
                <div class="field">
                    <label><span class="num">৩.৫</span><span class="text">সকল আধার ও সংগ্রহস্থল হতে প্রতিদিন বর্জ্য নিয়ে যাওয়া হয় কিনা</span></label>
                    <select name="daily_pickup">
                        <option value="">-- নির্বাচন করুন --</option>
                        <option value="yes">হ্যাঁ</option>
                        <option value="no">না</option>
                    </select>
                </div>
                <div class="field">
                    <label><span class="num">৩.৬</span><span class="text">আধার হতে বর্জ্য কায়িক নাকি যান্ত্রিক উপায়ে উত্তোলন করা হয়</span></label>
                    <select name="lifting_method">
                        <option value="">-- নির্বাচন করুন --</option>
                        <option value="manual">কায়িক</option>
                        <option value="mechanical">যান্ত্রিক</option>
                        <option value="both">উভয়</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</section>
