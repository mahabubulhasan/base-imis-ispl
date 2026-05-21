<section class="section">
    <div class="section-header"><h2>(৪) বর্জ্য পরিবহন</h2></div>
    <div class="section-body">
        <div class="tbl-wrap">
            <table class="tbl" id="tbl-transport">
                <thead>
                    <tr>
                        <th style="width:36px;">ক্রম</th>
                        <th>ধরন</th>
                        <th class="col-num">বিদ্যমান সংখ্যা @include('swm.dashboard.compliance-report.partials.auto-tip')</th>
                        <th class="col-num">প্রকৃত প্রয়োজন</th>
                        <th class="col-act"></th>
                    </tr>
                </thead>
                <tbody>{{-- rows built by doe-compliance-report.js from transport / vehicle_catalog --}}</tbody>
            </table>
        </div>
        <div class="tbl-toolbar"><button type="button" class="add-row-btn" id="doe-add-transport">+ ধরন যোগ করুন</button></div>
    </div>
</section>
