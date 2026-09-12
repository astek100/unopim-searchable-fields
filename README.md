# Searchable Fields for UnoPIM

Let an administrator choose **which product attributes the search box above the
product grid actually searches** - GTIN, a supplier reference, an external id -
instead of only SKU and name.

Unofficial community package. Not affiliated with Webkul.

## The problem

UnoPIM's product grid search is hardcoded. In
`Webkul\Admin\DataGrids\Catalog\ProductDataGrid::processFilters()` the "all"
search term is handed to the filter with a literal field list:

```php
$queryBuilder->applySkuOrUnfilteredFilter(['sku', 'name'], FilterOperators::WILDCARD, $value, $context);
```

So typing a barcode or a supplier article number into that box returns nothing,
even though the value is stored on the product and, on an Elasticsearch
installation, already indexed. Measured on a production catalogue in September
2026: querying the index directly for such an identifier returned the product in
well under a second - the interface simply never asks for that field.

There is no configuration for it, and no public seam either: the method holding
the list is `protected`, so `app()->extend()` on the grid cannot reach it.

## What this package does

* Adds **Settings -> Searchable Fields**: a checkbox per attribute, with a
  filter box and pagination.
* Stores the selection in its own table (`unopim_searchable_fields`). UnoPIM's
  own schema is not touched, so a core upgrade cannot overwrite the setting and
  uninstalling drops exactly one table.
* Ships an ACL entry, so the page and the save are permissions like any other.
* Ships a check command for after core upgrades.

**Installing it changes nothing until you tick something.** With an empty
selection the search behaves exactly like a stock UnoPIM (SKU and name).

## Screenshot

<!-- TODO: add docs/screenshot-settings.png before the first release. -->
![The Searchable Fields settings screen](docs/screenshot-settings.png)

## Requirements

* UnoPIM 3.x - specifically a version that has
  `Webkul\Product\Filter\ElasticSearch\SkuOrUniversalFilter` and
  `Webkul\Product\Filter\Database\SkuOrUniversalFilter`, which is what this
  package extends.
* PHP 8.2+ (UnoPIM 3 itself requires 8.4).
* Works with and without Elasticsearch, and with or without the separate
  `unopim/dam` module.

`unopim/unopim` is deliberately **not** in `composer.json`'s `require`: UnoPIM's
root `composer.json` declares no `version`, so a constraint like
`"unopim/unopim": "^3.0"` fails to resolve on any installation that is not a
tagged git checkout. The dependency is documented here and verified at runtime
by `php artisan searchable-fields:check` instead.

## Installation

```bash
composer require astek100/unopim-searchable-fields
php artisan migrate
php artisan cache:clear
```

Then open **Settings -> Searchable Fields**, tick the attributes the search box
should cover, and save. Grant the `settings.searchable_fields` and
`settings.searchable_fields.edit` permissions to the roles that should see and
change it.

Optional, to change the defaults (allowed attribute types, field limit, cache):

```bash
php artisan vendor:publish --tag=searchable-fields-config
```

## How it works, and why it does not replace ProductDataGrid

The obvious implementation is to subclass `ProductDataGrid` and override
`processFilters()`. Do not: only one package can own that binding.

* Subclassing core's grid throws away whatever another module bound before you.
* Subclassing `Webkul\DAM\DataGrids\Catalog\ProductDataGrid` makes the DAM
  module a hard requirement. DAM is **not** part of UnoPIM core - it is a
  separate composer package - so on an installation without it the missing
  parent class is a PHP fatal error on the catalogue screen, not a missing
  feature.
* Resolving the parent at runtime, or rebinding whatever is currently bound,
  removes the DAM requirement but keeps the real problem: the last package to
  bind wins, and load order decides whether your search or someone's image
  column survives.

This package therefore swaps nothing at grid level. The field list is consumed
one layer lower, by two leaf filters that core resolves through the container on
every search:

```php
// Webkul\Product\Filter\FilterManager::getSkuOrUnfilteredFilter()
return config('elasticsearch.enabled')
    ? resolve(ElasticSearch\SkuOrUniversalFilter::class)
    : resolve(Database\SkuOrUniversalFilter::class);
```

