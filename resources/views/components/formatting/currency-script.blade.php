@once
<script>
(function () {
    window.ImisFormat = window.ImisFormat || {};
    var currencies = @json(config('formatting.currencies'));
    var defaultCurrency = @json(config('formatting.default_currency', 'tk'));

    function normalizeAmount(value) {
        if (value === null || value === undefined || value === '') {
            return null;
        }
        var n = Number(String(value).replace(/,/g, ''));
        return isFinite(n) ? n : null;
    }

    window.ImisFormat.currency = function (code, value, groupThousands) {
        code = code || defaultCurrency;
        var config = currencies[code];
        if (!config) {
            return '—';
        }

        var amount = normalizeAmount(value);
        if (amount === null) {
            return config.missing || '—';
        }

        var grouped = groupThousands !== false;
        var thousandsSep = grouped ? (config.thousands_separator || ',') : '';
        var decimals = config.decimals != null ? config.decimals : 0;
        var decimalSep = config.decimal_separator || '.';

        var fixed = amount.toFixed(decimals);
        var parts = fixed.split('.');
        var intPart = parts[0];
        var fracPart = parts.length > 1 ? parts[1] : '';

        if (thousandsSep) {
            intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);
        }

        return decimals > 0 ? intPart + decimalSep + fracPart : intPart;
    };
})();
</script>
@endonce
