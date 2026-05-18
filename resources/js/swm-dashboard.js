/**
 * SWM Dashboard: module accordions, Chart.js charts, ward grid heatmaps, networks, To-month refresh.
 */
(function () {
    'use strict';

    var cfg = window.swmDashboardConfig || {};
    var ChartCtor = typeof window.Chart === 'function' ? window.Chart : null;
    var chartInstances = {};
    var networkInstances = {};

    var palette = {
        bar: 'rgba(54, 162, 235, 0.75)',
        barHover: 'rgba(54, 162, 235, 0.9)',
        line: 'rgba(54, 162, 235, 1)',
        lineFill: 'rgba(54, 162, 235, 0.15)',
        doughnut: [
            'rgba(54, 162, 235, 0.65)',
            'rgba(251, 176, 64, 0.85)',
            'rgba(153, 202, 60, 0.85)',
            'rgba(90, 155, 212, 0.65)',
            'rgba(255, 99, 132, 0.65)',
            'rgba(153, 102, 255, 0.65)',
        ],
    };

    var networkGroupColors = {
        landfill: { background: '#1f3a52', border: '#117a8b' },
        sts: { background: '#17a2b8', border: '#138496' },
        ward: { background: '#6c757d', border: '#5a6268' },
    };

    function initModuleAccordions() {
        document.querySelectorAll('.swm-module-toggle').forEach(function (el) {
            el.addEventListener('click', function () {
                var section = el.closest('.dash-section');
                if (!section) {
                    return;
                }
                section.classList.toggle('collapsed');
                if (!section.classList.contains('collapsed')) {
                    initCharts();
                    initHeatmaps();
                    initNetworks();
                }
            });
        });
    }

    function parseChartValue(v, decimalValues) {
        if (decimalValues) {
            var f = parseFloat(v);
            return isNaN(f) ? 0 : f;
        }
        return parseInt(v, 10) || 0;
    }

    function mapChartData(data, decimalValues) {
        return (data || []).map(function (v) {
            return parseChartValue(v, decimalValues);
        });
    }

    function scaleOptions(unitX, unitY, opts) {
        opts = opts || {};
        var scales = {};
        if (unitX) {
            var xTicks = { beginAtZero: true };
            if (opts.integerXTicks) {
                xTicks.precision = 0;
                xTicks.stepSize = 1;
            }
            scales.xAxes = [{
                scaleLabel: { display: true, labelString: unitX },
                ticks: xTicks,
            }];
        }
        var yTicks = { beginAtZero: true };
        if (opts.percentYAxis) {
            yTicks.max = 100;
            yTicks.suggestedMax = 100;
        } else if (opts.integerYTicks || !unitY) {
            yTicks.precision = 0;
            yTicks.stepSize = 1;
        }
        var yAxis = { ticks: yTicks };
        if (unitY) {
            yAxis.scaleLabel = { display: true, labelString: unitY };
        }
        scales.yAxes = [yAxis];
        return scales;
    }

    function destroyChart(id) {
        if (chartInstances[id]) {
            chartInstances[id].destroy();
            delete chartInstances[id];
        }
    }

    function renderBar(canvas, chart) {
        var opts = chart.options || {};
        var ds = (chart.datasets && chart.datasets[0]) ? chart.datasets[0] : { data: [] };
        destroyChart(canvas.id);
        chartInstances[canvas.id] = new ChartCtor(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: chart.labels || [],
                datasets: [{
                    label: ds.label || '',
                    data: mapChartData(ds.data, opts.decimalValues),
                    backgroundColor: palette.bar,
                    hoverBackgroundColor: palette.barHover,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: scaleOptions(opts.unitX, opts.unitY, opts),
            },
        });
    }

    function stackedBarColors(count) {
        var colors = palette.doughnut;
        var result = [];
        for (var i = 0; i < count; i++) {
            result.push(colors[i % colors.length]);
        }
        return result;
    }

    function renderStackedBar(canvas, chart) {
        var opts = chart.options || {};
        var datasets = chart.datasets || [];
        var colors = stackedBarColors(datasets.length);
        var chartDatasets = datasets.map(function (ds, i) {
            return {
                label: ds.label || '',
                data: mapChartData(ds.data, opts.decimalValues),
                backgroundColor: colors[i],
                hoverBackgroundColor: colors[i],
            };
        });
        var scales = scaleOptions(opts.unitX, opts.unitY, opts);
        if (opts.stacked) {
            if (scales.xAxes && scales.xAxes[0]) {
                scales.xAxes[0].stacked = true;
            }
            if (scales.yAxes && scales.yAxes[0]) {
                scales.yAxes[0].stacked = true;
            }
        }
        destroyChart(canvas.id);
        chartInstances[canvas.id] = new ChartCtor(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: chart.labels || [],
                datasets: chartDatasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom' },
                scales: scales,
            },
        });
    }

    function renderLine(canvas, chart) {
        var opts = chart.options || {};
        var ds = (chart.datasets && chart.datasets[0]) ? chart.datasets[0] : { data: [] };
        destroyChart(canvas.id);
        chartInstances[canvas.id] = new ChartCtor(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: chart.labels || [],
                datasets: [{
                    label: ds.label || '',
                    data: mapChartData(ds.data, opts.decimalValues),
                    borderColor: palette.line,
                    backgroundColor: palette.lineFill,
                    fill: false,
                    lineTension: 0.2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: scaleOptions(opts.unitX, opts.unitY, opts),
            },
        });
    }

    function renderHorizontalBar(canvas, chart) {
        var opts = chart.options || {};
        var ds = (chart.datasets && chart.datasets[0]) ? chart.datasets[0] : { data: [] };
        var scales = scaleOptions(opts.unitX, opts.unitY, opts);
        destroyChart(canvas.id);
        chartInstances[canvas.id] = new ChartCtor(canvas.getContext('2d'), {
            type: 'horizontalBar',
            data: {
                labels: chart.labels || [],
                datasets: [{
                    label: ds.label || '',
                    data: mapChartData(ds.data, opts.decimalValues),
                    backgroundColor: palette.bar,
                    hoverBackgroundColor: palette.barHover,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: scales,
            },
        });
    }

    function renderStackedArea(canvas, chart) {
        var opts = chart.options || {};
        var datasets = chart.datasets || [];
        var colors = stackedBarColors(datasets.length);
        var chartDatasets = datasets.map(function (ds, i) {
            return {
                label: ds.label || '',
                data: mapChartData(ds.data, opts.decimalValues),
                borderColor: colors[i],
                backgroundColor: colors[i].replace('0.85', '0.45').replace('0.65', '0.35'),
                fill: true,
                lineTension: 0.2,
                pointRadius: 2,
            };
        });
        var scales = scaleOptions(opts.unitX, opts.unitY, opts);
        if (scales.yAxes && scales.yAxes[0]) {
            scales.yAxes[0].stacked = true;
        }
        destroyChart(canvas.id);
        chartInstances[canvas.id] = new ChartCtor(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: chart.labels || [],
                datasets: chartDatasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom' },
                scales: scales,
            },
        });
    }

    function renderDoughnut(canvas, chart) {
        var ds = (chart.datasets && chart.datasets[0]) ? chart.datasets[0] : { data: [] };
        var colors = palette.doughnut;
        destroyChart(canvas.id);
        chartInstances[canvas.id] = new ChartCtor(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: chart.labels || [],
                datasets: [{
                    data: ds.data || [],
                    backgroundColor: (chart.labels || []).map(function (_, i) {
                        return colors[i % colors.length];
                    }),
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom' },
            },
        });
    }

    function initCharts() {
        if (!ChartCtor) {
            console.warn('SWM Dashboard: Chart.js is not loaded; charts were skipped.');
            return;
        }
        document.querySelectorAll('.swm-chart-canvas').forEach(function (canvas) {
            var raw = canvas.getAttribute('data-chart');
            if (!raw) {
                return;
            }
            var chart;
            try {
                chart = JSON.parse(raw);
            } catch (e) {
                return;
            }
            if (chart.type === 'doughnut') {
                renderDoughnut(canvas, chart);
            } else if (chart.type === 'stackedBar') {
                renderStackedBar(canvas, chart);
            } else if (chart.type === 'line') {
                renderLine(canvas, chart);
            } else if (chart.type === 'horizontalBar') {
                renderHorizontalBar(canvas, chart);
            } else if (chart.type === 'stackedArea') {
                renderStackedArea(canvas, chart);
            } else {
                renderBar(canvas, chart);
            }
        });
    }

    function formatWardLabel(ward) {
        var n = parseInt(String(ward), 10);
        if (!isNaN(n)) {
            return 'Ward ' + (n < 10 ? '0' + n : String(n));
        }
        return 'Ward ' + ward;
    }

    function heatmapColor(pct) {
        var t = Math.max(0, Math.min(100, pct)) / 100;
        var r = Math.round(212 + (31 - 212) * t);
        var g = Math.round(238 + (58 - 238) * t);
        var b = Math.round(245 + (82 - 245) * t);
        return 'rgb(' + r + ',' + g + ',' + b + ')';
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderWardHeatmapGrid(el, chart) {
        var wards = chart.wards || [];
        var multiRows = chart.heatmapRows;
        var opts = chart.options || {};
        var valueDisplay = opts.valueDisplay || (multiRows && multiRows.length ? 'count' : 'percent');
        var colCount = Math.max(wards.length, 1);
        var multiRowClass = multiRows && multiRows.length > 1 ? ' swm-heatmap-table--multi-row' : '';
        var html = '<div class="swm-heatmap-table' + multiRowClass + '" style="--heatmap-cols:' + colCount + '">';

        html += '<div class="heatmap-ward-headers">';
        html += '<div class="heatmap-corner" aria-hidden="true"></div>';
        wards.forEach(function (ward) {
            html += '<div class="heatmap-ward-label">' + escapeHtml(formatWardLabel(ward)) + '</div>';
        });
        html += '</div>';

        var globalMax = 0;
        if (multiRows && multiRows.length) {
            multiRows.forEach(function (r) {
                (r.values || []).forEach(function (v) {
                    var n = Number(v);
                    if (!isNaN(n) && n > globalMax) {
                        globalMax = n;
                    }
                });
            });
        } else {
            (chart.values || []).forEach(function (v) {
                var n = Number(v);
                if (!isNaN(n) && n > globalMax) {
                    globalMax = n;
                }
            });
        }
        if (valueDisplay === 'count') {
            globalMax = Math.max(globalMax, 1);
        } else {
            globalMax = Math.max(globalMax, 100);
        }

        function cellColor(v) {
            var num = Number(v);
            if (isNaN(num)) {
                num = 0;
            }
            if (valueDisplay === 'percent') {
                return heatmapColor(num);
            }
            var intensity = globalMax > 0 ? (num / globalMax) * 100 : 0;
            return heatmapColor(intensity);
        }

        function cellText(v) {
            var num = Number(v);
            if (isNaN(num)) {
                num = 0;
            }
            if (valueDisplay === 'count') {
                return String(num);
            }
            return String(num) + '%';
        }

        function renderOneRow(rowLabel, rowValues) {
            html += '<div class="heatmap-data-row">';
            html += '<div class="heatmap-row-label">' + escapeHtml(rowLabel) + '</div>';
            wards.forEach(function (ward, i) {
                var v = rowValues[i] != null ? rowValues[i] : 0;
                html += '<div class="heatmap-value-cell" style="background-color:' + cellColor(v) + '">';
                html += '<span>' + escapeHtml(cellText(v)) + '</span></div>';
            });
            html += '</div>';
        }

        if (multiRows && multiRows.length) {
            multiRows.forEach(function (r) {
                renderOneRow(r.rowLabel || '', r.values || []);
            });
        } else {
            renderOneRow(chart.rowLabel || 'Segregation Rate', chart.values || []);
        }

        html += '</div>';

        html += '<div class="heatmap-scale-legend">';
        html += '<span class="heatmap-scale-low">Low</span>';
        html += '<span class="heatmap-scale-bar" aria-hidden="true"></span>';
        html += '<span class="heatmap-scale-high">High</span></div>';

        el.innerHTML = html;
    }

    function initHeatmaps() {
        document.querySelectorAll('.swm-heatmap').forEach(function (el) {
            var raw = el.getAttribute('data-heatmap');
            if (!raw) {
                return;
            }
            var chart;
            try {
                chart = JSON.parse(raw);
            } catch (e) {
                return;
            }
            renderWardHeatmapGrid(el, chart);
        });
    }

    function networkNodeColor(group) {
        var style = networkGroupColors[group] || networkGroupColors.ward;
        return {
            background: style.background,
            border: style.border,
            highlight: {
                background: style.background,
                border: style.border,
            },
            hover: {
                background: style.background,
                border: style.border,
            },
        };
    }

    function networkNodeFont() {
        return {
            color: '#ffffff',
            size: 13,
            face: 'Tahoma, Verdana, sans-serif',
            strokeWidth: 2,
            strokeColor: '#1f3a52',
        };
    }

    function networkGroupsOptions() {
        var groups = {};
        Object.keys(networkGroupColors).forEach(function (group) {
            groups[group] = {
                color: networkNodeColor(group),
                font: networkNodeFont(),
                shape: group === 'landfill' ? 'box' : 'ellipse',
                margin: 10,
            };
        });
        return groups;
    }

    function renderNetwork(el, chart) {
        var id = chart.id || el.id;
        if (!id) {
            return;
        }
        if (networkInstances[id]) {
            networkInstances[id].destroy();
            delete networkInstances[id];
        }
        if (typeof window.vis === 'undefined' || !window.vis.Network) {
            el.innerHTML = '<p class="text-muted mb-0">Network chart library is not loaded.</p>';
            return;
        }
        var nodeFont = networkNodeFont();
        var nodes = new window.vis.DataSet((chart.nodes || []).map(function (n) {
            var group = n.group || 'ward';
            return {
                id: n.id,
                label: n.label,
                group: group,
                color: networkNodeColor(group),
                font: nodeFont,
                shape: group === 'landfill' ? 'box' : 'ellipse',
                margin: 10,
            };
        }));
        var edges = new window.vis.DataSet(chart.edges || []);
        networkInstances[id] = new window.vis.Network(el, { nodes: nodes, edges: edges }, {
            groups: networkGroupsOptions(),
            layout: {
                hierarchical: {
                    enabled: true,
                    direction: 'UD',
                    sortMethod: 'directed',
                },
                improvedLayout: true,
            },
            physics: { enabled: false },
            edges: {
                arrows: { to: { enabled: true, scaleFactor: 0.6 } },
                color: { color: '#adb5bd' },
                smooth: { type: 'cubicBezier' },
                font: {
                    color: '#495057',
                    size: 11,
                    strokeWidth: 2,
                    strokeColor: '#ffffff',
                },
            },
            nodes: {
                font: nodeFont,
                borderWidth: 2,
                shadow: false,
            },
            interaction: { hover: true, zoomView: true, dragView: true },
        });
    }

    function initNetworks() {
        document.querySelectorAll('.swm-network').forEach(function (el) {
            var raw = el.getAttribute('data-network');
            if (!raw) {
                return;
            }
            var chart;
            try {
                chart = JSON.parse(raw);
            } catch (e) {
                return;
            }
            renderNetwork(el, chart);
        });
    }

    function initExportButtons() {
        document.querySelectorAll('.swm-export-chart').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-target');
                var ch = chartInstances[id];
                if (!ch || !ch.canvas) {
                    return;
                }
                var link = document.createElement('a');
                link.download = (id || 'chart') + '.png';
                link.href = ch.canvas.toDataURL('image/png');
                link.click();
            });
        });
    }

    function refreshDashboard(toMonth) {
        var url = new URL(cfg.dataUrl, window.location.href);
        if (toMonth) {
            url.searchParams.set('to_month', toMonth);
        }
        if (typeof window.displayAjaxLoader === 'function') {
            window.displayAjaxLoader('');
        }
        return fetch(url.toString(), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) {
                if (!r.ok) {
                    throw new Error('HTTP ' + r.status);
                }
                return r.json();
            })
            .then(function () {
                window.location.href = url.pathname + '?to_month=' + encodeURIComponent(toMonth || '');
            })
            .catch(function (err) {
                console.error(err);
                if (typeof window.Swal !== 'undefined') {
                    window.Swal.fire({ icon: 'error', title: 'Error', text: 'Could not refresh dashboard.' });
                }
            })
            .finally(function () {
                if (typeof window.removeAjaxLoader === 'function') {
                    window.removeAjaxLoader();
                }
            });
    }

    function clampToMaxMonth(input) {
        if (!input || !input.value) {
            return '';
        }
        var maxMonth = input.max || (cfg.period && cfg.period.max_to_month) || '';
        if (maxMonth && input.value > maxMonth) {
            input.value = maxMonth;
        }
        return input.value;
    }

    function bindFilterForm() {
        var form = document.getElementById('swm-dashboard-filter-form');
        if (!form) {
            return;
        }
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var input = document.getElementById('to_month');
            var toMonth = clampToMaxMonth(input);
            refreshDashboard(toMonth);
        });
    }

    function init() {
        initModuleAccordions();
        initCharts();
        initHeatmaps();
        initNetworks();
        initExportButtons();
        bindFilterForm();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
