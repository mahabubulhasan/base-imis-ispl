<?php

namespace App\Services\Swm\Queries;

/**
 * Complaint counts for one reporting window (soft-deleted excluded, date_time in range).
 */
final class ComplaintStatusCounts
{
    public function __construct(
        public int $total,
        public int $resolved,
        public int $pending,
        public int $inProcess,
        public int $closed,
    ) {
    }

    public static function fromDatabaseRow(?object $row): self
    {
        if ($row === null) {
            return new self(0, 0, 0, 0, 0);
        }

        return new self(
            (int) $row->total,
            (int) $row->resolved,
            (int) $row->pending,
            (int) $row->in_process,
            (int) $row->closed,
        );
    }
}
