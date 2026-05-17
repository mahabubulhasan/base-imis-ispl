<?php

namespace App\Services\Swm\Dashboard\Contracts;

use App\Services\Swm\Dashboard\DashboardReportingPeriod;

interface SwmDashboardModuleInterface
{
    public function key(): string;

    public function label(): string;

  /**
   * @return array{submodules: array<int, array{key: string, title: string, blocks: array<int, array<string, mixed>>}>}
   */
    public function build(DashboardReportingPeriod $period): array;

    public function permission(): ?string;
}
