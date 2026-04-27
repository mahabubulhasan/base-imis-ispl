<?php

namespace App\Services\BuildingInfo;

use App\Models\BuildingInfo\Building;
use App\Models\BuildingInfo\Household;

class BuildingHouseholdLinkService
{
    /**
     * @return list<string>
     */
    public function parseCsv(?string $csv): array
    {
        if ($csv === null || $csv === '') {
            return [];
        }

        return collect(explode(',', $csv))
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $ids
     */
    public function toCsv(array $ids): ?string
    {
        $clean = array_values(array_unique(array_filter(array_map('trim', $ids))));
        if ($clean === []) {
            return null;
        }

        return implode(',', $clean);
    }

    public function syncFromBuilding(Building $building, ?string $previousCsv, ?string $newCsv): void
    {
        $bin = $building->bin;
        // dd($building,$previousCsv,$newCsv);
        if ($bin === null || $bin === '') {
            return;
        }

        $previous = $this->parseCsv($previousCsv);
        $new = $this->parseCsv($newCsv);
        // dd($new,$previous);
        foreach ($new as $householdId) {
            Household::query()
                ->whereNull('deleted_at')
                ->where('household_id', $householdId)
                ->update(['bin' => $bin]);
        }

        // Treat current household->bin links as source of truth for unlinking, so
        // deselecting all households reliably clears existing links even if CSV
        // history was previously inconsistent.
        $unlinkQuery = Household::query()
            ->whereNull('deleted_at')
            ->where('bin', $bin);

        if ($new !== []) {
            $unlinkQuery->whereNotIn('household_id', $new);
        }

        $unlinkQuery->update(['bin' => null]);
    }

    public function syncHouseholdBinChange(string $householdId, ?string $oldBin, ?string $newBin): void
    {
        $householdId = trim($householdId);
        if ($householdId === '') {
            return;
        }

        $oldBin = ($oldBin !== null && $oldBin !== '') ? $oldBin : null;
        $newBin = ($newBin !== null && $newBin !== '') ? $newBin : null;

        if ($oldBin === $newBin) {
            return;
        }

        if ($newBin === null) {
            $this->removeHouseholdIdFromAllBuildings($householdId);

            return;
        }

        $this->removeHouseholdIdFromAllBuildings($householdId);
        $this->addHouseholdIdToBuilding($newBin, $householdId);
    }

    private function removeHouseholdIdFromAllBuildings(string $householdId): void
    {
        Building::query()
            ->whereNotNull('swm_customer_id')
            ->where('swm_customer_id', '!=', '')
            ->select(['bin', 'swm_customer_id'])
            ->chunk(200, function ($buildings) use ($householdId) {
                foreach ($buildings as $building) {
                    $ids = $this->parseCsv($building->swm_customer_id);
                    if (! in_array($householdId, $ids, true)) {
                        continue;
                    }

                    $building->swm_customer_id = $this->toCsv(array_values(array_diff($ids, [$householdId])));
                    $building->save();
                }
            });
    }

    private function addHouseholdIdToBuilding(string $bin, string $householdId): void
    {
        $building = Building::find($bin);
        if (! $building) {
            return;
        }

        $ids = $this->parseCsv($building->swm_customer_id);
        if (! in_array($householdId, $ids, true)) {
            $ids[] = $householdId;
        }

        $building->swm_customer_id = $this->toCsv($ids);
        $building->save();
    }
}
