<?php

namespace App\Services\Swm;

use App\Models\Swm\BillCollectionPayment;
use App\Models\Swm\Landfill;
use App\Models\Swm\Organization;
use App\Models\Swm\Sts;
use App\Models\Swm\Worker;
use App\Services\Swm\Queries\ComplaintStatusCounts;
use App\Services\Swm\Queries\HouseholdKpiCoreAggregate;
use App\Services\Swm\Queries\SwmComplaintKpiQueries;
use App\Services\Swm\Queries\SwmHouseholdKpiQueries;
use App\Services\Swm\Queries\SwmVehicleKpiQueries;
use App\Services\Swm\Queries\SwmWorkerKpiQueries;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SwmDashboardKpiService
{
    public function __construct(
        protected BillCollectionPaymentService $billCollectionPaymentService,
        protected SwmHouseholdKpiQueries $householdKpiQueries,
        protected SwmComplaintKpiQueries $complaintKpiQueries,
        protected SwmWorkerKpiQueries $workerKpiQueries,
        protected SwmVehicleKpiQueries $vehicleKpiQueries,
    ) {
    }

    public function buildDashboardData(?string $monthFrom, ?string $monthTo): array
    {
        [$from, $to] = $this->resolveRange($monthFrom, $monthTo);

        $householdCore = $this->householdKpiQueries->coreAggregate();
        $complaintStatus = $this->complaintKpiQueries->statusCountsInRange($from, $to);

        return [
            'range' => ['from' => $from->format('Y-m'), 'to' => $to->format('Y-m')],
            'existing_kpis' => $this->existingKpis($householdCore, $complaintStatus),
            'coverage' => $this->coverageMetrics($householdCore),
            'billing' => $this->billingMetrics($from, $to),
            'complaints' => $this->complaintMetrics($complaintStatus),
            'service_providers' => $this->serviceProviderMetrics(),
            'service_facilities' => $this->serviceFacilityMetrics(),
            'city_statistics' => $this->cityStatistics($householdCore),
            'ward_statistics' => $this->wardStatistics(),
            'vehicle_statistics' => $this->vehicleStatistics(),
        ];
    }

    public function complaintByTypeChart(?string $monthFrom, ?string $monthTo): array
    {
        [$from, $to] = $this->resolveRange($monthFrom, $monthTo);

        $rows = $this->complaintKpiQueries->countsByTypeInRange($from, $to)->get();

        return [
            'labels' => $rows->pluck('complaint_type')->map(fn ($v) => (string) $v)->values(),
            'values' => $rows->pluck('total')->map(fn ($v) => (int) $v)->values(),
        ];
    }

    public function complaintByWardChart(?string $monthFrom, ?string $monthTo): array
    {
        [$from, $to] = $this->resolveRange($monthFrom, $monthTo);

        $rows = $this->complaintKpiQueries->countsByWardInRange($from, $to)->get();

        return [
            'labels' => $rows->pluck('ward_label')->map(fn ($v) => (string) $v)->values(),
            'values' => $rows->pluck('total')->map(fn ($v) => (int) $v)->values(),
        ];
    }

    public function workersByTypeChart(): array
    {
        $rows = $this->workerKpiQueries->groupedCountByWorkTypeQuery()->get();

        return [
            'labels' => $rows->pluck('work_type')->values(),
            'values' => $rows->pluck('total')->map(fn ($v) => (int) $v)->values(),
        ];
    }

    public function vehiclesByTypeChart(): array
    {
        $rows = $this->vehicleKpiQueries->groupedCountByVehicleTypeQuery()->get();

        return [
            'labels' => $rows->pluck('vehicle_type')->values(),
            'values' => $rows->pluck('total')->map(fn ($v) => (int) $v)->values(),
        ];
    }

    private function existingKpis(HouseholdKpiCoreAggregate $hh, ComplaintStatusCounts $cc): array
    {
        $totalGenerationKg = $hh->totalDailyWasteVolumeKg;
        $householdsCount = $hh->householdCount;
        $formalCollectedKg = $hh->formalCollectedKg;
        $segregatedHouseholds = $hh->segregatedCount;
        $coveredHouseholdCount = $hh->coveredHouseholdCount;
        $functionalSts = (int) Sts::query()->whereNull('deleted_at')->count();

        $complaintTotal = $cc->total;
        $complaintResolved = $cc->resolved;

        $landfillCount = (int) Landfill::query()->whereNull('deleted_at')->count();

        return [
            'Total SW Generation in Households (Ton/day)' => round($totalGenerationKg / 1000, 2),
            'Per Household SW Generation (Kg/HH/day)' => $householdsCount > 0 ? round($totalGenerationKg / $householdsCount, 2) : 0,
            'SW Collected by Formal System (Ton)' => round($formalCollectedKg / 1000, 2),
            'Percent of SW Collected from Total Generated (%)' => $totalGenerationKg > 0 ? round(($formalCollectedKg / $totalGenerationKg) * 100, 2) : 0,
            'SW Disposed at Designated Site (Ton)' => round($formalCollectedKg / 1000, 2),
            'SW Disposed at Non-Designated Sites (Ton)' => 0,
            'Uncollected SW (Ton)' => round(max(0, $totalGenerationKg - $formalCollectedKg) / 1000, 2),
            'HH Practicing Waste Segregation (Number)' => $segregatedHouseholds,
            'Percent of HH Practicing Waste Segregation (%)' => $householdsCount > 0 ? round(($segregatedHouseholds / $householdsCount) * 100, 2) : 0,
            'Functional Secondary Transfer Stations (STS) (Number)' => $functionalSts,
            'Complaint Resolution (%)' => $complaintTotal > 0 ? round(($complaintResolved / $complaintTotal) * 100, 2) : 0,
            'STS Functionality (%)' => $functionalSts > 0 ? 100 : 0,
            'Landfill Functionality (%)' => $landfillCount > 0 ? 100 : 0,
            'HH Awareness (Segregation Proxy) (%)' => $householdsCount > 0 ? round(($segregatedHouseholds / $householdsCount) * 100, 2) : 0,
            'Waste Collection Efficiency (%)' => $totalGenerationKg > 0 ? round(($formalCollectedKg / $totalGenerationKg) * 100, 2) : 0,
            'HH Collection Coverage (%)' => $householdsCount > 0 ? round(($coveredHouseholdCount / $householdsCount) * 100, 2) : 0,
        ];
    }

    private function coverageMetrics(HouseholdKpiCoreAggregate $hh): array
    {
        $avgWasteByWardRows = $this->householdKpiQueries->averageDailyWasteByWard();

        return [
            'Total number of primary collection sites/buildings' => $hh->householdCount,
            'Total number of buildings by wards' => (int) DB::table('building_info.buildings')->whereNull('deleted_at')->count(),
            'Total number of family members/population covered' => $hh->totalFamilyMembers,
            'Number of households practicing segregation' => $hh->segregatedCount,
            'Number of households NOT practicing segregation' => $hh->segregationNotYesCount,
            'Average daily waste volume collected per building' => round($hh->avgDailyWasteVolume, 2),
            'Average daily waste volume collected per ward' => $avgWasteByWardRows->map(function ($row) {
                return 'Ward '.$row->ward.': '.round((float) $row->avg_waste, 2);
            })->implode(', '),
            'Covered households' => $hh->coveredHouseholdCount,
        ];
    }

    private function billingMetrics(Carbon $from, Carbon $to): array
    {
        $paymentsInRange = (float) BillCollectionPayment::query()
            ->whereNull('deleted_at')
            ->whereDate('payment_for_month', '>=', $from->copy()->startOfMonth()->toDateString())
            ->whereDate('payment_for_month', '<=', $to->copy()->endOfMonth()->toDateString())
            ->sum('amount');

        $totalDue = '0.00';
        $totalBilled = '0.00';
        $m = $from->copy()->startOfMonth();
        $households = $this->householdKpiQueries->activeForBilling();
        while ($m->lte($to)) {
            foreach ($households as $household) {
                $due = $this->billCollectionPaymentService->marginalDueForMonth($household, $m);
                $totalDue = bcadd($totalDue, $due, 2);
            }
            $m->addMonth();
        }
        $totalBilled = bcadd($totalDue, number_format($paymentsInRange, 2, '.', ''), 2);

        return [
            'Total billed amount in the selected period' => $totalBilled,
            'Total revenue collected' => number_format($paymentsInRange, 2, '.', ''),
            'Total due amount' => $totalDue,
        ];
    }

    private function complaintMetrics(ComplaintStatusCounts $cc): array
    {
        return [
            'Total complaints received' => $cc->total,
            'Total complaints resolved' => $cc->resolved,
            'Total complaints pending' => $cc->pending,
            'Total complaints in process' => $cc->inProcess,
            'Total complaints closed' => $cc->closed,
            'Number of complaints by type' => 'See chart endpoint',
            'Number of complaints by ward' => 'See chart endpoint',
        ];
    }

    private function serviceProviderMetrics(): array
    {
        $providerCount = (int) Organization::query()->whereNull('deleted_at')->count();
        $workerCount = (int) Worker::query()->whereNull('deleted_at')->count();
        $fleet = $this->vehicleKpiQueries->activeCountAndFleetCapacity();

        $workersByType = $this->workerKpiQueries->groupedCountByWorkTypeQuery()
            ->get()
            ->map(fn ($row) => $row->work_type.': '.(int) $row->total)
            ->implode(', ');

        $workersByWardOrg = $this->householdKpiQueries->workersByWardOrganizationSummary()
            ->map(fn ($row) => 'Ward '.$row->ward_label.' / '.$row->org_name.': '.(int) $row->hh_count)
            ->implode(', ');

        $vehiclesByType = $this->vehicleKpiQueries->groupedCountByVehicleTypeQuery()
            ->get()
            ->map(fn ($row) => $row->vehicle_type.': '.(int) $row->total)
            ->implode(', ');

        return [
            'Total number of service providers' => $providerCount,
            'Total number of workers' => $workerCount,
            'Workers by work type' => $workersByType,
            'Workers by ward/area and by service provider' => $workersByWardOrg !== '' ? $workersByWardOrg : 'N/A',
            'Total vehicles' => $fleet->activeVehicleCount,
            'Vehicles by type' => $vehiclesByType,
            'Total fleet capacity available by service area or dumping place' => round($fleet->totalCapacity, 2),
        ];
    }

    private function serviceFacilityMetrics(): array
    {
        $sts = Sts::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->limit(20)
            ->get(['name', 'operator_name', 'contact_number', 'capacity', 'segregation_practiced'])
            ->map(function ($row) {
                return $row->name.' ('.$row->operator_name.', '.$row->contact_number.', cap: '.($row->capacity ?? 'N/A').', seg: '.($row->segregation_practiced ? 'Yes' : 'No').')';
            })->implode('; ');

        $landfills = Landfill::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->limit(20)
            ->get(['name', 'operator_name', 'contact_number', 'capacity', 'segregation_practiced', 'treatment', 'reuse_practiced'])
            ->map(function ($row) {
                return $row->name.' ('.$row->operator_name.', '.$row->contact_number.', cap: '.($row->capacity ?? 'N/A').', seg: '.($row->segregation_practiced ? 'Yes' : 'No').', treat: '.($row->treatment ? 'Yes' : 'No').', reuse: '.($row->reuse_practiced ? 'Yes' : 'No').')';
            })->implode('; ');

        return [
            'STS list (operator, contact, capacity, segregation status)' => $sts !== '' ? $sts : 'N/A',
            'Landfill list (operator, capacity, segregation, treatment, reuse status)' => $landfills !== '' ? $landfills : 'N/A',
        ];
    }

    private function cityStatistics(HouseholdKpiCoreAggregate $hh): array
    {
        $totalPopulation = $hh->totalFamilyMembers;
        $totalHouseholds = $hh->householdCount;
        $totalHoldings = $hh->distinctHoldingsCount;
        $totalWards = (int) DB::table('layer_info.wards')->whereNull('deleted_at')->count();
        $totalDailyWaste = $hh->totalDailyWasteVolumeKg;
        $wastePerCapitaKg = $totalPopulation > 0 ? round($totalDailyWaste / $totalPopulation, 4) : 0;
        $dailyWasteTon = round($totalDailyWaste / 1000, 2);

        $areaKm2Raw = DB::selectOne("SELECT COALESCE(SUM(ST_Area(geom::geography)) / 1000000.0, 0) as area_km2 FROM layer_info.citypolys WHERE deleted_at IS NULL");
        $areaKm2 = round((float) ($areaKm2Raw->area_km2 ?? 0), 2);

        $sourceSegregatedFamilies = $hh->segregatedCount;
        $familiesUsingCollection = $hh->coveredHouseholdCount;
        $coverageRatio = $totalHouseholds > 0 ? round(($familiesUsingCollection / $totalHouseholds) * 100, 2) : 0;

        return [
            'Total Population' => $totalPopulation,
            'Total Households' => $totalHouseholds,
            'Total Holdings' => $totalHoldings,
            'Total Area Km2' => $areaKm2,
            'Total Wards' => $totalWards,
            'Avg Family Size' => $totalHouseholds > 0 ? round($totalPopulation / $totalHouseholds, 2) : 0,
            'Waste Per Capita Kg' => $wastePerCapitaKg,
            'Daily Waste Ton' => $dailyWasteTon,
            'Monthly Waste Ton' => round($dailyWasteTon * 30, 2),
            'Source Segregated Families' => $sourceSegregatedFamilies,
            'Families using Collection Service' => $familiesUsingCollection,
            'Coverage Ratio Percent' => $coverageRatio,
        ];
    }

    private function wardStatistics(): array
    {
        return $this->householdKpiQueries->wardStatisticsRows()
            ->map(function ($row) {
                $total = (int) $row->total_households;
                $covered = (int) $row->covered_households;

                return [
                    'ward_no' => (int) $row->ward,
                    'total_households' => $total,
                    'waste_vans' => null,
                    'covered_households' => $covered,
                    'coverage_percent' => $total > 0 ? round(($covered / $total) * 100, 2) : 0,
                ];
            })
            ->all();
    }

    private function vehicleStatistics(): array
    {
        return $this->vehicleKpiQueries->groupedCountByVehicleTypeQuery()
            ->get()
            ->map(function ($row) {
                return [
                    'vehicle_type' => (string) $row->vehicle_type,
                    'total_count' => (int) $row->total,
                    'remarks' => '',
                ];
            })
            ->all();
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRange(?string $monthFrom, ?string $monthTo): array
    {
        $from = $this->parseMonth($monthFrom) ?? now()->startOfMonth();
        $to = $this->parseMonth($monthTo) ?? now()->startOfMonth();
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->copy()->startOfMonth(), $to->copy()->startOfMonth()];
    }

    private function parseMonth(?string $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }
        try {
            return Carbon::createFromFormat('Y-m', trim($value))->startOfMonth();
        } catch (\Throwable) {
            return null;
        }
    }
}
