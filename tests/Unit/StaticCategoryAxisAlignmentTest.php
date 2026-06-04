<?php

namespace Tests\Unit;

use App\Services\Swm\Dashboard\Concerns\AlignsChartsToStaticCategoryAxis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaticCategoryAxisAlignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_align_counts_uses_master_keys_with_zero_fill(): void
    {
        $helper = new StaticCategoryAxisTestHelper();
        $aligned = $helper->publicAlignCounts(
            ['B' => 2],
            ['A', 'B', 'C'],
        );

        $this->assertSame(['A', 'B', 'C'], $aligned['labels']);
        $this->assertSame([0.0, 2.0, 0.0], $aligned['values']);
    }

    public function test_complaint_type_keys_cover_config(): void
    {
        $helper = new StaticCategoryAxisTestHelper();
        $keys = array_keys(config('swm_complaints.complaint_types', []));
        $aligned = $helper->publicAlignCounts(
            ['waste_not_collected' => 3],
            $keys,
            fn (string $key) => $key,
        );

        $this->assertCount(count($keys), $aligned['labels']);
        $this->assertSame(3.0, $aligned['values'][array_search('waste_not_collected', $keys, true)]);
    }
}

final class StaticCategoryAxisTestHelper
{
    use AlignsChartsToStaticCategoryAxis;

    /**
     * @param  array<string|int, int|float|string>  $counts
     * @param  list<string>  $masterKeys
     * @return array{labels: list<string>, values: list<int|float>}
     */
    public function publicAlignCounts(
        array $counts,
        array $masterKeys,
        ?callable $displayLabel = null,
    ): array {
        return $this->alignCountsToCategoryAxis($counts, $masterKeys, $displayLabel);
    }
}
