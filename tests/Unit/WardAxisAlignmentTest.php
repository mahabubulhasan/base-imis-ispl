<?php

namespace Tests\Unit;

use App\Services\Swm\Dashboard\SwmDashboardAxisKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WardAxisAlignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_align_counts_includes_all_municipal_wards_with_zeros(): void
    {
        $this->seedMunicipalWards([1, 2, 3]);

        $helper = new WardAxisAlignmentTestHelper();
        $aligned = $helper->publicAlignCounts(['2' => 5]);

        $this->assertSame(['1', '2', '3'], $aligned['labels']);
        $this->assertSame([0.0, 5.0, 0.0], $aligned['values']);
    }

    public function test_align_counts_appends_unknown_for_complaint_charts(): void
    {
        $this->seedMunicipalWards([1, 2]);

        $helper = new WardAxisAlignmentTestHelper();
        $aligned = $helper->publicAlignCounts(
            ['1' => 2, SwmDashboardAxisKeys::WARD_AXIS_UNKNOWN_KEY => 1],
            true,
        );

        $this->assertSame(['1', '2', __('Unknown')], $aligned['labels']);
        $this->assertSame([2.0, 0.0, 1.0], $aligned['values']);
    }

    public function test_align_counts_falls_back_to_data_keys_when_master_empty(): void
    {
        $helper = new WardAxisAlignmentTestHelper();
        $aligned = $helper->publicAlignCounts(['7' => 3, '5' => 1]);

        $this->assertSame(['5', '7'], $aligned['labels']);
        $this->assertSame([1.0, 3.0], $aligned['values']);
    }

    /**
     * @param  list<int|string>  $wards
     */
    private function seedMunicipalWards(array $wards): void
    {
        foreach ($wards as $ward) {
            DB::table('layer_info.wards')->insertOrIgnore([
                'ward' => (int) $ward,
            ]);
        }
    }
}

final class WardAxisAlignmentTestHelper
{
    use BuildsCountChartAxisLabels;

    /**
     * @param  array<string|int, int|float|string>  $countsByWard
     * @return array{labels: list<string>, values: list<int|float>}
     */
    public function publicAlignCounts(array $countsByWard, bool $appendUnknown = false): array
    {
        return $this->alignCountsToWardAxis($countsByWard, $appendUnknown);
    }
}
