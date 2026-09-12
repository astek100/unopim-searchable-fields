<?php

namespace Astek\SearchableFields\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Astek\SearchableFields\Support\FieldSelection;

final class FieldSelectionTest extends TestCase
{
    public function test_normalize_trims_deduplicates_and_drops_non_strings(): void
    {
        $this->assertSame(
            ['sku', 'name'],
            FieldSelection::normalize([' sku ', 'name', 'sku', '', null, 5, 'name'])
        );
    }

    public function test_merge_keeps_fields_selected_on_another_page(): void
    {
        // 'gtin' was ticked on a page that is not the one being submitted.
        $this->assertSame(
            ['gtin', 'sku'],
            FieldSelection::merge(['gtin', 'name'], ['sku', 'name'], ['sku'])
        );
    }

    public function test_merge_removes_only_fields_that_were_on_the_submitted_page(): void
    {
        $this->assertSame(
            [],
            FieldSelection::merge(['sku', 'name'], ['sku', 'name'], [])
        );
    }

    public function test_merge_ignores_a_checked_code_that_was_not_rendered(): void
    {
        $this->assertSame(
            ['sku'],
            FieldSelection::merge([], ['sku'], ['sku', 'injected_code'])
        );
    }

    public function test_merge_of_an_empty_submit_without_visible_codes_changes_nothing(): void
    {
        $this->assertSame(
            ['sku', 'gtin'],
            FieldSelection::merge(['sku', 'gtin'], [], [])
        );
    }
}
