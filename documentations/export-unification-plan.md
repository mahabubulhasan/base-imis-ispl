# Export Unification Plan

Goal: make **every** SWM + Household + LIC module export through **one** pattern:

> `getColumns → buildBaseQuery → applyFilters → (headers + rows from the same column definitions) → stream download`

This mirrors what the **import** side already has (`HandlesSwmExcelImport` trait). Export has no
equivalent, which is *why* three divergent export styles grew over time.

---

## 1) Current state (verified inventory)

There are **three export engines** and **two row-building styles** today.

| Module | Engine | Row building | Column-def method |
|---|---|---|---|
| `BuildingInfo/HouseholdService` | `SwmExcelExportWriter` (PhpSpreadsheet, callback) | **column-driven** (`buildExportRow` + `formatHouseholdExportValue`) ✅ | `excelColumnDefinitions` |
| `LayerInfo/LowIncomeCommunityServiceClass` | `SwmExcelExportWriter` | **column-driven** (`buildExportRow` + `formatLicExportValue`) ✅ | `excelColumnDefinitions` |
| `Swm/ComplaintService` | `SwmExcelExportWriter` | inline positional (with lookup maps) ⚠️ | `excelColumnDefinitions` |
| `Swm/BillCollectionPaymentService` | `SwmExcelExportWriter` | inline positional ⚠️ | `exportColumnDefinitions` |
| `Swm/StsService` | `SwmExcelTemplateWriter::downloadData` (eager array) | positional ⚠️ | `excelColumnDefinitions` |
| `Swm/WasteBinService` | `SwmExcelTemplateWriter::downloadData` | positional ⚠️ | `excelColumnDefinitions` |
| `Swm/LandfillService` | `SwmExcelTemplateWriter::downloadData` | positional ⚠️ | `excelColumnDefinitions` |
| `Swm/VehicleService` | Spout `WriterFactory` | positional ⚠️ | `exportColumnDefinitions` |
| `Swm/WorkerService` | Spout `WriterFactory` | **column-driven** (`buildExportRow` + `formatWorkerExportValue`) ✅ | `excelColumnDefinitions` |
| `Swm/LandfillLogService` | Spout `WriterFactory` | positional ⚠️ | `excelColumnDefinitions` |
| `Swm/StsLogService` | Spout `WriterFactory` | positional ⚠️ | `excelColumnDefinitions` |
| `Swm/AttendanceLogService` | Spout `WriterFactory` | positional ⚠️ | `excelColumnDefinitions` |
| `Swm/OrganizationService` | Spout `WriterFactory` | positional ⚠️ | `excelColumnDefinitions` |
| `Swm/WasteProcessingService` | Spout `WriterFactory` | positional ⚠️ | `excelColumnDefinitions` |

### The core problem with positional rows
The "positional" modules build each row as a hand-ordered array:

```php
$rows[] = [$row->sts_id, $row->name, $row->location, /* ...17 values by position... */];
```

This array order must stay perfectly in sync with `exportHeaders()` order, but **nothing enforces
it**. Insert one column into `excelColumnDefinitions()` and every value past that point lands under
the wrong header — silently, no error. Column-driven modules (`buildExportRow`) can't drift because
headers and values are produced from the *same* list in the *same* order.

> ⚠️ **Pre-migration audit required:** for each positional module, the current header order
> (`exportHeaders`) must already match the current positional value order. If a module is already
> mis-aligned today, that is a latent bug; the migration will surface it. Decide per module whether
> the current output or the "correct" alignment is the intended one before converting.

### Secondary inconsistencies to normalize
- **Column-def method name:** most use `excelColumnDefinitions()`; `VehicleService` and
  `BillCollectionPaymentService` use `exportColumnDefinitions()`. Standardize the name.
- **Engine choice:** PhpSpreadsheet (`SwmExcelExportWriter`) builds the whole workbook in memory
  before streaming; Spout (`WriterFactory`) is true row-by-row streaming (lower memory on huge
  tables). See decision D1.

---

## 2) Target architecture

