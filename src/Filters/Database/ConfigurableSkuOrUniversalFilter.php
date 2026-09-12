<?php

namespace Astek\SearchableFields\Filters\Database;

use Astek\SearchableFields\Support\ResolvesSearchableFields;
use Webkul\Product\Filter\Database\SkuOrUniversalFilter;

/**
 * Database half of the swap, for installations running without Elasticsearch.
 *
 * UnoPIM picks between this filter and the Elasticsearch one at runtime
 * (FilterManager::getSkuOrUnfilteredFilter()), so both have to be replaced for
 * the setting to hold on either kind of installation.
 */
class ConfigurableSkuOrUniversalFilter extends SkuOrUniversalFilter
{
    use ResolvesSearchableFields;

    /**
     * {@inheritdoc}
     */
    public function applyUnfilteredFilter(
        $fields,
        $operator,
        $value,
        $options = []
    ): static {
        return parent::applyUnfilteredFilter($this->searchableFields($fields), $operator, $value, $options);
    }
}
