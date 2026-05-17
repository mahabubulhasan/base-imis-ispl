/**
 * SWM Dashboard: module accordions, Chart.js charts, ward grid heatmaps, To-month refresh.
 */
(function () {
    'use strict';

    var cfg = window.swmDashboardConfig || {};
    var ChartCtor = typeof window.Chart === 'function' ? window.Chart : null;
    var chartInstances = {};

    var palette = {
        bar: 'rgba(54, 162, 235, 0.75)',
        barHover: 'rgba(54, 162, 235, 0.9)',
        doughnut: [
            'rgba(54, 162, 235, 0.65)',
            'rgba(251, 176, 64, 0.85)',
            'rgba(153, 202, 60, 0.85)',
            'rgba(90, 155, 212, 0.65)',
            'rgba(255, 99, 132, 0.65)',
            'rgba(153, 102, 255, 0.65)',
        ],
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
                }
            });
        });
    }

    function scaleOptions(unitX, unitY) {
        var scales = {};
        if (unitX) {
            scales.xAxes = [{ scaleLabel: { display: true, labelString: unitX } }];
        }
        if (unitY) {
            scales.yAxes = [{ scaleLabel: { display: true, labelString: unitY }, ticks: { beginAtZero: true } }];
        }
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
                    data: ds.data || [],
                    backgroundColor: palette.bar,
                    hoverBackgroundColor: palette.barHover,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: scaleOptions(opts.unitX, opts.unitY),
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
        var values = chart.values || [];
        var rowLabel = chart.rowLabel || 'Segregation Rate';
        var colCount = Math.max(wards.length, 1);
        var html = '<div class="swm-heatmap-table" style="--heatmap-cols:' + colCount + '">';

        html += '<div class="heatmap-ward-headers">';
        html += '<div class="heatmap-corner" aria-hidden="true"></div>';
        wards.forEach(function (ward) {
            html += '<div class="heatmap-ward-label">' + escapeHtml(formatWardLabel(ward)) + '</div>';
        });
        html += '</div>';

        html += '<div class="heatmap-data-row">';
        html += '<div class="heatmap-row-label">' + escapeHtml(rowLabel) + '</div>';
        wards.forEach(function (ward, i) {
            var v = values[i] != null ? values[i] : 0;
            html += '<div class="heatmap-value-cell" style="background-color:' + heatmapColor(v) + '">';
            html += '<span>' + escapeHtml(String(v)) + '%</span></div>';
        });
        html += '</div></div>';

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
        initExportButtons();
        bindFilterForm();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