### New shared trait: `HandlesSwmExcelExport`
Location: `app/Services/Swm/Concerns/HandlesSwmExcelExport.php` (service-side mirror of the
controller-side `HandlesSwmExcelImport`).

```php
namespace App\Services\Swm\Concerns;

use App\Support\Swm\SwmExcelColumns;
use App\Support\Swm\SwmExcelExportWriter;
use Illuminate\Database\Eloquent\Builder;

trait HandlesSwmExcelExport
{
    /**
     * @param  array<int, array{key:string,label:string,export?:bool,...}>  $columns
     * @param  callable(string $key, mixed $model): mixed  $formatValue
     */
    protected function swmExport(
        string $filename,
        array $columns,
        Builder $query,
        callable $formatValue,
        string $orderBy = 'id',
        int $chunk = 5000,
    ): void {
        $headers = SwmExcelColumns::exportHeaders($columns);

        (new SwmExcelExportWriter())->download($filename, $headers,
            function ($sheet, $colLetter) use ($query, $columns, $formatValue, $orderBy, $chunk) {
                $rowNum = 2;
                $query->orderBy($orderBy)->chunk($chunk,
                    function ($models) use ($sheet, $colLetter, $columns, $formatValue, &$rowNum) {
                        foreach ($models as $model) {
                            $values = SwmExcelColumns::buildExportRow($columns, $model, $formatValue);
                            foreach ($values as $i => $value) {
                                $sheet->setCellValue($colLetter($i + 1) . $rowNum, $value);
                            }
                            $rowNum++;
                        }
                    });
            });
    }
}
```

### What each service keeps (legitimate per-module variation)
1. `excelColumnDefinitions()` — its columns/labels/order (single source of truth).
2. `baseQuery()` + `applyExportFilters($query, $data)` — its query and filters.
3. `formatXExportValue(string $key, $model, ...$lookups)` — its per-column display mapping
   (Yes/No, dates, `implode` of arrays, FK label lookups).

### What every service's `download()` collapses to (~8 lines)
```php
public function download(array $data): void
{
    $columns = $this->excelColumnDefinitions();
    $query   = $this->baseQuery();
    $this->applyExportFilters($query, $data);

    // Hoist any lookup maps ONCE (not per row), then capture in the closure:
    $wasteTypeMap = WasteType::query()->whereNull('deleted_at')->pluck('name', 'id')->all();

    $this->swmExport(
        SwmExcelFilename::export('sts'),
        $columns,
        $query,
        fn (string $key, $model) => $this->formatStsExportValue($key, $model, $wasteTypeMap),
        orderBy: 'swm.sts.id',
    );
}
```

### Converting a positional array → a key-based formatter
Mechanical but must be exact. Example (Sts):

```php
// BEFORE (positional, order-fragile)
$rows[] = [$row->sts_id, $row->name, /* ... */ $row->operational_status];

// AFTER (key-based; keys MUST match excelColumnDefinitions())
protected function formatStsExportValue(string $key, $model, array $wasteTypeMap): mixed
{
    return match ($key) {
        'sts_id'                 => $model->sts_id,
        'name'                   => $model->name,
        'segregation_practiced'  => $model->segregation_practiced ? __('Yes') : __('No'),
        'waste_type_ids'         => implode(', ', array_values(array_intersect_key(
                                        $wasteTypeMap, array_flip($model->waste_type_ids ?? [])))),
        // ...one arm per column key, in any order...
        default => '',
    };
}
```

---

## 3) Decisions to settle before coding

- **D1 — Export engine.** Standardize on `SwmExcelExportWriter` (PhpSpreadsheet) for all exports?
  - *Pro:* one writer, matches the gold-standard modules, consistent with the template writer.
  - *Con:* PhpSpreadsheet holds the whole workbook in memory; Spout streams. For very large tables
    (logs) this is a real memory regression for the 6 Spout modules.
  - *Recommendation:* keep the **public seam** `SwmExcelExportWriter::download(filename, headers, callback)`
    as the single API the services call, and (optionally, later) let it use Spout internally for the
    actual write. Modules stay decoupled from the engine. Start with PhpSpreadsheet; revisit only if
    a log export OOMs.
