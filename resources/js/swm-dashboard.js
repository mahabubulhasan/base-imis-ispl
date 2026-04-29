/**
 * SWM Dashboard & KPIs: client-side charts (Chart.js 2), KPI table AJAX refresh, table search.
 * Expects window.swmDashboardConfig from the Blade view (after app.js loads).
 */
(function () {
    'use strict';

    var cfg = window.swmDashboardConfig || {};
    var ChartCtor = (typeof window.Chart === 'function')
        ? window.Chart
        : (typeof window.chart === 'function' ? window.chart : null);
    var chartInstances = [];

    /* FSM / IMIS dashboard palette (fsmDashboard, _emptyingByStructureTypes, KPI inclusion charts). */
    var palette = {
        primary: 'rgba(54, 162, 235, 0.5)',
        primaryHover: 'rgba(54, 162, 235, 0.7)',
        targetOrange: 'rgba(251, 176, 64, 0.8)',
        targetOrangeHover: 'rgba(251, 176, 64, 0.9)',
        achievementGreen: 'rgba(153, 202, 60, 0.8)',
        achievementGreenHover: 'rgba(153, 202, 60, 0.9)',
        lineOrange: 'rgba(251, 176, 64, 1)',
        lineGreen: 'rgba(153, 202, 60, 1)',
        lineBlueFill: 'rgba(54, 162, 235, 0.15)',
        lineGreenFill: 'rgba(153, 202, 60, 0.12)',
        doughnut: [
            'rgba(54, 162, 235, 0.65)',
            'rgba(251, 176, 64, 0.85)',
            'rgba(153, 202, 60, 0.85)',
            'rgba(90, 155, 212, 0.65)',
            'rgba(255, 99, 132, 0.65)',
            'rgba(153, 102, 255, 0.65)',
        ],
        /* Dark pie slices: FSM containment pie + building-use blues (#023047, #219EBC), see DashboardService::getContainTypeChart */
        fsmPie: [
            'rgba(2, 48, 71, 0.94)',
            'rgba(32, 139, 58, 0.92)',
            'rgba(33, 158, 188, 0.9)',
            'rgba(68, 108, 179, 0.9)',
            'rgba(247, 142, 49, 0.9)',
            'rgba(120, 40, 31, 0.9)',
            'rgba(106, 76, 147, 0.9)',
            'rgba(0, 105, 92, 0.92)',
            'rgba(77, 175, 124, 0.92)',
            'rgba(183, 104, 8, 0.9)',
            'rgba(13, 79, 60, 0.94)',
            'rgba(153, 87, 0, 0.9)',
            'rgba(55, 71, 79, 0.92)',
            'rgba(251, 176, 64, 0.88)',
            'rgba(178, 222, 39, 0.85)',
        ],
        /** Distinct bar colors (wards / categories) — longer list than doughnut for ward charts. */
        indexedBar: [
            'rgba(54, 162, 235, 0.75)',
            'rgba(251, 176, 64, 0.82)',
            'rgba(153, 202, 60, 0.82)',
            'rgba(90, 155, 212, 0.75)',
            'rgba(255, 99, 132, 0.75)',
            'rgba(153, 102, 255, 0.75)',
            'rgba(75, 192, 192, 0.78)',
            'rgba(255, 159, 64, 0.82)',
            'rgba(199, 120, 208, 0.75)',
            'rgba(100, 181, 246, 0.78)',
            'rgba(129, 199, 132, 0.8)',
            'rgba(171, 71, 188, 0.72)',
        ],
        householdsStack: 'rgba(46, 125, 50, 0.88)',
        householdsStackHover: 'rgba(46, 125, 50, 1)',
        vanPullersStack: 'rgba(174, 213, 92, 0.92)',
        vanPullersStackHover: 'rgba(174, 213, 92, 1)',
    };

    function hasDataLabelsPlugin() {
        return typeof window.ChartDataLabels !== 'undefined' && ChartCtor && ChartCtor.plugins;
    }

    function extendOptions(base, extra) {
        var $ = window.jQuery;
        if ($ && $.extend) {
            return $.extend(true, {}, base, extra);
        }
        return Object.assign({}, base || {}, extra || {});
    }

    function fsmAnimation() {
        return { animation: { animateScale: true } };
    }

    function dataLabelsOpts(formatter, anchor, align, color) {
        if (!hasDataLabelsPlugin()) {
            return {};
        }
        return {
            plugins: {
                datalabels: {
                    color: color || '#333',
                    font: { weight: 'bold', size: 11 },
                    anchor: anchor || 'end',
                    align: align || 'top',
                    offset: 2,
                    formatter: formatter || function (value) {
                        if (value === null || value === undefined || (typeof value === 'number' && isNaN(value))) {
                            return '';
                        }
                        if (typeof value === 'number' && Math.abs(value - Math.round(value)) < 0.05) {
                            return String(Math.round(value));
                        }
                        return String(Number(value).toFixed(1));
                    },
                },
            },
        };
    }

    function formatCurrencyLabel(v) {
        if (v === null || v === undefined || isNaN(v)) {
            return '';
        }
        var n = Number(v);
        if (Math.abs(n) >= 1000) {
            return n.toLocaleString(undefined, { maximumFractionDigits: 0 });
        }
        return n.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function esc(s) {
        if (s === null || s === undefined) {
            return '';
        }
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function getMonthParams() {
        var fromEl = document.getElementById('month_from');
        var toEl = document.getElementById('month_to');
        return {
            month_from: fromEl && fromEl.value ? fromEl.value : '',
            month_to: toEl && toEl.value ? toEl.value : '',
        };
    }

    function urlWithMonths(baseUrl, includeMonths) {
        var u = new URL(baseUrl, window.location.href);
        if (includeMonths) {
            var q = getMonthParams();
            if (q.month_from) {
                u.searchParams.set('month_from', q.month_from);
            }
            if (q.month_to) {
                u.searchParams.set('month_to', q.month_to);
            }
        }
        return u.toString();
    }

    function fetchChartJson(url) {
        return fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).then(function (r) {
            if (!r.ok) {
                throw new Error('HTTP ' + r.status);
            }
            return r.json();
        });
    }

    function destroyCharts() {
        chartInstances.forEach(function (ch) {
            try {
                ch.destroy();
            } catch (e) { /* ignore */ }
        });
        chartInstances = [];
    }

    function hideSwmChartLoader(canvasId) {
        var wrap = document.querySelector('[data-swm-chart-loader="' + canvasId + '"]');
        if (wrap) {
            wrap.classList.remove('d-flex');
            wrap.classList.add('d-none');
            wrap.style.display = 'none';
            wrap.style.pointerEvents = 'none';
            wrap.setAttribute('aria-busy', 'false');
        }
    }

    function showAllSwmChartLoaders() {
        var nodes = document.querySelectorAll('[data-swm-chart-loader]');
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].classList.add('d-flex');
            nodes[i].classList.remove('d-none');
            nodes[i].style.display = 'flex';
            nodes[i].style.pointerEvents = 'auto';
            nodes[i].setAttribute('aria-busy', 'true');
        }
    }

    function hideAllSwmChartLoaders() {
        var nodes = document.querySelectorAll('[data-swm-chart-loader]');
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].classList.remove('d-flex');
            nodes[i].classList.add('d-none');
            nodes[i].style.display = 'none';
            nodes[i].style.pointerEvents = 'none';
            nodes[i].setAttribute('aria-busy', 'false');
        }
    }

    function buildIntegerCountTicks(maxVal) {
        maxVal = Math.max(0, Number(maxVal) || 0);
        var step = 1;
        if (maxVal > 15) {
            var rough = Math.max(1, Math.ceil(maxVal / 6));
            var pow10 = Math.pow(10, Math.floor(Math.log10(rough)));
            var m = rough / pow10;
            var n = m <= 1 ? 1 : m <= 2 ? 2 : m <= 5 ? 5 : 10;
            step = n * pow10;
        }
        var niceMax = Math.max(step, Math.ceil(maxVal / step) * step);
        var ticks = {
            beginAtZero: true,
            userCallback: function (label) {
                if (Math.floor(label) === label) {
                    return label;
                }
            },
        };
        if (maxVal > 0) {
            ticks.stepSize = step;
            ticks.max = niceMax;
        }
        return ticks;
    }

    /**
     * @param {object} axis — { x: label, y: label } (x = count axis when horizontalBar)
     * @param {boolean} horizontal — true for horizontalBar (value scale on X, categories on Y)
     * @param {number|null|undefined} maxCountVal — when set, Y (vertical) or X (horizontal) count axis uses integer steps
     */
    function defaultScaleOptions(axis, horizontal, maxCountVal) {
        axis = axis || {};
        horizontal = !!horizontal;
        var countTicks = maxCountVal != null && maxCountVal !== ''
            ? buildIntegerCountTicks(maxCountVal)
            : {
                beginAtZero: true,
                userCallback: function (label) {
                    if (Math.floor(label) === label) {
                        return label;
                    }
                },
            };
        var xAxes;
        var yAxes;
        if (horizontal) {
            xAxes = [{ ticks: countTicks }];
            yAxes = [{ ticks: { autoSkip: true } }];
        } else {
            xAxes = [{ ticks: { autoSkip: true } }];
            yAxes = [{ ticks: countTicks }];
        }
        if (axis.x) {
            xAxes[0].scaleLabel = { display: true, labelString: axis.x };
        }
        if (axis.y) {
            yAxes[0].scaleLabel = { display: true, labelString: axis.y };
        }
        return extendOptions({
            responsive: true,
            maintainAspectRatio: false,
            legend: { labels: { boxWidth: 10 } },
            scales: { xAxes: xAxes, yAxes: yAxes },
        }, fsmAnimation());
    }

    function indexedCategoryLegendOptions() {
        return {
            position: 'bottom',
            labels: {
                boxWidth: 12,
                generateLabels: function (chart) {
                    var ds = chart.data.datasets[0];
                    var bg = ds && ds.backgroundColor;
                    return (chart.data.labels || []).map(function (label, i) {
                        return {
                            text: label,
                            fillStyle: Array.isArray(bg) ? bg[i] : bg,
                            strokeStyle: '#ccc',
                            lineWidth: 0,
                            hidden: false,
                            index: i,
                            datasetIndex: 0,
                        };
                    });
                },
            },
        };
    }

    function makeSingleBar(canvasId, payload, horizontal) {
        var el = document.getElementById(canvasId);
        if (!el || !ChartCtor) {
            hideSwmChartLoader(canvasId);
            return;
        }
        var labels = payload.labels || [];
        var data = (payload.values || []).map(Number);
        var variedIds = ['swmChartHouseholdsByWard', 'swmChartComplaintsByWard', 'swmChartComplaintsByType', 'swmChartWorkersByType'];
        var useVaried = variedIds.indexOf(canvasId) >= 0;
        var ib = palette.indexedBar || palette.doughnut;
        var colors = useVaried
            ? data.map(function (_, i) {
                return ib[i % ib.length];
            })
            : data.map(function () {
                return palette.primary;
            });
        var hovers = useVaried
            ? colors.map(function (c) {
                return c;
            })
            : data.map(function () {
                return palette.primaryHover;
            });
        var type = horizontal ? 'horizontalBar' : 'bar';
        var scaleAxis = horizontal
            ? { x: cfg.axisCount || '', y: cfg.axisWard || '' }
            : { x: cfg.axisCategory || '', y: cfg.axisCount || '' };
        if (canvasId === 'swmChartHouseholdCoverageByWard') {
            scaleAxis = horizontal
                ? { x: cfg.axisPercent || '%', y: cfg.axisWard || '' }
                : { x: cfg.axisWard || '', y: cfg.axisPercent || '%' };
        }
        var maxCount = null;
        if (useVaried && canvasId !== 'swmChartHouseholdCoverageByWard') {
            maxCount = Math.max.apply(null, data.concat([0]));
        }
        var dlAnchor = horizontal ? 'center' : 'end';
        var dlAlign = horizontal ? 'center' : 'top';
        var dlColor = '#333';
        var baseScale = defaultScaleOptions(scaleAxis, horizontal, maxCount);
        if (useVaried) {
            baseScale = extendOptions(baseScale, { legend: indexedCategoryLegendOptions() });
        }
        var barOpts = extendOptions(
            baseScale,
            dataLabelsOpts(null, dlAnchor, dlAlign, dlColor)
        );
        var borders = useVaried
            ? colors.map(function () {
                return 'rgba(255, 255, 255, 0.35)';
            })
            : 'rgba(54, 162, 235, 1)';
        var ch = new ChartCtor(el.getContext('2d'), {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: useVaried ? '' : (cfg.chartMeta && cfg.chartMeta[canvasId] ? cfg.chartMeta[canvasId] : ''),
                    backgroundColor: colors,
                    hoverBackgroundColor: hovers,
                    borderColor: borders,
                    borderWidth: 1,
                    data: data,
                }],
            },
            options: barOpts,
        });
        chartInstances.push(ch);
        hideSwmChartLoader(canvasId);
    }

    function makeLineOrMultiBar(canvasId, payload, asLine) {
        var el = document.getElementById(canvasId);
        if (!el || !ChartCtor) {
            hideSwmChartLoader(canvasId);
            return;
        }
        var labels = payload.labels || [];
        var datasets = (payload.datasets || []).map(function (ds, idx) {
            return {
                label: ds.label || '',
                data: (ds.data || []).map(Number),
                backgroundColor: asLine
                    ? (idx === 0 ? palette.lineBlueFill : palette.lineGreenFill)
                    : (idx === 0 ? palette.targetOrange : palette.achievementGreen),
                borderColor: asLine ? (idx === 0 ? palette.lineOrange : palette.lineGreen) : undefined,
                borderWidth: asLine ? 2 : 0,
                fill: asLine ? false : true,
                pointBackgroundColor: asLine ? (idx === 0 ? palette.lineOrange : palette.lineGreen) : undefined,
                pointRadius: asLine ? 3 : undefined,
            };
        });
        var lineOpts = extendOptions(
            defaultScaleOptions({ x: cfg.axisMonth, y: cfg.axisAmount }, false, null),
            dataLabelsOpts(
                canvasId === 'swmChartBillingByMonth' ? formatCurrencyLabel : null,
                'end',
                'top',
                '#333'
            )
        );
        var ch = new ChartCtor(el.getContext('2d'), {
            type: asLine ? 'line' : 'bar',
            data: { labels: labels, datasets: datasets },
            options: lineOpts,
        });
        chartInstances.push(ch);
        hideSwmChartLoader(canvasId);
    }

    /** Stacked vertical bars: households (bottom) + van pullers (top), legend at bottom. */
    function makeStackedHouseholdsVanPullers(canvasId, payload) {
        var el = document.getElementById(canvasId);
        if (!el || !ChartCtor) {
            hideSwmChartLoader(canvasId);
            return;
        }
        var labels = payload.labels || [];
        var raw = payload.datasets || [];
        var maxStack = 0;
        var i;
        var j;
        for (i = 0; i < labels.length; i++) {
            var sum = 0;
            for (j = 0; j < raw.length; j++) {
                sum += Number((raw[j].data || [])[i] || 0);
            }
            if (sum > maxStack) {
                maxStack = sum;
            }
        }
        var countTicks = buildIntegerCountTicks(maxStack);
        var datasets = raw.map(function (ds, idx) {
            return {
                label: ds.label || '',
                data: (ds.data || []).map(Number),
                stack: 'ward',
                backgroundColor: idx === 0 ? palette.householdsStack : palette.vanPullersStack,
                hoverBackgroundColor: idx === 0 ? palette.householdsStackHover : palette.vanPullersStackHover,
                borderWidth: 1,
                borderColor: 'rgba(255, 255, 255, 0.4)',
            };
        });
        var grpOpts = extendOptions({
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom',
                labels: { boxWidth: 12 },
            },
            tooltips: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                xAxes: [{
                    stacked: true,
                    ticks: { autoSkip: true },
                    scaleLabel: { display: true, labelString: cfg.axisWard || '' },
                }],
                yAxes: [{
                    stacked: true,
                    ticks: countTicks,
                    scaleLabel: { display: true, labelString: cfg.axisCount || '' },
                }],
            },
        }, extendOptions(fsmAnimation(), dataLabelsOpts(null, 'end', 'top', '#333')));
        var ch = new ChartCtor(el.getContext('2d'), {
            type: 'bar',
            data: { labels: labels, datasets: datasets },
            options: grpOpts,
        });
        chartInstances.push(ch);
        hideSwmChartLoader(canvasId);
    }

    /** Full pie (FSM containTypeChart style), not doughnut. */
    function makePie(canvasId, payload) {
        var el = document.getElementById(canvasId);
        if (!el || !ChartCtor) {
            hideSwmChartLoader(canvasId);
            return;
        }
        var labels = payload.labels || [];
        var data = (payload.values || []).map(Number);
        var colors = palette.fsmPie || palette.doughnut;
        var bg = labels.map(function (_, i) {
            return colors[i % colors.length];
        });
        var pieOpts = extendOptions({
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: true,
                position: 'right',
                labels: { boxWidth: 10 },
            },
        }, extendOptions(fsmAnimation(), dataLabelsOpts(function (value, ctx) {
            var arr = ctx.dataset.data || [];
            var sum = arr.reduce(function (a, b) { return a + Number(b); }, 0);
            if (!sum) {
                return '';
            }
            var pct = Math.round((Number(value) / sum) * 100);
            return Math.round(value) + (pct > 0 ? '\n(' + pct + '%)' : '');
        }, 'center', 'center', '#fff')));
        var ch = new ChartCtor(el.getContext('2d'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: bg,
                    hoverBackgroundColor: bg,
                    borderWidth: 1,
                    borderColor: 'rgba(255, 255, 255, 0.35)',
                }],
            },
            options: pieOpts,
        });
        chartInstances.push(ch);
        hideSwmChartLoader(canvasId);
    }

    function bindExportButton(btnId, canvasId) {
        var btn = document.getElementById(btnId);
        var canvas = document.getElementById(canvasId);
        if (!btn || !canvas) {
            return;
        }
        btn.onclick = function () {
            var a = document.createElement('a');
            a.href = canvas.toDataURL('image/png', 1.0);
            a.download = (cfg.chartMeta && cfg.chartMeta[canvasId]) ? (cfg.chartMeta[canvasId] + '.png') : 'chart.png';
            a.click();
        };
    }

    window.refreshSwmDashboardCharts = function () {
        ensureDataLabelsRegistered();
        if (!cfg.urls) {
            return;
        }
        destroyCharts();
        showAllSwmChartLoaders();
        var u = cfg.urls;

        Promise.allSettled([
            fetchChartJson(urlWithMonths(u.billingByMonth, true)),
            fetchChartJson(urlWithMonths(u.complaintsByType, true)),
            fetchChartJson(urlWithMonths(u.complaintsByWard, true)),
            fetchChartJson(u.workersByType),
            fetchChartJson(u.vehiclesByType),
            fetchChartJson(u.householdsByWard),
            fetchChartJson(u.householdCoverageByWard),
            fetchChartJson(u.householdsVsVanPullersByWard),
        ]).then(function (results) {
            var valueOf = function (idx, fallback) {
                return (results[idx] && results[idx].status === 'fulfilled') ? results[idx].value : fallback;
            };

            makeLineOrMultiBar('swmChartBillingByMonth', valueOf(0, { labels: [], datasets: [] }), true);
            makeSingleBar('swmChartComplaintsByType', valueOf(1, { labels: [], values: [] }), false);
            makeSingleBar('swmChartComplaintsByWard', valueOf(2, { labels: [], values: [] }), true);
            makeSingleBar('swmChartWorkersByType', valueOf(3, { labels: [], values: [] }), false);
            makePie('swmChartVehiclesByType', valueOf(4, { labels: [], values: [] }));
            makeSingleBar('swmChartHouseholdsByWard', valueOf(5, { labels: [], values: [] }), true);
            makeSingleBar('swmChartHouseholdCoverageByWard', valueOf(6, { labels: [], values: [] }), false);
            makeStackedHouseholdsVanPullers('swmChartHouseholdsVsVanPullers', valueOf(7, { labels: [], datasets: [] }));

            var failed = results.filter(function (r) { return !r || r.status !== 'fulfilled'; }).length;
            if (failed > 0) {
                console.warn('SWM dashboard charts: ' + failed + ' endpoint(s) failed, showing partial charts.');
            }

            [
                ['swmExportBillingByMonth', 'swmChartBillingByMonth'],
                ['swmExportComplaintsByType', 'swmChartComplaintsByType'],
                ['swmExportComplaintsByWard', 'swmChartComplaintsByWard'],
                ['swmExportWorkersByType', 'swmChartWorkersByType'],
                ['swmExportVehiclesByType', 'swmChartVehiclesByType'],
                ['swmExportHouseholdsByWard', 'swmChartHouseholdsByWard'],
                ['swmExportHouseholdCoverageByWard', 'swmChartHouseholdCoverageByWard'],
                ['swmExportHouseholdsVsVanPullers', 'swmChartHouseholdsVsVanPullers'],
            ].forEach(function (pair) {
                bindExportButton(pair[0], pair[1]);
            });
        }).catch(function (err) {
            console.error('SWM dashboard charts:', err);
            hideAllSwmChartLoaders();
            if (typeof window.Swal !== 'undefined') {
                window.Swal.fire({ icon: 'error', title: cfg.errorTitle || 'Error', text: cfg.chartsFailedMsg || 'Charts could not be loaded.' });
            }
        });
    };

    function formatCell(v) {
        if (v !== null && typeof v === 'object') {
            return esc(JSON.stringify(v));
        }
        return esc(v);
    }

    function buildKeyValueTable(rows, noDataLabel) {
        var keys = Object.keys(rows || {});
        if (!keys.length) {
            return '<tr><td colspan="2">' + esc(noDataLabel) + '</td></tr>';
        }
        return keys.map(function (label) {
            return '<tr><td>' + esc(label) + '</td><td>' + formatCell(rows[label]) + '</td></tr>';
        }).join('');
    }

    function buildWardTable(rows, wardCols, naLabel, noDataLabel) {
        if (!rows || !rows.length) {
            return '<tr><td colspan="5">' + esc(noDataLabel) + '</td></tr>';
        }
        return rows.map(function (row) {
            return '<tr>'
                + '<td>' + esc(row.ward_no) + '</td>'
                + '<td>' + esc(row.total_households) + '</td>'
                + '<td>' + (row.waste_vans === null || row.waste_vans === undefined || row.waste_vans === '' ? esc(naLabel) : esc(row.waste_vans)) + '</td>'
                + '<td>' + esc(row.covered_households) + '</td>'
                + '<td>' + esc(row.coverage_percent) + '</td>'
                + '</tr>';
        }).join('');
    }

    function buildVehicleTable(rows, vehicleCols, noDataLabel) {
        if (!rows || !rows.length) {
            return '<tr><td colspan="3">' + esc(noDataLabel) + '</td></tr>';
        }
        return rows.map(function (row) {
            return '<tr>'
                + '<td>' + esc(row.vehicle_type) + '</td>'
                + '<td>' + esc(row.total_count) + '</td>'
                + '<td>' + esc(row.remarks) + '</td>'
                + '</tr>';
        }).join('');
    }

    function renderDashboard(d) {
        var sectionKeys = [
            'existing_kpis',
            'coverage',
            'billing',
            'complaints',
            'service_providers',
            'service_facilities',
            'city_statistics',
        ];
        var html = '';
        var sectionTitles = cfg.sectionTitles || [];
        var indicatorLabel = cfg.indicatorLabel || 'Indicator';
        var valueLabel = cfg.valueLabel || 'Value';
        var noDataLabel = cfg.noDataLabel || '';
        var searchPh = cfg.searchPlaceholder || '';

        for (var i = 0; i < sectionKeys.length; i++) {
            html += '<div class="card swm-kpi-section-card">'
                + '<div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">'
                + '<strong>' + esc(sectionTitles[i]) + '</strong>'
                + '<input type="search" class="form-control form-control-sm swm-kpi-search" style="max-width:220px" placeholder="' + esc(searchPh) + '" autocomplete="off">'
                + '</div>'
                + '<div class="card-body p-0"><div style="overflow:auto">'
                + '<table class="table table-bordered mb-0 swm-kpi-table">'
                + '<thead><tr><th style="width:70%">' + esc(indicatorLabel) + '</th><th>' + esc(valueLabel) + '</th></tr></thead>'
                + '<tbody>' + buildKeyValueTable(d[sectionKeys[i]] || {}, noDataLabel) + '</tbody>'
                + '</table></div></div></div>';
        }

        var wardTitle = cfg.wardTitle || '';
        var wardCols = cfg.wardCols || [];
        var vehicleTitle = cfg.vehicleTitle || '';
        var vehicleCols = cfg.vehicleCols || [];
        var naLabel = cfg.naLabel || '';

        html += '<div class="card swm-kpi-section-card">'
            + '<div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">'
            + '<strong>' + esc(wardTitle) + '</strong>'
            + '<input type="search" class="form-control form-control-sm swm-kpi-search" style="max-width:220px" placeholder="' + esc(searchPh) + '" autocomplete="off">'
            + '</div>'
            + '<div class="card-body p-0"><div style="overflow:auto">'
            + '<table class="table table-bordered mb-0 swm-kpi-table">'
            + '<thead><tr>'
            + '<th>' + esc(wardCols[0]) + '</th><th>' + esc(wardCols[1]) + '</th><th>' + esc(wardCols[2]) + '</th>'
            + '<th>' + esc(wardCols[3]) + '</th><th>' + esc(wardCols[4]) + '</th>'
            + '</tr></thead>'
            + '<tbody>' + buildWardTable(d.ward_statistics || [], wardCols, naLabel, noDataLabel) + '</tbody>'
            + '</table></div></div></div>';

        html += '<div class="card swm-kpi-section-card">'
            + '<div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">'
            + '<strong>' + esc(vehicleTitle) + '</strong>'
            + '<input type="search" class="form-control form-control-sm swm-kpi-search" style="max-width:220px" placeholder="' + esc(searchPh) + '" autocomplete="off">'
            + '</div>'
            + '<div class="card-body p-0"><div style="overflow:auto">'
            + '<table class="table table-bordered mb-0 swm-kpi-table">'
            + '<thead><tr>'
            + '<th>' + esc(vehicleCols[0]) + '</th><th>' + esc(vehicleCols[1]) + '</th><th>' + esc(vehicleCols[2]) + '</th>'
            + '</tr></thead>'
            + '<tbody>' + buildVehicleTable(d.vehicle_statistics || [], vehicleCols, noDataLabel) + '</tbody>'
            + '</table></div></div></div>';

        var body = document.getElementById('swm-kpi-dashboard-body');
        if (body) {
            body.innerHTML = html;
        }
    }

    var dataLabelsRegistered = false;

    function ensureDataLabelsRegistered() {
        if (dataLabelsRegistered || !hasDataLabelsPlugin()) {
            return;
        }
        try {
            ChartCtor.plugins.register(window.ChartDataLabels);
            dataLabelsRegistered = true;
        } catch (err) {
            console.warn('SWM dashboard: chartjs-plugin-datalabels register failed', err);
        }
    }

    function formatCountDisplay(v) {
        if (v === null || v === undefined || v === '') {
            return '0';
        }
        var n = Number(String(v).replace(/,/g, ''));
        if (!isNaN(n)) {
            return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
        return String(v);
    }

    function updateSwmCountBoxes(d) {
        if (!d) {
            return;
        }
        var cs = d.city_statistics || {};
        var cov = d.coverage || {};
        var sp = d.service_providers || {};
        var cp = d.complaints || {};
        var map = {
            'swm-count-households': cs['Total Households'],
            'swm-count-covered': cov['Covered households'],
            'swm-count-workers': sp['Total number of workers'],
            'swm-count-vehicles': sp['Total vehicles'],
            'swm-count-providers': sp['Total number of service providers'],
            'swm-count-complaints': cp['Total complaints received'],
        };
        Object.keys(map).forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.textContent = formatCountDisplay(map[id]);
            }
        });
    }

    function bindKpiTableSearch() {
        var root = document.getElementById('swm-kpi-dashboard-body');
        if (!root) {
            return;
        }
        root.addEventListener('input', function (e) {
            var t = e.target;
            if (!t.classList || !t.classList.contains('swm-kpi-search')) {
                return;
            }
            var card = t.closest('.swm-kpi-section-card');
            if (!card) {
                return;
            }
            var q = (t.value || '').toLowerCase().trim();
            var tbody = card.querySelector('tbody');
            if (!tbody) {
                return;
            }
            var rows = tbody.querySelectorAll('tr');
            for (var i = 0; i < rows.length; i++) {
                var tr = rows[i];
                var text = tr.textContent || '';
                tr.style.display = !q || text.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
            }
        });
    }

    function dashboardFetchUrl(monthFrom, monthTo) {
        var u = new URL(cfg.kpiIndexUrl, window.location.href);
        if (monthFrom) {
            u.searchParams.set('month_from', monthFrom);
        }
        if (monthTo) {
            u.searchParams.set('month_to', monthTo);
        }
        return u.toString();
    }

    function dashboardHistoryUrl(monthFrom, monthTo) {
        var u = new URL(cfg.kpiIndexUrl, window.location.href);
        if (monthFrom) {
            u.searchParams.set('month_from', monthFrom);
        }
        if (monthTo) {
            u.searchParams.set('month_to', monthTo);
        }
        return u.pathname + u.search;
    }

    function init() {
        ensureDataLabelsRegistered();
        bindKpiTableSearch();

        var $ = window.jQuery;
        if ($) {
            $('#swm-dashboard-filter-form').on('submit', function (e) {
                e.preventDefault();
                var monthFrom = $('#month_from').val();
                var monthTo = $('#month_to').val();
                if (typeof window.displayAjaxLoader === 'function') {
                    window.displayAjaxLoader(cfg.loaderMsg || '');
                }
                fetch(dashboardFetchUrl(monthFrom, monthTo), {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                    .then(function (r) {
                        if (!r.ok) {
                            throw new Error('HTTP ' + r.status);
                        }
                        return r.json();
                    })
                    .then(function (payload) {
                        if (!payload.dashboard) {
                            throw new Error('Invalid response');
                        }
                        renderDashboard(payload.dashboard);
                        updateSwmCountBoxes(payload.dashboard);
                        if (window.history && window.history.replaceState) {
                            window.history.replaceState(null, '', dashboardHistoryUrl(monthFrom, monthTo));
                        }
                        window.refreshSwmDashboardCharts();
                    })
                    .catch(function (err) {
                        console.error(err);
                        if (typeof window.Swal !== 'undefined') {
                            window.Swal.fire({ icon: 'error', title: cfg.errorTitle || 'Error', text: cfg.loadFailedMsg || '' });
                        } else {
                            alert(cfg.loadFailedMsg || '');
                        }
                    })
                    .finally(function () {
                        if (typeof window.removeAjaxLoader === 'function') {
                            window.removeAjaxLoader();
                        }
                    });
            });
        }

        window.refreshSwmDashboardCharts();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