Binding a subclass of each is enough to change what the box searches, and it is
indifferent to who owns `ProductDataGrid`. It works on a plain UnoPIM, on one
with DAM (whose grid inherits `processFilters` unchanged), next to
ProductPassport's `extend()`-based decoration, and on both search paths. Each
subclass overrides one method, substitutes the field list and delegates
everything else - clause building, escaping, the text/keyword split - to core.

When nothing is configured, the filters fall back to the list core passed in, so
an installed-but-unconfigured package is a genuine no-op.

## Relationship to the core patch

The clean fix belongs upstream: turn that literal into a seam - an overridable
`protected function searchableFields(): array`, or
`config('unopim.datagrid.product.search_fields', ['sku', 'name'])` - so that no
package has to subclass anything to change it. A pull request doing that is the
companion to this package.

If it lands, this package keeps working unchanged and can later drop both filter
subclasses, becoming pure configuration plus UI. Until then the container swap
above is the only construction that does not fight other modules.

## After a core upgrade

```bash
php artisan searchable-fields:check
```

It reports which search path is active, which filter class is actually resolved,
whether the wiring still holds, and whether every configured attribute code
still exists (both core filters skip unknown codes silently). It is quiet and
exits 0 on a healthy installation - including one deliberately left at the
default - so it is safe to run from cron.

## Configuration

`config/searchable_fields.php`:

| Key | Default | Meaning |
| --- | --- | --- |
| `attribute_types` | `['text', 'textarea']` | Attribute types offered in the picker. Types whose stored value is an id, a path or a boolean cannot match typed text. |
| `max_fields` | `10` | Upper bound on selected fields. Each one is an extra OR clause per search; Elasticsearch caps clause expansion. |
| `cache_ttl` | `300` | Seconds the selection is cached. `0` disables caching. |
| `cache_store` | `null` | Cache store for that entry. Point it at a shared store (e.g. redis) if your web and queue containers do not share the default one, otherwise a saved change is only visible in the process that saved it until the TTL expires. |

## Translations

English (`en_US`) and German (`de_DE`) are included, under the
`searchable-fields::` namespace, following UnoPIM's package layout
(`src/Resources/lang/<locale>/app.php`). Pull requests with more locales are
welcome; a missing locale falls back to the application's fallback locale.

A note on styling: the admin theme compiles Tailwind from
`packages/*/src/Resources` only, which does not include a package installed
under `vendor/`. The views here therefore use only utility classes core already
uses, plus the theme's own `primary-button` / `secondary-button` classes. Keep
to that vocabulary when changing them.

## Uninstalling

```bash
php artisan migrate:rollback --path=vendor/astek100/unopim-searchable-fields/src/Database/Migrations
composer remove astek100/unopim-searchable-fields
```

The search returns to SKU and name. Nothing else is left behind.

## A note on the name

This package began as an in-house patch on a single installation, carrying that
company's name in the vendor, the PHP namespace, the database table, the cache
key, the routes and the artisan command, with German identifiers and
German-only strings. All of it was renamed before publication: every one of
those names is public API, and renaming after a first release would be a
breaking change for everyone who had installed it. The seeded defaults from that
installation - its own attribute codes - were dropped for the same reason: they
exist nowhere else and would have been silently dead configuration on any other
catalogue.

This package is the general-purpose extraction. The related upstream proposal --
making the very same field list a core configuration key so no package is needed
for the common case -- is [unopim/unopim#702](https://github.com/unopim/unopim/issues/702)
with the patch in [unopim/unopim#703](https://github.com/unopim/unopim/pull/703).
If that lands, this package remains useful for what it adds on top: the admin
screen, so the list is changed by ticking boxes rather than by editing a file on
the server.

## Authorship

Written by Claude Opus 5 (Anthropic) against a production UnoPIM 3.1.0
installation, and reviewed there before publication. Commits carry a
`Co-Authored-By` trailer. Treat it as you would any unfamiliar package: read the
source before installing it on a catalogue you care about.

## License

MIT. See [LICENSE](LICENSE).
