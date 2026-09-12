<?php

namespace Astek\SearchableFields\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Astek\SearchableFields\Repositories\SearchableFieldRepository;
use Astek\SearchableFields\Support\ResolvesSearchableFields;
use Webkul\Attribute\Contracts\Attribute;
use Webkul\Attribute\Services\AttributeService;
use Webkul\Product\Filter\FilterManager;

/**
 * Reports whether the configured search is still wired into UnoPIM.
 *
 * Two things can break it without anyone noticing: a core release that changes
 * how the search filter is resolved, and a configured attribute code that no
 * longer exists (both core filters skip unknown codes in silence). Run it after
 * every core upgrade. It is quiet and returns 0 on a healthy installation, so it
 * is safe to run from cron.
 */
class CheckSearchableFieldsCommand extends Command
{
    protected $signature = 'searchable-fields:check';

    protected $description = 'Check that the configurable product search is still wired into UnoPIM';

    public function handle(SearchableFieldRepository $repository, AttributeService $attributeService): int
    {
        $failures = [];
        $warnings = [];

        $this->line('Search path: '.(config('elasticsearch.enabled') ? 'Elasticsearch' : 'database'));

        /*
         * Resolve the filter the way the product grid does, through the core
         * manager, so this checks the real wiring instead of a copy of it.
         */
        if (! method_exists(FilterManager::class, 'getSkuOrUnfilteredFilter')) {
            $failures[] = 'Webkul\Product\Filter\FilterManager::getSkuOrUnfilteredFilter() is gone - this UnoPIM release resolves the search filter differently.';
        } else {
            $filter = resolve(FilterManager::class)->getSkuOrUnfilteredFilter();

            $this->line('Resolved search filter: '.$filter::class);

            if (! in_array(ResolvesSearchableFields::class, class_uses_recursive($filter), true)) {
                $failures[] = 'The resolved search filter is not the one shipped by this package, so the field selection is ignored. Either another package rebound it, or this provider did not load.';
            }

            if (! method_exists($filter, 'applyUnfilteredFilter')) {
                $failures[] = 'The resolved filter has no applyUnfilteredFilter() method - the core filter contract changed.';
            }
        }

        if (! Schema::hasTable(SearchableFieldRepository::TABLE)) {
            $failures[] = 'Table '.SearchableFieldRepository::TABLE.' is missing - run `php artisan migrate`.';
        }

        $codes = $repository->codes();

        if ($codes === []) {
            /*
             * Not a failure: an empty selection means the search behaves exactly
             * like stock UnoPIM. A cron on a healthy installation stays quiet.
             */
            $this->line('Configured fields: none (search falls back to core behaviour: sku, name)');
        } else {
            $this->line('Configured fields: '.implode(', ', $codes));

            foreach ($codes as $code) {
                if (! $attributeService->findAttributeByCode($code) instanceof Attribute) {
                    $warnings[] = "Configured field '{$code}' matches no attribute and is silently ignored by the search.";
                }
            }
        }

        foreach ($warnings as $warning) {
            $this->warn('WARNING: '.$warning);
        }

        foreach ($failures as $failure) {
            $this->error('FAILED: '.$failure);
        }

        if ($failures !== []) {
            return self::FAILURE;
        }

        $this->info('Searchable fields are wired in correctly.');

        return self::SUCCESS;
    }
}
