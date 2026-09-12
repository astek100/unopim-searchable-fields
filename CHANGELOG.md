# Changelog

All notable changes to this package are documented here. The format is based on
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this package
follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Settings -> Searchable Fields screen: pick the attributes the product grid
  search box searches, with a filter box, pagination and a configurable limit.
- ACL entries for viewing and for editing the setting.
- `searchable-fields:check` command to verify the wiring after a core upgrade.
- English and German translations.
- Configuration file (`searchable_fields.php`) for the allowed attribute types,
  the field limit and cache behaviour.

### Before tagging the first release

- Replace the placeholder vendor name, `authors`, `homepage` and `support` in
  `composer.json`, and add `docs/screenshot-settings.png`.
- The package name, PHP namespace, database table, route names and artisan
  command are public API; changing them after a tagged release is a breaking
  change.
