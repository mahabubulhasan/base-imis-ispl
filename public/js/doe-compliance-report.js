(function () {
    'use strict';

    const cfg = window.doeComplianceReportConfig || {};
    const ynOptionsHtml =
        '<option value="">--</option><option value="yes">হ্যাঁ</option><option value="no">না</option>';

    function renumberTable(tableId) {
        const tbody = document.querySelector('#' + tableId + ' tbody');
        if (!tbody) return;
        Array.from(tbody.rows).forEach((row, i) => {
            if (row.cells[0]) row.cells[0].textContent = String(i + 1);
        });
    }

    function nextIndex(tableId) {
        const tbody = document.querySelector('#' + tableId + ' tbody');
        return tbody ? tbody.rows.length : 0;
    }

    function initLandfillTypeSelect($el, value) {
        if (!$el.length || !$.fn.select2) return;
        if ($el.data('select2')) $el.select2('destroy');
        const data = (cfg.landfillTypeOptions || []).map((t) => ({ id: t, text: t }));
        $el.select2({
            width: '100%',
            tags: true,
            data: data,
            placeholder: 'নির্বাচন বা লিখুন',
            allowClear: true,
        });
        if (value) {
            if (!$el.find('option[value="' + value.replace(/"/g, '\\"') + '"]').length) {
                const opt = new Option(value, value, true, true);
                $el.append(opt);
            }
            $el.val(value).trigger('change');
        }
    }

    function initAllLandfillTypeSelects() {
        $('.doe-lf-type-select').each(function () {
            initLandfillTypeSelect($(this), $(this).val());
        });
    }

    function initLandfillEquipmentSiteSelect($el, value) {
        if (!$el.length || !$.fn.select2) return;
        if ($el.data('select2')) $el.select2('destroy');
        const data = (cfg.landfillCatalog || []).map((lf) => ({ id: lf.name, text: lf.name }));
        $el.select2({
            width: '100%',
            tags: true,
            data: data,
            placeholder: 'নির্বাচন বা লিখুন',
            allowClear: true,
        });
        if (value) {
            if (!$el.find('option[value="' + value.replace(/"/g, '\\"') + '"]').length) {
                const opt = new Option(value, value, true, true);
                $el.append(opt);
            }
            $el.val(value).trigger('change');
        }
    }

    function initAllLandfillEquipmentSiteSelects() {
        $('#tbl-equipment .doe-lf-equipment-site-select').each(function () {
            initLandfillEquipmentSiteSelect($(this), $(this).val());
        });
    }

    function initWasteBinTypeSelect($el, value) {
        if (!$el.length || !$.fn.select2) return;
        if ($el.data('select2')) $el.select2('destroy');
        const data = (cfg.wasteBinTypeOptions || []).map((t) => ({ id: t, text: t }));
        $el.select2({
            width: '100%',
            data: data,
            placeholder: 'নির্বাচন করুন',
            allowClear: true,
        });
        if (value) {
            if (!$el.find('option[value="' + value.replace(/"/g, '\\"') + '"]').length) {
                const opt = new Option(value, value, true, true);
                $el.append(opt);
            }
            $el.val(value).trigger('change');
        }
    }

    function initAllWasteBinTypeSelects() {
        $('#tbl-bins .doe-bin-type-select').each(function () {
            initWasteBinTypeSelect($(this), $(this).val());
        });
    }

    function wasteBinFromCatalog(typeName) {
        if (!typeName) return null;
        return (cfg.wasteBinCatalog || []).find((b) => b.type === typeName) || null;
    }

    function applyWasteBinTypeSelection($row, typeName) {
        const bin = wasteBinFromCatalog(typeName);
        $row.find('.doe-bin-size').val(bin?.size || '');
        $row.find('.doe-bin-unit').val(bin?.unit || '');
        $row.find('.doe-bin-count').val(bin?.count || '');
    }

    function bindWasteBinTypeChange() {
        $(document).on('change select2:select', '#tbl-bins .doe-bin-type-select', function () {
            applyWasteBinTypeSelection($(this).closest('tr'), this.value);
        });
    }

    function initVehicleTypeSelect($el, value) {
        if (!$el.length || !$.fn.select2) return;
        if ($el.data('select2')) $el.select2('destroy');
        const data = (cfg.vehicleTypeOptions || []).map((t) => ({ id: t, text: t }));
        $el.select2({
            width: '100%',
            data: data,
            placeholder: 'নির্বাচন করুন',
            allowClear: true,
        });
        if (value) {
            if (!$el.find('option[value="' + value.replace(/"/g, '\\"') + '"]').length) {
                const opt = new Option(value, value, true, true);
                $el.append(opt);
            }
            $el.val(value).trigger('change');
        }
    }

    function initAllVehicleTypeSelects() {
        $('#tbl-transport .doe-vehicle-type-select').each(function () {
            initVehicleTypeSelect($(this), $(this).val());
        });
    }

    function vehicleFromCatalog(typeName) {
        if (!typeName) return null;
        return (cfg.vehicleCatalog || []).find((v) => v.type === typeName) || null;
    }

    function applyVehicleTypeSelection($row, typeName) {
        const vehicle = vehicleFromCatalog(typeName);
        $row.find('.doe-vehicle-existing').val(vehicle?.existing || '');
        $row.find('.doe-vehicle-required').val(vehicle?.required || '');
    }

    function bindVehicleTypeChange() {
        $(document).on('change select2:select', '#tbl-transport .doe-vehicle-type-select', function () {
            applyVehicleTypeSelection($(this).closest('tr'), this.value);
        });
    }

    function landfillFromCatalog(landfillId) {
        if (!landfillId) return null;
        return (cfg.landfillCatalog || []).find((lf) => String(lf.landfill_id) === String(landfillId)) || null;
    }

    function resolveLandfillId(data) {
        if (data?.landfill_id) return String(data.landfill_id);
        if (!data?.name) return '';
        const match = (cfg.landfillCatalog || []).find((lf) => lf.name === data.name);
        return match ? String(match.landfill_id) : '';
    }

    /** Strip grouping commas so values work in <input type="number">. */
    function capacityInputValue(value) {
        if (value === undefined || value === null || value === '') return '';
        return String(value).replace(/,/g, '').trim();
    }

    function initLandfillSiteSelect($el, value) {
        if (!$el.length || !$.fn.select2) return;
        if ($el.data('select2')) $el.select2('destroy');
        $el.select2({
            width: '100%',
            placeholder: $el.data('placeholder') || 'নির্বাচন করুন',
            allowClear: true,
        });
        if (value) {
            $el.val(String(value)).trigger('change');
        }
    }

    function applyLandfillSelection($row, landfillId) {
        const lf = landfillFromCatalog(landfillId);
        $row.find('.doe-lf-name-hidden').val(lf?.name || '');
        const $type = $row.find('.doe-lf-type-select');
        if (lf?.type) {
            initLandfillTypeSelect($type, lf.type);
        } else if ($type.length) {
            initLandfillTypeSelect($type, $type.val());
        }
        const $capacity = $row.find('.doe-lf-capacity');
        const $unit = $row.find('.doe-lf-capacity-unit');
        if (lf) {
            $capacity.val(capacityInputValue(lf.capacity));
            $unit.val(lf.unit || cfg.defaultLandfillUnit || 'টন');
        }
    }

    function applyLandfillInfoSelection($row, landfillId) {
        const lf = landfillFromCatalog(landfillId);
        $row.find('.doe-lf-name-hidden').val(lf?.name || '');
        if (!lf) return;
        $row.find('.doe-lf-area').val(capacityInputValue(lf.area));
        $row.find('.doe-lf-area-unit').val(lf.area_unit || cfg.defaultLandfillAreaUnit || 'একর');
        ['wb', 'bw', 'lt', 'cv', 'gas', 'lc'].forEach((f) => {
            const sel = $row.find('[name*="[' + f + ']"]')[0];
            if (sel) sel.value = lf[f] || '';
        });
        const pax = $row.find('[name*="[pax]"]')[0];
        if (pax) pax.value = lf.pax || '';
    }

    function appendLandfillSiteOptions($select) {
        (cfg.landfillCatalog || []).forEach((lf) => {
            const opt = new Option(lf.name, String(lf.landfill_id));
            opt.dataset.capacity = capacityInputValue(lf.capacity);
            opt.dataset.type = lf.type || '';
            opt.dataset.unit = lf.unit || cfg.defaultLandfillUnit || 'টন';
            $select[0].add(opt);
        });
    }

    function initAllLandfillSiteSelects() {
        $('#tbl-landfill-type .doe-lf-site-select').each(function () {
            const $el = $(this);
            initLandfillSiteSelect($el, $el.val());
            applyLandfillSelection($el.closest('tr'), $el.val());
        });
        $('#tbl-landfill-info .doe-lf-site-select').each(function () {
            const $el = $(this);
            initLandfillSiteSelect($el, $el.val());
            applyLandfillInfoSelection($el.closest('tr'), $el.val());
        });
    }

    function bindLandfillSiteChange() {
        $(document).on('change select2:select', '#tbl-landfill-type .doe-lf-site-select', function () {
            applyLandfillSelection($(this).closest('tr'), this.value);
        });
        $(document).on('change select2:select', '#tbl-landfill-info .doe-lf-site-select', function () {
            applyLandfillInfoSelection($(this).closest('tr'), this.value);
        });
    }

    function initialLandfillTypeRows(report) {
        const fromReport = report?.landfill_types;
        if (fromReport && fromReport.length) {
            return fromReport;
        }
        const catalog = report?.landfill_catalog || cfg.landfillCatalog || [];

        return catalog;
    }

    function initialLandfillInfoRows(report) {
        const sites = report?.landfill_info?.sites;
        if (sites && sites.length) {
            return sites;
        }
        const catalog = report?.landfill_catalog || cfg.landfillCatalog || [];

        return catalog.map((lf) => ({
            landfill_id: lf.landfill_id,
            name: lf.name,
            area: lf.area,
            unit: lf.area_unit || cfg.defaultLandfillAreaUnit || 'একর',
            wb: lf.wb,
            bw: lf.bw,
            lt: lf.lt,
            pax: lf.pax,
            cv: lf.cv,
            gas: lf.gas,
            lc: lf.lc,
        }));
    }

    function bindDeleteRows() {
        document.querySelectorAll('.doe-report-page').forEach((root) => {
            root.addEventListener('click', (e) => {
                const btn = e.target.closest('.doe-del-row');
                if (!btn) return;
                const tr = btn.closest('tr');
                const table = btn.closest('table');
                if (tr && table) {
                    tr.remove();
                    if (table.id) renumberTable(table.id);
                }
            });
        });
    }

    function addLandfillTypeRow(data) {
        const i = nextIndex('tbl-landfill-type');
        const tbody = document.querySelector('#tbl-landfill-type tbody');
        const tr = document.createElement('tr');
        const landfillId = data?.landfill_id || '';
        tr.innerHTML =
            '<td></td>' +
            '<td><input type="hidden" class="doe-lf-name-hidden" name="landfill_types[' +
            i +
            '][name]" value="' +
            (data?.name || '') +
            '" />' +
            '<select class="form-control doe-lf-site-select" name="landfill_types[' +
            i +
            '][landfill_id]" style="width:100%" data-placeholder="নির্বাচন করুন"><option value=""></option></select></td>' +
            '<td><select class="form-control doe-lf-type-select" name="landfill_types[' +
            i +
            '][type]" style="width:100%"></select></td>' +
            '<td><input type="number" step="0.01" min="0" class="doe-lf-capacity auto-filled" name="landfill_types[' +
            i +
            '][capacity]" value="' +
            capacityInputValue(data?.capacity) +
            '" title="ল্যান্ডফিল ধারণ ক্ষমতা (টন)" /></td>' +
            '<td><input type="text" class="doe-lf-capacity-unit" name="landfill_types[' +
            i +
            '][unit]" value="' +
            (data?.unit || cfg.defaultLandfillUnit || 'টন') +
            '" /></td>' +
            '<td class="col-act"><button type="button" class="del-btn doe-del-row">✕</button></td>';
        tbody.appendChild(tr);
        const $tr = $(tr);
        const $site = $tr.find('.doe-lf-site-select');
        appendLandfillSiteOptions($site);
        initLandfillSiteSelect($site, landfillId);
        initLandfillTypeSelect($tr.find('.doe-lf-type-select'), data?.type || '');
        applyLandfillSelection($tr, landfillId || data?.landfill_id);
        const cap = capacityInputValue(data?.capacity ?? landfillFromCatalog(landfillId)?.capacity);
        if (cap) {
            $tr.find('.doe-lf-capacity').val(cap);
        }
        renumberTable('tbl-landfill-type');
    }

    function makeYnSelect(name, value) {
        const td = document.createElement('td');
        const sel = document.createElement('select');
        sel.className = 'form-control doe-yn-select';
        sel.name = name;
        sel.innerHTML = ynOptionsHtml;
        if (value) sel.value = value;
        td.appendChild(sel);
        return td;
    }

    function addLandfillInfoRow(data) {
        const i = nextIndex('tbl-landfill-info');
        const tbody = document.querySelector('#tbl-landfill-info tbody');
        const tr = document.createElement('tr');
        const prefix = 'landfill_sites[' + i + ']';
        const landfillId = resolveLandfillId(data);
        tr.appendChild(document.createElement('td'));
        const nameTd = document.createElement('td');
        nameTd.className = 'doe-lf-site-cell';
        const nameHidden = document.createElement('input');
        nameHidden.type = 'hidden';
        nameHidden.className = 'doe-lf-name-hidden';
        nameHidden.name = prefix + '[name]';
        nameHidden.value = data?.name || '';
        nameTd.appendChild(nameHidden);
        const siteSel = document.createElement('select');
        siteSel.className = 'form-control doe-lf-site-select';
        siteSel.name = prefix + '[landfill_id]';
        siteSel.style.width = '100%';
        siteSel.setAttribute('data-placeholder', 'নির্বাচন করুন');
        siteSel.innerHTML = '<option value=""></option>';
        nameTd.appendChild(siteSel);
        tr.appendChild(nameTd);
        const areaTd = document.createElement('td');
        const areaInp = document.createElement('input');
        areaInp.type = 'number';
        areaInp.step = '0.01';
        areaInp.min = '0';
        areaInp.name = prefix + '[area]';
        areaInp.className = 'auto-filled doe-lf-area';
        areaInp.title = 'ল্যান্ডফিল আয়তন (একর)';
        areaInp.value = capacityInputValue(data?.area);
        areaTd.appendChild(areaInp);
        tr.appendChild(areaTd);
        const unitTd = document.createElement('td');
        const unitInp = document.createElement('input');
        unitInp.type = 'text';
        unitInp.name = prefix + '[unit]';
        unitInp.className = 'auto-filled doe-lf-area-unit';
        unitInp.value = data?.unit || cfg.defaultLandfillAreaUnit || 'একর';
        unitTd.appendChild(unitInp);
        tr.appendChild(unitTd);
        ['wb', 'bw', 'lt'].forEach((f) => tr.appendChild(makeYnSelect(prefix + '[' + f + ']', data?.[f])));
        const paxTd = document.createElement('td');
        const paxInp = document.createElement('input');
        paxInp.type = 'number';
        paxInp.name = prefix + '[pax]';
        paxInp.value = data?.pax || '';
        paxTd.appendChild(paxInp);
        tr.appendChild(paxTd);
        ['cv', 'gas', 'lc'].forEach((f) => tr.appendChild(makeYnSelect(prefix + '[' + f + ']', data?.[f])));
        const actTd = document.createElement('td');
        actTd.className = 'col-act';
        actTd.innerHTML = '<button type="button" class="del-btn doe-del-row">✕</button>';
        tr.appendChild(actTd);
        tbody.appendChild(tr);
        const $tr = $(tr);
        const $site = $tr.find('.doe-lf-site-select');
        appendLandfillSiteOptions($site);
        initLandfillSiteSelect($site, landfillId);
        applyLandfillInfoSelection($tr, landfillId);
        const areaVal = capacityInputValue(data?.area ?? landfillFromCatalog(landfillId)?.area);
        if (areaVal) {
            $tr.find('.doe-lf-area').val(areaVal);
        }
        renumberTable('tbl-landfill-info');
    }

    function addDoorOrgRow(data) {
        const i = nextIndex('tbl-door-orgs');
        const tbody = document.querySelector('#tbl-door-orgs tbody');
        const tr = document.createElement('tr');
        tr.innerHTML =
            '<td></td>' +
            '<td><input type="text" name="door_orgs[' + i + '][name]" value="' + (data?.name || '') + '" /></td>' +
            '<td><textarea name="door_orgs[' + i + '][desc]" rows="2">' + (data?.description || data?.desc || '') + '</textarea></td>' +
            '<td class="col-act"><button type="button" class="del-btn doe-del-row">✕</button></td>';
        tbody.appendChild(tr);
        renumberTable('tbl-door-orgs');
    }

    function addBinRow(data) {
        const i = nextIndex('tbl-bins');
        const tbody = document.querySelector('#tbl-bins tbody');
        const tr = document.createElement('tr');
        tr.innerHTML =
            '<td></td>' +
            '<td><select class="form-control doe-bin-type-select" name="bins[' +
            i +
            '][type]" style="width:100%"></select></td>' +
            '<td><input type="text" class="auto-filled doe-bin-size" name="bins[' +
            i +
            '][size]" value="' +
            (data?.size || '') +
            '" /></td>' +
            '<td><input type="text" class="auto-filled doe-bin-unit" name="bins[' +
            i +
            '][unit]" value="' +
            (data?.unit || '') +
            '" /></td>' +
            '<td><input type="number" min="0" class="auto-filled doe-bin-count" name="bins[' +
            i +
            '][count]" value="' +
            (data?.count || '') +
            '" /></td>' +
            '<td class="col-act"><button type="button" class="del-btn doe-del-row">✕</button></td>';
        tbody.appendChild(tr);
        const $tr = $(tr);
        initWasteBinTypeSelect($tr.find('.doe-bin-type-select'), data?.type || '');
        applyWasteBinTypeSelection($tr, data?.type || '');
        renumberTable('tbl-bins');
    }

    function addEquipmentRow(data) {
        const i = nextIndex('tbl-equipment');
        const tbody = document.querySelector('#tbl-equipment tbody');
        const tr = document.createElement('tr');
        tr.innerHTML =
            '<td></td>' +
            '<td class="doe-lf-site-cell"><select class="form-control doe-lf-equipment-site-select" name="equipment[' +
            i +
            '][name]" style="width:100%"></select></td>' +
            '<td><input type="text" name="equipment[' +
            i +
            '][eq]" /></td>' +
            '<td><input type="number" min="0" name="equipment[' +
            i +
            '][count]" /></td>' +
            '<td class="col-act"><button type="button" class="del-btn doe-del-row">✕</button></td>';
        tbody.appendChild(tr);
        const $tr = $(tr);
        initLandfillEquipmentSiteSelect($tr.find('.doe-lf-equipment-site-select'), data?.name || '');
        if (data?.eq) $tr.find('[name*="[eq]"]').val(data.eq);
        if (data?.count !== undefined && data?.count !== '') {
            $tr.find('[name*="[count]"]').val(data.count);
        }
        renumberTable('tbl-equipment');
    }

    function addTransportRow(data) {
        const i = nextIndex('tbl-transport');
        const tbody = document.querySelector('#tbl-transport tbody');
        const tr = document.createElement('tr');
        tr.innerHTML =
            '<td></td>' +
            '<td><select class="form-control doe-vehicle-type-select" name="transport[' +
            i +
            '][type]" style="width:100%"></select></td>' +
            '<td><input type="number" min="0" class="auto-filled doe-vehicle-existing" name="transport[' +
            i +
            '][existing]" value="' +
            (data?.existing || '') +
            '" /></td>' +
            '<td><input type="number" min="0" class="doe-vehicle-required" name="transport[' +
            i +
            '][required]" value="' +
            (data?.required || '') +
            '" /></td>' +
            '<td class="col-act"><button type="button" class="del-btn doe-del-row">✕</button></td>';
        tbody.appendChild(tr);
        const $tr = $(tr);
        initVehicleTypeSelect($tr.find('.doe-vehicle-type-select'), data?.type || '');
        applyVehicleTypeSelection($tr, data?.type || '');
        renumberTable('tbl-transport');
    }

    function setVal(name, value) {
        const el = document.querySelector('[name="' + name + '"]');
        if (el && value !== undefined && value !== null) el.value = value;
    }

    function applyReport(report) {
        if (!report) return;
        if (report.year) {
            updateReportDisplayYear(report.year);
        }
        const inst = report.institution || {};
        const wq = report.waste_quantity || {};
        const wp = report.waste_processing || {};
        const storage = report.storage || {};
        const lic = report.lic || {};
        const lfInfo = report.landfill_info || {};

        setVal('org_name', inst.org_name);
        updateReportMunicipalityName(inst.org_name);
        setVal('population', inst.population);
        setVal('daily_avg', wq.daily_avg);
        setVal('annual_total', wq.annual_total);
        setVal('collected_formal', wq.collected_formal);
        setVal('stockpiled', wq.stockpiled);
        setVal('uncollected', wq.uncollected);
        Object.keys(wp).forEach((k) => setVal(k, wp[k]));
        setVal('collection_volume', storage.collection_volume);
        setVal('num_houses', storage.num_houses);
        setVal('num_landfill_sites', lfInfo.num_landfill_sites);
        setVal('total_slums', lic.total_slums);
        setVal('slums_sanitation', lic.slums_sanitation);

        const rebuild = (tableId, rows, addFn) => {
            const tbody = document.querySelector('#' + tableId + ' tbody');
            if (!tbody) return;
            tbody.innerHTML = '';
            (rows || []).forEach((row) => addFn(row));
        };

        if (report.landfill_catalog) {
            cfg.landfillCatalog = report.landfill_catalog;
        }
        if (report.waste_bin_catalog) {
            cfg.wasteBinCatalog = report.waste_bin_catalog;
        }
        if (report.waste_bin_type_options) {
            cfg.wasteBinTypeOptions = report.waste_bin_type_options;
        }
        if (report.vehicle_catalog) {
            cfg.vehicleCatalog = report.vehicle_catalog;
        }
        if (report.vehicle_type_options) {
            cfg.vehicleTypeOptions = report.vehicle_type_options;
        }
        rebuild('tbl-landfill-type', initialLandfillTypeRows(report), addLandfillTypeRow);
        rebuild('tbl-landfill-info', initialLandfillInfoRows(report), addLandfillInfoRow);
        rebuild('tbl-door-orgs', report.private_organizations, addDoorOrgRow);
        rebuild('tbl-bins', report.bins, addBinRow);
        rebuild('tbl-transport', report.transport, addTransportRow);
        rebuild('tbl-equipment', report.equipment, addEquipmentRow);
    }

    const bnDigitMap = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    function toBengaliDigits(value) {
        return String(value).replace(/\d/g, (d) => bnDigitMap[Number(d)]);
    }

    function updateReportDisplayYear(year) {
        if (!year) return;
        const bnYear = toBengaliDigits(year);
        document.querySelectorAll('.doe-report-display-year').forEach((el) => {
            el.textContent = bnYear;
        });
    }

    function updateReportMunicipalityName(name) {
        const value = name || '';
        document.querySelectorAll('.doe-report-municipality-name').forEach((el) => {
            el.textContent = value;
        });
    }

    function refreshData() {
        const year = document.getElementById('doe-report-year')?.value;
        if (!year || !cfg.dataUrl) return;
        document.getElementById('doe-form-year').value = year;
        updateReportDisplayYear(year);
        fetch(cfg.dataUrl + '?year=' + encodeURIComponent(year), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((r) => r.json())
            .then(applyReport)
            .catch(() => alert('ডেটা লোড করতে ব্যর্থ হয়েছে'));
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindDeleteRows();
        bindLandfillSiteChange();
        bindWasteBinTypeChange();
        bindVehicleTypeChange();

        const yearSelect = document.getElementById('doe-report-year');
        if (yearSelect) {
            updateReportDisplayYear(yearSelect.value);
        }

        if (cfg.initialReport) {
            applyReport(cfg.initialReport);
        } else {
            initAllLandfillSiteSelects();
            initAllLandfillTypeSelects();
            initAllLandfillEquipmentSiteSelects();
            initAllWasteBinTypeSelects();
            initAllVehicleTypeSelects();
        }

        document.getElementById('doe-report-refresh')?.addEventListener('click', refreshData);
        document.getElementById('doe-report-year')?.addEventListener('change', function () {
            document.getElementById('doe-form-year').value = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('year', this.value);
            window.history.replaceState({}, '', url);
            refreshData();
        });

        document.getElementById('doe-add-landfill-type')?.addEventListener('click', () => addLandfillTypeRow({}));
        document.getElementById('doe-add-landfill-info')?.addEventListener('click', () => addLandfillInfoRow({}));
        document.getElementById('doe-add-door-org')?.addEventListener('click', () => addDoorOrgRow({}));
        document.getElementById('doe-add-bin')?.addEventListener('click', () => addBinRow({}));
        document.getElementById('doe-add-transport')?.addEventListener('click', () => addTransportRow({}));
        document.getElementById('doe-add-equipment')?.addEventListener('click', () => addEquipmentRow({}));
        document.getElementById('doe-add-contract')?.addEventListener('click', () => {
            const i = nextIndex('tbl-contracts');
            const tbody = document.querySelector('#tbl-contracts tbody');
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td></td><td><input type="text" name="contracts[' + i + '][person]" /></td>' +
                '<td><input type="text" name="contracts[' + i + '][tech]" /></td>' +
                '<td><input type="text" name="contracts[' + i + '][dur]" /></td>' +
                '<td><textarea name="contracts[' + i + '][addr]" rows="2"></textarea></td>' +
                '<td class="col-act"><button type="button" class="del-btn doe-del-row">✕</button></td>';
            tbody.appendChild(tr);
            renumberTable('tbl-contracts');
        });

        document.getElementById('doe-report-reset')?.addEventListener('click', function () {
            if (!confirm('সকল ফিল্ড রিসেট করতে চান?')) return;
            applyReport(cfg.initialReport);
        });
    });
})();
