<?php

namespace Tests\Unit\BuildingInfo;

use App\Imports\BuildingInfo\HouseholdImport;
use PHPUnit\Framework\TestCase;

class HouseholdImportTest extends TestCase
{
    public function test_import_class_exposes_counters(): void
    {
        $import = new HouseholdImport(1);
        $this->assertSame(0, $import->successCount);
        $this->assertSame([], $import->errors);
    }
}
