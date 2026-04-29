<?php

namespace App\Services\Swm\Queries;

/**
 * Single-row household KPI scalars for active (non-deleted) rows in building_info.households.
 * Populated by {@see SwmHouseholdKpiQueries::coreAggregate()} using one PostgreSQL query.
 */
final readonly class HouseholdKpiCoreAggregate
{
    public function __construct(
        public int $householdCount,
        public float $totalDailyWasteVolumeKg,
        public float $formalCollectedKg,
        public int $coveredHouseholdCount,
        public int $segregatedCount,
        public int $segregationNotYesCount,
        public int $totalFamilyMembers,
        public int $distinctHoldingsCount,
        public float $avgDailyWasteVolume,
    ) {
    }

    public static function fromDatabaseRow(?object $row): self
    {
        if ($row === null) {
            return new self(0, 0.0, 0.0, 0, 0, 0, 0, 0, 0.0);
        }

        return new self(
            (int) $row->household_count,
            (float) ($row->total_daily_waste_volume_kg ?? 0),
            (float) ($row->formal_collected_kg ?? 0),
            (int) $row->covered_household_count,
            (int) $row->segregated_count,
            (int) $row->segregation_not_yes_count,
            (int) ($row->total_family_members ?? 0),
            (int) $row->distinct_holdings_count,
            (float) ($row->avg_daily_waste_volume ?? 0),
        );
    }
}
