<?php

namespace App\Imports\BuildingInfo;

use App\Models\BuildingInfo\Building;
use App\Models\BuildingInfo\Household;
use App\Models\LayerInfo\Lic;
use App\Models\Swm\Worker;
use App\Services\BuildingInfo\HouseholdService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HouseholdImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(private int $userId)
    {
    }

    public function collection(Collection $rows): void
    {
        $service = app(HouseholdService::class);
        $columnDefinitions = $service->excelColumnDefinitions();
        $licMap = Lic::query()
            ->whereNull('deleted_at')
            ->orderBy('community_name')
            ->get(['id', 'community_name'])
            ->mapWithKeys(fn ($row) => [$row->id => "{$row->community_name} - {$row->id}"])
            ->all();

        $workerMap = Worker::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapWithKeys(fn ($row) => [$row->id => "{$row->name} - {$row->id}"])
            ->all();

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $norm = SwmImportRowHelper::mapRowToKeys(
                SwmImportRowHelper::normalizeRow($row->toArray()),
                $columnDefinitions
            );
            if (SwmImportRowHelper::rowIsEmpty($norm)) {
                continue;
            }

            try {
                $householdId = trim((string) ($norm['household_id'] ?? ''));
                $ownerName = trim((string) ($norm['household_owner_name'] ?? ''));
                $contactNumber = trim((string) ($norm['contact_number'] ?? ''));
                $roadName = trim((string) ($norm['road_name'] ?? ''));
                $holdingNumber = trim((string) ($norm['holding_number'] ?? ''));

                if ($householdId === '') {
                    $this->errors[] = __('Row :n: household_id is required.', ['n' => $rowNum]);
                    continue;
                }
                if ($ownerName === '') {
                    $this->errors[] = __('Row :n: household_owner_name is required.', ['n' => $rowNum]);
                    continue;
                }
                if ($contactNumber === '') {
                    $this->errors[] = __('Row :n: contact_number is required.', ['n' => $rowNum]);
                    continue;
                }
                if ($roadName === '') {
                    $this->errors[] = __('Row :n: road_name is required.', ['n' => $rowNum]);
                    continue;
                }
                if ($holdingNumber === '') {
                    $this->errors[] = __('Row :n: holding_number is required.', ['n' => $rowNum]);
                    continue;
                }

                $status = $this->resolveStatus($norm['status'] ?? null);
                if ($status === null) {
                    $this->errors[] = __('Row :n: status is required.', ['n' => $rowNum]);
                    continue;
                }

                $wardRaw = $norm['ward'] ?? null;
                if ($wardRaw === null || $wardRaw === '') {
                    $this->errors[] = __('Row :n: ward is required.', ['n' => $rowNum]);
                    continue;
                }
                $ward = (int) $wardRaw;
                if ($ward < 1) {
                    $this->errors[] = __('Row :n: invalid ward.', ['n' => $rowNum]);
                    continue;
                }

                $bin = isset($norm['bin']) && trim((string) $norm['bin']) !== ''
                    ? trim((string) $norm['bin'])
                    : null;
                if ($bin !== null && ! Building::query()->whereNull('deleted_at')->where('bin', $bin)->exists()) {
                    $this->errors[] = __('Row :n: invalid bin.', ['n' => $rowNum]);
                    continue;
                }

                $isLic = SwmImportRowHelper::parseBoolean($norm['is_lic'] ?? null) ?? false;
                $licId = null;
                if ($isLic) {
                    $licInput = trim((string) ($norm['lic_id'] ?? ''));
                    if ($licInput === '') {
                        $this->errors[] = __('Row :n: lic_id is required when is_lic is Yes.', ['n' => $rowNum]);
                        continue;
                    }
                    $licId = SwmImportRowHelper::resolveByLabel($licInput, $licMap);
                    if (! $licId) {
                        $this->errors[] = __('Row :n: invalid lic_id.', ['n' => $rowNum]);
                        continue;
                    }
                }

                $vanPullerId = null;
                $vanPullerInput = trim((string) ($norm['van_puller'] ?? ''));
                if ($vanPullerInput !== '') {
                    $vanPullerId = SwmImportRowHelper::resolveByLabel($vanPullerInput, $workerMap);
                    if (! $vanPullerId) {
                        $this->errors[] = __('Row :n: invalid van_puller.', ['n' => $rowNum]);
                        continue;
                    }
                }

                $data = [
                    'household_id' => $householdId,
                    'household_owner_name' => $ownerName,
                    'father_or_husband_name' => ($norm['father_or_husband_name'] ?? '') !== ''
                        ? trim((string) $norm['father_or_husband_name'])
                        : null,
                    'status' => $status,
                    'contact_number' => $contactNumber,
                    'ward' => $ward,
                    'area_mohalla_name' => ($norm['area_mohalla_name'] ?? '') !== ''
                        ? trim((string) $norm['area_mohalla_name'])
                        : null,
                    'road_no' => ($norm['road_no'] ?? '') !== ''
                        ? trim((string) $norm['road_no'])
                        : null,
                    'road_name' => $roadName,
                    'holding_number' => $holdingNumber,
                    'tax_id' => ($norm['tax_id'] ?? '') !== '' ? trim((string) $norm['tax_id']) : null,
                    'waste_charge' => ($norm['waste_charge'] ?? '') !== '' ? $norm['waste_charge'] : null,
                    'bin' => $bin,
                    'is_owner' => SwmImportRowHelper::parseBoolean($norm['is_owner'] ?? null) ?? false,
                    'is_lic' => $isLic,
                    'lic_id' => $licId,
                    'number_of_family_members' => ($norm['number_of_family_members'] ?? '') !== ''
                        ? (int) $norm['number_of_family_members']
                        : null,
                    'daily_waste_volume' => ($norm['daily_waste_volume'] ?? '') !== ''
                        ? $norm['daily_waste_volume']
                        : null,
                    'segregation_practiced' => SwmImportRowHelper::parseBoolean($norm['segregation_practiced'] ?? null) ?? false,
                    'waste_bin_provided' => SwmImportRowHelper::parseBoolean($norm['waste_bin_provided'] ?? null) ?? false,
                    'using_this_service_since' => SwmImportRowHelper::parseDate($norm['using_this_service_since'] ?? null)?->format('Y-m-d'),
                    'survey_date' => SwmImportRowHelper::parseDate($norm['survey_date'] ?? null)?->format('Y-m-d'),
                    'van_puller_id' => $vanPullerId,
                    'remarks' => ($norm['remarks'] ?? '') !== '' ? trim((string) $norm['remarks']) : null,
                ];

                $saved = $service->storeOrUpdate(null, $data);
                if ($saved) {
                    $this->successCount++;
                } else {
                    $this->errors[] = __('Row :n: could not save.', ['n' => $rowNum]);
                }
            } catch (\Throwable $e) {
                $this->errors[] = __('Row :n: :msg', ['n' => $rowNum, 'msg' => $e->getMessage()]);
            }
        }
    }

    protected function resolveStatus($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $input = strtolower(trim((string) $value));
        if (in_array($input, [Household::STATUS_ACTIVE, Household::STATUS_INACTIVE], true)) {
            return $input;
        }

        foreach (Household::statusOptions() as $key => $label) {
            if (strcasecmp(trim((string) $value), (string) $label) === 0) {
                return $key;
            }
        }

        $parsed = SwmImportRowHelper::parseBoolean($value);
        if ($parsed === true) {
            return Household::STATUS_ACTIVE;
        }
        if ($parsed === false) {
            return Household::STATUS_INACTIVE;
        }

        return null;
    }
}
