<?php

namespace App\Services\Swm\Queries;

final class VehicleFleetStats
{
    public function __construct(
        public int $activeVehicleCount,
        public float $totalCapacity,
    ) {
    }

    public static function fromDatabaseRow(?object $row): self
    {
        if ($row === null) {
            return new self(0, 0.0);
        }

        return new self(
            (int) $row->vehicle_count,
            (float) ($row->total_capacity ?? 0),
        );
    }
}
