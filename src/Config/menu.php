<?php

return [
    /**
     * A search-behaviour switch is a setting, so it hangs under Settings rather
     * than opening a sixth root item in the sidebar. `name` is a translation key:
     * UnoPIM passes every menu label through trans() (Webkul\Core\Tree::create()).
     */
    [
        'key'   => 'settings.searchable_fields',
        'name'  => 'searchable-fields::app.menu.searchable-fields',
        'route' => 'admin.settings.searchable_fields.index',
        'sort'  => 9,
        'icon'  => '',
    ],
];
