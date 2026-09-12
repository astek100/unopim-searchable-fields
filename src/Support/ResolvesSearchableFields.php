<?php

namespace Astek\SearchableFields\Support;

use Astek\SearchableFields\Repositories\SearchableFieldRepository;

/**
 * Shared by both filter subclasses: swap the caller's hardcoded field list for
 * the configured one, and fall back to the caller's list when nothing is
 * configured, so an installed-but-unconfigured package is a true no-op.
 */
trait ResolvesSearchableFields
{
    /**
     * @param  mixed  $fields  whatever the caller passed in (core passes ['sku', 'name'])
     * @return array<int, string>
     */
    protected function searchableFields($fields): array
    {
        /*
         * Resolved here instead of through a constructor on purpose: redeclaring
         * the parent constructor would pin this subclass to the parent's current
         * dependency list and break on the next core release that changes it.
         */
        $configured = app(SearchableFieldRepository::class)->codes();

        return $configured !== [] ? $configured : FieldSelection::normalize((array) $fields);
    }
}
