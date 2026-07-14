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
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HouseholdImport implements ToCollection, WithHeadingRow, WithMultipleSheets
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(private int $userId)
    {
    }

    public function sheets(): array
    {
        return [0 => $this];   // process ONLY the first sheet
    }

    public function collection(Collection $rows): void
    {
        $service = app(HouseholdService::class);
        $columnDefinitions = $service->importColumnDefinitions();
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
            if (SwmImportRowHelper::rowHasNoRequiredData($norm, $columnDefinitions)) {
                continue;
            }
            try {
                $householdId = trim((string) ($norm['household_id'] ?? ''));
                $ownerName = trim((string) ($norm['household_owner_name'] ?? ''));
                $contactNumber = trim((string) ($norm['contact_number'] ?? ''));
                $roadName = trim((string) ($norm['road_name'] ?? ''));
                $holdingNumber = trim((string) ($norm['holding_number'] ?? ''));

                if ($householdId === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'household_id', $columnDefinitions);
                    continue;
                }
                if ($ownerName === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'household_owner_name', $columnDefinitions);
                    continue;
                }
                if ($contactNumber === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'contact_number', $columnDefinitions);
                    continue;
                }
                if ($roadName === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'road_name', $columnDefinitions);
                    continue;
                }
                if ($holdingNumber === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'holding_number', $columnDefinitions);
                    continue;
                }

                $status = $this->resolveStatus($norm['status'] ?? null);
                if ($status === null) {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'status', $columnDefinitions);
                    continue;
                }

                $wardRaw = $norm['ward'] ?? null;
                if ($wardRaw === null || $wardRaw === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'ward', $columnDefinitions);
                    continue;
                }
                $ward = (int) $wardRaw;
                if ($ward < 1) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'ward', $columnDefinitions);
                    continue;
                }

                $bin = isset($norm['bin']) && trim((string) $norm['bin']) !== ''
                    ? trim((string) $norm['bin'])
                    : null;
                if ($bin !== null && ! Building::query()->whereNull('deleted_at')->where('bin', $bin)->exists()) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'bin', $columnDefinitions);
                    continue;
                }

                $isLic = SwmImportRowHelper::parseBoolean($norm['is_lic'] ?? null) ?? false;
                $licId = null;
                if ($isLic) {
                    $licInput = trim((string) ($norm['lic_id'] ?? ''));
                    if ($licInput === '') {
                        $this->errors[] = SwmImportRowHelper::rowRequiredWhen(
                            $rowNum,
                            'lic_id',
                            $columnDefinitions,
                            'is_lic',
                            (string) __('Yes')
                        );
                        continue;
                    }
                    $licId = SwmImportRowHelper::resolveByLabel($licInput, $licMap);
                    if (! $licId) {
                        $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'lic_id', $columnDefinitions);
                        continue;
                    }
                }

                $vanPullerId = null;
                $vanPullerInput = trim((string) ($norm['van_puller'] ?? ''));
                if ($vanPullerInput !== '') {
                    $vanPullerId = SwmImportRowHelper::resolveByLabel($vanPullerInput, $workerMap);
                    if (! $vanPullerId) {
                        $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'van_puller', $columnDefinitions);
                        continue;
                    }
                }

                $isOwner = SwmImportRowHelper::parseBoolean($norm['is_owner'] ?? null) ?? false;
                $wasteBinProvided = SwmImportRowHelper::parseBoolean($norm['waste_bin_provided'] ?? null) ?? false;
                // Waste bin row details are not imported via Excel; only parent-level fields are supported.
                if (! $isOwner || $wasteBinProvided) {
                    $wasteBinProvided = false;
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
                    'waste_charge' => SwmImportRowHelper::parseDecimal($norm['waste_charge'] ?? null),
                    'bin' => $bin,
                    'is_owner' => $isOwner,
                    'is_lic' => $isLic,
                    'lic_id' => $licId,
                    'number_of_family_members' => ($norm['number_of_family_members'] ?? '') !== ''
                        ? (int) $norm['number_of_family_members']
                        : null,
                    'daily_waste_volume' => SwmImportRowHelper::parseDecimal($norm['daily_waste_volume'] ?? null),
                    'segregation_practiced' => SwmImportRowHelper::parseBoolean($norm['segregation_practiced'] ?? null) ?? false,
                    'waste_bin_provided' => $wasteBinProvided,
                    'using_this_service_since' => SwmImportRowHelper::parseDate($norm['using_this_service_since'] ?? null)?->format('Y-m-d'),
                    'survey_date' => SwmImportRowHelper::parseDate($norm['survey_date'] ?? null)?->format('Y-m-d'),
                    'van_puller_id' => $vanPullerId,
                    'remarks' => ($norm['remarks'] ?? '') !== '' ? trim((string) $norm['remarks']) : null,
                ];



                $existing = Household::withTrashed()
                    ->where('household_id', $householdId)
                    ->first();
                if ($existing && $existing->trashed()) {
                    $existing->restore();
                }
                $existingId = $existing?->id;

                $saved = $service->storeOrUpdate($existingId, $data);
                if ($saved) {
                    $this->successCount++;
                } else {
                    $this->errors[] = SwmImportRowHelper::rowMessage($rowNum, __('could not save.'));
                }
            } catch (\Throwable $e) {
                $this->errors[] = SwmImportRowHelper::importCatchMessage($rowNum, $e);
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
