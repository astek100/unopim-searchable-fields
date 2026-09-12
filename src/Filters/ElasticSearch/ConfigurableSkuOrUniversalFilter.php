<?php

namespace Astek\SearchableFields\Filters\ElasticSearch;

use Astek\SearchableFields\Support\ResolvesSearchableFields;
use Webkul\Product\Filter\ElasticSearch\SkuOrUniversalFilter;

/**
 * Elasticsearch half of the swap: the same filter core uses for the product
 * grid's "all" search box, with the field list taken from this package's
 * settings instead of the literal ['sku', 'name'] the grid hands in.
 *
 * Nothing else changes - clause building, escaping and the text/keyword split
 * all stay with the parent, so a core fix to any of them applies here too.
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
        array $options = []
    ): static {
        return parent::applyUnfilteredFilter($this->searchableFields($fields), $operator, $value, $options);
    }
}
