<?php

namespace Astek\SearchableFields\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Astek\SearchableFields\Console\CheckSearchableFieldsCommand;
use Astek\SearchableFields\Filters\Database\ConfigurableSkuOrUniversalFilter as DatabaseConfigurableFilter;
use Astek\SearchableFields\Filters\ElasticSearch\ConfigurableSkuOrUniversalFilter as ElasticConfigurableFilter;
use Webkul\Product\Filter\Database\SkuOrUniversalFilter as DatabaseSkuOrUniversalFilter;
use Webkul\Product\Filter\ElasticSearch\SkuOrUniversalFilter as ElasticSkuOrUniversalFilter;

class SearchableFieldsServiceProvider extends ServiceProvider
{
    /**
     * Why this package does not touch ProductDataGrid.
     *
     * The field list the grid searches is a literal inside
     * `Webkul\Admin\DataGrids\Catalog\ProductDataGrid::processFilters()`, and that
     * method is `protected`, so `app()->extend()` on the grid cannot reach it.
     * The obvious alternatives are both worse:
     *
     *  - Subclassing the grid means picking a parent at compile time. Picking
     *    core's class throws away whatever another module bound before us;
     *    picking `Webkul\DAM\DataGrids\Catalog\ProductDataGrid` makes the DAM
     *    module - a separate composer package, not part of UnoPIM core - a hard
     *    requirement, and a missing parent class is a fatal error on the catalogue
     *    screen, not a degraded feature.
     *  - Resolving the parent at runtime (class_alias + class_exists) or rebinding
     *    whatever class is currently bound removes the DAM requirement but keeps
     *    the real problem: exactly one package can own the concrete grid, so the
     *    last binder wins and load order decides whether search or image columns
     *    survive.
     *
     * So the swap happens one layer lower, where there is no competition. The
     * field list is consumed by two leaf filters that core resolves through the
     * container on every search:
     *
     *     FilterManager::getSkuOrUnfilteredFilter()
     *         => resolve(ElasticSearch\SkuOrUniversalFilter::class)   // ES path
     *         => resolve(Database\SkuOrUniversalFilter::class)        // DB path
     *
     * Binding subclasses of those two is enough to change what the box searches,
     * and it is indifferent to who owns ProductDataGrid. It works on a plain
     * UnoPIM, on one with DAM (DAM's grid inherits `processFilters` unchanged and
     * still routes through these filters), alongside ProductPassport's
     * `extend()`-based decoration, and on both the Elasticsearch and database
     * search paths.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/searchable_fields.php', 'searchable_fields');

        $this->app->bind(ElasticSkuOrUniversalFilter::class, ElasticConfigurableFilter::class);

        $this->app->bind(DatabaseSkuOrUniversalFilter::class, DatabaseConfigurableFilter::class);
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/acl.php', 'acl');

        $this->mergeConfigFrom(__DIR__.'/../Config/menu.php', 'menu.admin');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'searchable-fields');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'searchable-fields');

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        Route::middleware('web')->group(__DIR__.'/../Routes/admin.php');

        $this->publishes([
            __DIR__.'/../Config/searchable_fields.php' => config_path('searchable_fields.php'),
        ], 'searchable-fields-config');

        if ($this->app->runningInConsole()) {
            $this->commands([CheckSearchableFieldsCommand::class]);
        }
    }
}
