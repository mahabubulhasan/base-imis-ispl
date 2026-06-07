<?php

namespace Tests\Unit;

use App\Support\Swm\SwmImportRowHelper;
use PHPUnit\Framework\TestCase;

class SwmImportRowHelperTest extends TestCase
{
    public function test_normalize_row_converts_headers_to_snake_case(): void
    {
        $norm = SwmImportRowHelper::normalizeRow([
            'Organization Type' => 'Private',
            'contact_number' => '12345',
        ]);

        $this->assertSame('Private', $norm['organization_type']);
        $this->assertSame('12345', $norm['contact_number']);
    }

    public function test_row_is_empty(): void
    {
        $this->assertTrue(SwmImportRowHelper::rowIsEmpty(['a' => '', 'b' => null]));
        $this->assertFalse(SwmImportRowHelper::rowIsEmpty(['a' => 'x']));
    }

    public function test_parse_boolean_accepts_common_values(): void
    {
        $this->assertTrue(SwmImportRowHelper::parseBoolean('yes'));
        $this->assertFalse(SwmImportRowHelper::parseBoolean('no'));
        $this->assertTrue(SwmImportRowHelper::parseBoolean('active'));
    }

    public function test_resolve_by_label(): void
    {
        $map = [1 => 'Alpha Org', 2 => 'Beta Org'];
        $this->assertSame(2, SwmImportRowHelper::resolveByLabel('Beta Org', $map));
        $this->assertSame(1, SwmImportRowHelper::resolveByLabel('1', $map));
        $this->assertNull(SwmImportRowHelper::resolveByLabel('Missing', $map));
    }

    public function test_resolve_config_key(): void
    {
        $map = ['cash' => 'Cash', 'cheque' => 'Cheque'];
        $this->assertSame('cash', SwmImportRowHelper::resolveConfigKey('Cash', $map));
        $this->assertSame('cheque', SwmImportRowHelper::resolveConfigKey('cheque', $map));
    }

    public function test_parse_comma_separated_ints(): void
    {
        $this->assertSame([1, 2, 3], SwmImportRowHelper::parseCommaSeparatedInts('1, 2,3'));
        $this->assertNull(SwmImportRowHelper::parseCommaSeparatedInts(''));
    }
}
