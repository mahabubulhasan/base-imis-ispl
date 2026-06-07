<?php

namespace Tests\Unit;

use App\Imports\Swm\OrganizationImport;
use PHPUnit\Framework\TestCase;

class SwmExcelImportClassesTest extends TestCase
{
    /** @dataProvider importClassProvider */
    public function test_import_classes_expose_counters(string $class): void
    {
        $import = new $class(1);
        $this->assertSame(0, $import->successCount);
        $this->assertSame([], $import->errors);
    }

    public static function importClassProvider(): array
    {
        return [
            [OrganizationImport::class],
            [\App\Imports\Swm\WorkerImport::class],
            [\App\Imports\Swm\VehicleImport::class],
            [\App\Imports\WasteBinImport::class],
            [\App\Imports\StsImport::class],
            [\App\Imports\LandfillImport::class],
            [\App\Imports\Swm\AttendanceLogImport::class],
            [\App\Imports\Swm\StsLogImport::class],
            [\App\Imports\Swm\LandfillLogImport::class],
            [\App\Imports\Swm\WasteProcessingImport::class],
            [\App\Imports\Swm\ComplaintImport::class],
            [\App\Imports\Swm\BillCollectionPaymentImport::class],
            [\App\Imports\BuildingInfo\HouseholdImport::class],
        ];
    }
}
