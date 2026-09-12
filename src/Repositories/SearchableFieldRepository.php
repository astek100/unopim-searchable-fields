<?php

namespace Astek\SearchableFields\Repositories;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Astek\SearchableFields\Support\FieldSelection;

/**
 * The selected attribute codes, kept in a table of this package's own.
 *
 * A dedicated table rather than a column on UnoPIM's `attributes`: core's schema
 * stays untouched, so a core upgrade can neither overwrite nor drop the setting,
 * and uninstalling drops one table and nothing else.
 */
class SearchableFieldRepository
{
    public const TABLE = 'unopim_searchable_fields';

    private const CACHE_KEY = 'unopim.searchable_fields.codes';

    /**
     * Attribute codes the product grid's search box should cover.
     *
     * An empty array means "not configured" and every caller treats it as
     * "behave like core".
     *
     * @return array<int, string>
     */
    public function codes(): array
    {
        $ttl = (int) config('searchable_fields.cache_ttl', 300);

        if ($ttl <= 0) {
            return $this->read();
        }

        return $this->cache()->remember(self::CACHE_KEY, $ttl, fn (): array => $this->read());
    }

    /**
     * Replace the selection.
     *
     * @param  array<int|string, mixed>  $codes
     */
    public function save(array $codes): void
    {
        $codes = FieldSelection::normalize($codes);

        DB::transaction(function () use ($codes): void {
            DB::table(self::TABLE)->delete();

            if ($codes === []) {
                return;
            }

            $now = now();

            DB::table(self::TABLE)->insert(array_map(fn (string $code): array => [
                'attribute_code' => $code,
                'created_at'     => $now,
                'updated_at'     => $now,
            ], $codes));
        });

        $this->flush();
    }

    public function flush(): void
    {
        $this->cache()->forget(self::CACHE_KEY);
    }

    /**
     * @return array<int, string>
     */
    protected function read(): array
    {
        /*
         * The package can be installed before `php artisan migrate` runs, and the
         * grid searches on every request in between. A missing table is therefore
         * a normal state, not an error: report "not configured".
         */
        if (! Schema::hasTable(self::TABLE)) {
            return [];
        }

        return FieldSelection::normalize(
            DB::table(self::TABLE)->orderBy('attribute_code')->pluck('attribute_code')->all()
        );
    }

    protected function cache(): CacheRepository
    {
        return Cache::store(config('searchable_fields.cache_store'));
    }
}
