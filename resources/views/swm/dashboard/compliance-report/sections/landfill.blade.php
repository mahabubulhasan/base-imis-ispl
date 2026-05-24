        <div class="subsection">
            <h3 class="subsection-title">২.৮ ল্যান্ডফিলের প্রকার @include('swm.dashboard.compliance-report.partials.auto-tip')</h3>
            <div class="tbl-wrap">
                <table class="tbl" id="tbl-landfill-type">
                    <thead>
                        <tr>
                            <th style="width:36px;">ক্রম</th>
                            <th>ল্যান্ডফিল সাইটের নাম</th>
                            <th>প্রকার</th>
                            <th class="col-num" title="{{ __('Landfill capacity (ton) from IMIS') }}">ধারণ ক্ষমতা</th>
                            <th class="col-num">ধারণ ক্ষমতার একক</th>
                            <th class="col-act"></th>
                        </tr>
                    </thead>
                    <tbody>{{-- rows built by doe-compliance-report.js from landfill_types / landfill_catalog --}}</tbody>
                </table>
            </div>
            <div class="tbl-toolbar"><button type="button" class="add-row-btn" id="doe-add-landfill-type">+ সাইট যোগ করুন</button></div>
        </div>

        <div class="subsection">
            <h3 class="subsection-title">২.৯ ল্যান্ডফিলের অন্যান্য তথ্যাদি</h3>
            <div class="doe-field-colon-row">
                <label><span class="num">(অ)</span><span class="text">ল্যান্ডফিল স্থানের সংখ্যা</span>@include('swm.dashboard.compliance-report.partials.auto-tip')</label>
                <span class="doe-field-colon" aria-hidden="true">:</span>
                <input name="num_landfill_sites" type="number" min="0" class="auto-filled" value="{{ $lfInfo['num_landfill_sites'] ?? '' }}" />
            </div>
            <div class="tbl-wrap">
                <table class="tbl" id="tbl-landfill-info">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width:36px;">ক্রম</th>
                            <th rowspan="2" class="doe-lf-site-col">ল্যান্ডফিল সাইটের নাম</th>
                            <th class="col-num doe-th-marker">(আ)</th>
                            <th class="col-num doe-th-marker">(আ)</th>
                            <th class="col-yn doe-th-marker">(ই)</th>
                            <th class="col-yn doe-th-marker">(ঈ)</th>
                            <th class="col-yn doe-th-marker">(উ)</th>
                            <th class="col-num doe-th-marker">(ঊ)</th>
                            <th class="col-yn doe-th-marker">(ঋ)</th>
                            <th class="col-yn doe-th-marker">(এ)</th>
                            <th class="col-yn doe-th-marker">(ঐ)</th>
                            <th class="col-act doe-th-marker"></th>
                        </tr>
                        <tr>
                            <th class="col-num">আয়তন</th>
                            <th class="col-num">আয়তনের একক</th>
                            <th class="col-yn">Weighbridge</th>
                            <th class="col-yn">সীমানা প্রাচীর</th>
                            <th class="col-yn">আলোকিতকরণ</th>
                            <th class="col-num">জনবল সংখ্যা</th>
                            <th class="col-yn">আচ্ছাদিতকরণ</th>
                            <th class="col-yn">গ্যাস নিয়ন্ত্রণ</th>
                            <th class="col-yn">চোয়ানি সংগ্রহ</th>
                            <th class="col-act" ></th>
                        </tr>
                    </thead>
                    <tbody>{{-- rows built by doe-compliance-report.js from landfill_info.sites --}}</tbody>
                </table>
            </div>
            <div class="tbl-toolbar"><button type="button" class="add-row-btn" id="doe-add-landfill-info">+ সাইট যোগ করুন</button></div>

            <h3 class="subsection-title" style="margin-top:18px;">(ও) ল্যান্ডফিলের যন্ত্রপাতির তথ্য</h3>
            <p class="subsection-note">বুলডোজার, কম্প্যাক্টর এবং অনুরূপ অন্যান্য ধরনের যন্ত্রপাতি থাকিলে উহার নাম।</p>
            <div class="tbl-wrap">
                <table class="tbl" id="tbl-equipment">
                    <thead>
                        <tr>
                            <th style="width:36px;">ক্রম</th>
                            <th class="doe-lf-site-col">ল্যান্ডফিল সাইটের নাম</th>
                            <th>যন্ত্রপাতির ধরন</th>
                            <th class="col-num">সংখ্যা</th>
                            <th class="col-act"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="tbl-toolbar"><button type="button" class="add-row-btn" id="doe-add-equipment">+ যন্ত্রপাতি যোগ করুন</button></div>
        </div>
