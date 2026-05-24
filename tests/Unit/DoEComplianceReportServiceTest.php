<?php

namespace Tests\Unit;

use App\Models\BuildingInfo\Household;
use App\Models\LayerInfo\Lic;
use App\Models\Swm\Landfill;
use App\Models\Swm\LandfillLog;
use App\Models\Swm\LandfillType;
use App\Models\Swm\ModuleSetting;
use App\Models\Swm\Organization;
use App\Models\Swm\OrganizationType;
use App\Models\Swm\StsLog;
use App\Models\Swm\Vehicle;
use App\Models\Swm\VehicleType;
use App\Models\Swm\WasteBin;
use App\Models\Swm\WasteBinType;
use App\Models\Swm\WasteProcessingLog;
use App\Models\Swm\WorkType;
use App\Models\Swm\Worker;
use App\Services\Swm\DoEComplianceReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoEComplianceReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private DoEComplianceReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DoEComplianceReportService::class);
        config([
            'app.city' => 'TestCity',
            'app.city_suffix' => 'Pouroshova',
            'app.city_bn' => '',
        ]);
    }

    public function test_waste_quantity_annualizes_from_household_and_logs(): void
    {
        $year = 2026;
        ModuleSetting::query()->firstOrCreate([], [
            'per_capita_sw_generation_kg_per_day' => 1.0,
        ]);

        $this->createHousehold([
            'number_of_family_members' => 1000,
            'daily_waste_volume' => 500,
            'created_at' => '2025-01-01 00:00:00',
        ]);

        $org = $this->createOrganization();
        $vehicle = $this->createVehicle($org);

        StsLog::query()->create([
            'vehicle_id' => $vehicle->id,
            'sts_name' => 'STS',
            'quantity_ton' => 10,
            'source_wards' => ['1'],
            'entry_at' => '2026-06-15 10:00:00',
            'operation_date' => '2026-06-15',
            'operation_status' => StsLog::STATUS_COMPLETED,
        ]);

        LandfillLog::query()->create([
            'vehicle_id' => $vehicle->id,
            'landfill_name' => 'LF',
            'quantity_ton' => 8,
            'weighbridge_weight_ton' => 12,
            'source_wards' => ['1'],
            'source_sts_ids' => [],
            'entry_at' => '2026-06-10 10:00:00',
            'operation_date' => '2026-06-10',
            'operation_status' => LandfillLog::STATUS_COMPLETED,
        ]);

        WasteProcessingLog::query()->create([
            'entry_at' => '2026-06-01',
            'report_date' => '2026-06-15',
            'reporting_month' => '2026-06-01',
            'waste_received_ton' => 100,
            'organic_waste_composted_ton' => 20,
            'inorganic_waste_recycled_ton' => 10,
            'waste_incinerated_ton' => 5,
            'waste_burned_open_air_ton' => 5,
            'residual_waste_landfilled_ton' => 60,
        ]);

        $report = $this->service->build($year);
        $wq = $report['waste_quantity'];

        $this->assertSame('1.00', $wq['daily_avg']);
        $this->assertSame('365.00', $wq['annual_total']);
        $this->assertSame('10.00', $wq['collected_formal']);
        $this->assertSame('12.00', $wq['stockpiled']);
        $this->assertSame('182.50', $wq['uncollected']);
        $this->assertSame('20.00', $report['waste_processing']['proc_organic']);
        $this->assertSame('TestCity Pouroshova', $report['institution']['org_name']);
    }

    public function test_institution_org_name_uses_city_bn_with_suffix_bn(): void
    {
        config([
            'app.city_bn' => 'চাঁপাইনবাবগঞ্জ',
            'app.city_suffix_bn' => 'পৌরসভা',
        ]);

        $report = $this->service->build((int) now()->year);

        $this->assertSame('চাঁপাইনবাবগঞ্জ পৌরসভা', $report['institution']['org_name']);
    }

    public function test_institution_org_name_uses_full_city_bn_without_appending_suffix(): void
    {
        config([
            'app.city_bn' => 'চাঁপাইনবাবগঞ্জ পৌরসভা',
            'app.city_suffix_bn' => 'পৌরসভা',
        ]);

        $report = $this->service->build((int) now()->year);

        $this->assertSame('চাঁপাইনবাবগঞ্জ পৌরসভা', $report['institution']['org_name']);
    }

    public function test_storage_metrics_and_lic_counts(): void
    {
        $year = (int) now()->year;
        $concreteType = WasteBinType::query()->firstOrCreate(['name' => 'Concrete']);

        WasteBin::query()->create([
            'waste_bin_type_id' => $concreteType->id,
            'total_capacity_kg' => 1000,
            'placed_at_buildings' => true,
            'ward_no' => 1,
            'created_at' => now()->subYear(),
        ]);
        WasteBin::query()->create([
            'waste_bin_type_id' => $concreteType->id,
            'total_capacity_kg' => 500,
            'placed_at_buildings' => false,
            'ward_no' => 2,
            'created_at' => now()->subYear(),
        ]);

        Lic::forceCreate([
            'community_name' => 'LIC A',
            'population_total' => 100,
            'sanitation_status' => true,
        ]);
        Lic::forceCreate([
            'community_name' => 'LIC B',
            'population_total' => 50,
            'sanitation_status' => false,
        ]);

        $report = $this->service->build($year);

        $this->assertSame('1.50', $report['storage']['collection_volume']);
        $this->assertSame('1', $report['storage']['num_houses']);
        $this->assertSame('2', $report['lic']['total_slums']);
        $this->assertSame('1', $report['lic']['slums_sanitation']);
    }

    public function test_bin_rows_list_db_types_with_avg_capacity_kg(): void
    {
        $year = (int) now()->year;
        $typeA = WasteBinType::query()->create(['name' => 'DoE Bin Type A']);
        $typeB = WasteBinType::query()->create(['name' => 'DoE Bin Type B']);
        WasteBinType::query()->create(['name' => 'DoE Bin Type Empty']);

        WasteBin::query()->create([
            'waste_bin_type_id' => $typeA->id,
            'total_capacity_kg' => 1000,
            'placed_at_buildings' => true,
            'ward_no' => 1,
            'created_at' => now()->subYear(),
        ]);
        WasteBin::query()->create([
            'waste_bin_type_id' => $typeA->id,
            'total_capacity_kg' => 500,
            'placed_at_buildings' => false,
            'ward_no' => 2,
            'created_at' => now()->subYear(),
        ]);
        WasteBin::query()->create([
            'waste_bin_type_id' => $typeB->id,
            'total_capacity_kg' => 200,
            'placed_at_buildings' => false,
            'ward_no' => 3,
            'created_at' => now()->subYear(),
        ]);

        $report = $this->service->build($year);
        $bins = $report['bins'];
        $rowA = collect($bins)->firstWhere('type', 'DoE Bin Type A');
        $rowB = collect($bins)->firstWhere('type', 'DoE Bin Type B');

        $this->assertNotNull($rowA);
        $this->assertSame('750', $rowA['size']);
        $this->assertSame('কেজি', $rowA['unit']);
        $this->assertSame('2', $rowA['count']);

        $this->assertNotNull($rowB);
        $this->assertSame('200', $rowB['size']);
        $this->assertSame('কেজি', $rowB['unit']);
        $this->assertSame('1', $rowB['count']);

        $rowEmpty = collect($bins)->firstWhere('type', 'DoE Bin Type Empty');
        $this->assertNotNull($rowEmpty);
        $this->assertSame('', $rowEmpty['size']);
        $this->assertSame('', $rowEmpty['unit']);
        $this->assertSame('', $rowEmpty['count']);

        $this->assertContains('DoE Bin Type A', $report['waste_bin_type_options']);
    }

    public function test_landfill_type_rows_use_capacity(): void
    {
        $year = (int) now()->year;
        $type = LandfillType::query()->firstOrCreate(['name' => 'Managed Anaerobic/Semi-anaerobic']);

        Landfill::query()->create([
            'name' => 'Site A',
            'operator_name' => 'Op',
            'contact_number' => '01',
            'capacity' => '5000',
            'landfill_type_id' => $type->id,
            'operational_status' => 'active',
            'created_at' => now()->subMonth(),
        ]);

        $rows = $this->service->build($year)['landfill_types'];

        $this->assertCount(1, $rows);
        $this->assertSame('Site A', $rows[0]['name']);
        $this->assertArrayHasKey('landfill_id', $rows[0]);
        $this->assertSame('5000', $rows[0]['capacity']);
        $this->assertSame('টন', $rows[0]['unit']);
    }

    public function test_landfill_info_rows_include_area_and_unit(): void
    {
        $year = (int) now()->year;
        $type = LandfillType::query()->firstOrCreate(['name' => 'Managed Anaerobic/Semi-anaerobic']);

        Landfill::query()->create([
            'name' => 'Site Area',
            'operator_name' => 'Op',
            'contact_number' => '01',
            'area' => '12.5',
            'manpower_deployed' => 8,
            'weighbridge_facility_available' => true,
            'landfill_type_id' => $type->id,
            'operational_status' => 'active',
            'created_at' => now()->subMonth(),
        ]);

        $sites = $this->service->build($year)['landfill_info']['sites'];
        $row = collect($sites)->firstWhere('name', 'Site Area');

        $this->assertNotNull($row);
        $this->assertSame('12.5', $row['area']);
        $this->assertSame('একর', $row['unit']);
        $this->assertSame('yes', $row['wb']);
        $this->assertSame('8', $row['pax']);
    }

    public function test_landfill_capacity_keeps_decimals_when_stored(): void
    {
        $year = (int) now()->year;
        $type = LandfillType::query()->firstOrCreate(['name' => 'Managed Anaerobic/Semi-anaerobic']);

        Landfill::query()->create([
            'name' => 'Site B',
            'operator_name' => 'Op',
            'contact_number' => '01',
            'capacity' => '5000.5',
            'landfill_type_id' => $type->id,
            'operational_status' => 'active',
            'created_at' => now()->subMonth(),
        ]);

        $rows = $this->service->build($year)['landfill_types'];
        $siteB = collect($rows)->firstWhere('name', 'Site B');

        $this->assertNotNull($siteB);
        $this->assertSame('5000.5', $siteB['capacity']);
    }

    public function test_private_organizations_excludes_government_type(): void
    {
        $year = 2026;
        $this->createOrganization([
            'name' => 'Gov Org',
            'email' => 'gov@example.com',
            'organization_type_id' => OrganizationType::ID_GOVERNMENT,
            'remarks' => 'Government remarks',
        ]);
        $this->createOrganization([
            'name' => 'Private Org',
            'email' => 'private@example.com',
            'organization_type_id' => OrganizationType::ID_PRIVATE,
            'remarks' => 'Private remarks',
        ]);
        $this->createOrganization([
            'name' => 'Other Org',
            'email' => 'other@example.com',
            'organization_type_id' => OrganizationType::ID_OTHER,
            'remarks' => 'Other remarks',
        ]);

        $orgs = $this->service->build($year)['private_organizations'];
        $names = array_column($orgs, 'name');

        $this->assertContains('Private Org', $names);
        $this->assertContains('Other Org', $names);
        $this->assertNotContains('Gov Org', $names);
    }

    public function test_transport_lists_db_vehicle_types_with_counts(): void
    {
        $year = (int) now()->year;
        $org = $this->createOrganization();
        VehicleType::query()->create(['name' => 'DoE Truck']);
        VehicleType::query()->create(['name' => 'DoE Empty Vehicle Type']);
        VehicleType::query()->create(['name' => 'DoE Rickshaw Van']);
        $this->createVehicle($org, 'Truck-1', 'DoE Truck');
        $this->createVehicle($org, 'Van-1', 'DoE Rickshaw Van');

        $report = $this->service->build($year);
        $byType = collect($report['transport'])->keyBy('type');

        $this->assertSame('1', $byType->get('DoE Truck')['existing']);
        $this->assertSame('', $byType->get('DoE Truck')['required']);
        $this->assertSame('1', $byType->get('DoE Rickshaw Van')['existing']);
        $this->assertSame('', $byType->get('DoE Empty Vehicle Type')['existing']);
        $this->assertContains('DoE Truck', $report['vehicle_type_options']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createHousehold(array $overrides = []): Household
    {
        static $counter = 0;
        $counter++;

        return Household::forceCreate(array_merge([
            'household_id' => 'HH-DOE-'.$counter,
            'household_owner_name' => 'Owner',
            'contact_number' => '9800000000',
            'bin' => 'BIN-'.$counter,
            'ward' => '1',
            'holding_number' => 'HN-'.$counter,
            'status' => Household::STATUS_ACTIVE,
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createOrganization(array $overrides = []): Organization
    {
        return Organization::forceCreate(array_merge([
            'name' => 'Test Org',
            'email' => 'org@example.com',
            'address' => 'Address',
            'contact_person_name' => 'Contact',
            'contact_number' => '9800000000',
            'status' => true,
            'organization_type_id' => OrganizationType::ID_GOVERNMENT,
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ], $overrides));
    }

    private function createVehicle(Organization $org, ?string $number = null, ?string $typeName = 'Truck'): Vehicle
    {
        $workType = WorkType::query()->firstOrCreate(['name' => 'Driver']);
        $worker = Worker::query()->create([
            'organization_id' => $org->id,
            'work_type_id' => $workType->id,
            'name' => 'Driver',
            'mobile' => '9811111111',
            'status' => 'active',
        ]);
        $vehicleType = VehicleType::query()->firstOrCreate(['name' => $typeName ?? 'Truck']);

        return Vehicle::query()->create([
            'organization_id' => $org->id,
            'vehicle_type_id' => $vehicleType->id,
            'vehicle_number' => $number ?? 'VEH-'.uniqid(),
            'driver_worker_id' => $worker->id,
            'dumping_place_kind' => 'sts',
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ]);
    }
}