- **D2 — Trait vs convention.** A trait *enforces* the pattern (recommended) vs a documented
  convention people can drift from (status quo). Recommend the trait.
- **D3 — Column-def method name.** Rename `exportColumnDefinitions()` → `excelColumnDefinitions()`
  in Vehicle and BillCollectionPayment for uniformity (check no other caller depends on the old
  name first).
- **D4 — `downloadData()` fate.** Once Sts/WasteBin/Landfill stop using it, delete
  `SwmExcelTemplateWriter::downloadData()` (it is *only* those 3 callers).

---

## 4) Safety net FIRST — characterization tests

No PHP runtime is available in the working session, and export output must stay byte-stable. Before
touching any service, add **characterization (golden) tests** that pin current behavior:

1. Seed a small known dataset per module.
2. Hit each export route; capture the generated `.xlsx`.
3. Assert the header row + 1–2 full data rows (values and order).

Run them green on the current code, refactor, then they must stay green. This replaces "eyeball the
file" with an automated guard and is the single most important risk control in this plan.

---

## 5) Migration order (lowest risk → highest)

Convert one module per commit; tests must stay green at each step.

1. **`HandlesSwmExcelExport` trait + tests scaffold.** No behavior change.
2. **Already column-driven, just adopt the trait** (smallest diffs, proves the trait):
   - Household, LIC, Worker.
3. **`SwmExcelExportWriter` + inline rows → trait + formatter:**
   - Complaint, BillCollectionPayment (also fixes D3 for BillCollection).
4. **Eager `downloadData` → trait + formatter:**
   - Sts, WasteBin, Landfill. Then delete `downloadData()` (D4).
5. **Spout positional → trait + formatter:**
   - Vehicle (also D3), LandfillLog, StsLog, AttendanceLog, Organization, WasteProcessing.
6. **Cleanup:** remove now-unused Spout imports; confirm no `exportColumnDefinitions` references
   remain; update `documentations/import-export-walkthrough.md` to describe the single export path.

---

## 6) Per-module checklist (repeat for each)

- [ ] Confirm current header order == current positional value order (audit; note discrepancies).
- [ ] Ensure `excelColumnDefinitions()` lists every exported column with a stable `key`.
- [ ] Write `formatXExportValue($key, $model, ...$lookups)` reproducing each current value exactly.
- [ ] Hoist lookup maps (e.g. ward labels, type maps) above `swmExport`, capture via the closure.
- [ ] Replace the old `download()` body with the ~8-line trait call.
- [ ] Remove dead code (positional builder, old writer instantiation, unused imports).
- [ ] Characterization test green (header + sample rows identical to pre-refactor).

---

## 7) Risks

| Risk | Mitigation |
|---|---|
| Column order silently changes during positional→key conversion | Characterization tests (§4) + per-module order audit (§6) |
| Per-row lookups accidentally moved inside the loop (N+1 / perf) | Hoist maps once, capture by `use`/closure arg; review each formatter |
| Memory regression on large log exports (PhpSpreadsheet) | D1 seam keeps engine swappable; monitor log-module exports |
| Hidden callers of `exportColumnDefinitions()` / `downloadData()` | grep before rename/delete (D3/D4) |
| No runtime in authoring session | All behavior verified via the test suite the author runs locally |

---

## 8) Effort estimate

- Trait + characterization test harness: ~0.5 day.
- 14 module conversions @ ~30–60 min each (incl. test): ~1.5 days.
- Cleanup + docs update: ~0.5 day.
- **Total: ~2.5–3 days**, sequenced so each commit is independently green and revertible.

---

## 9) Out of scope (separate follow-ups)

- Wrapping `excelColumnDefinitions()` in a `ColumnSet` value object (ergonomics; see prior
  discussion). Could ride along when touching each service but is not required here.
- Switching the export engine to Spout under the `SwmExcelExportWriter` seam (only if memory
  becomes a real problem).
