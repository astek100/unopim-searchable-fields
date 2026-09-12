<?php

return [
    /**
     * Without an entry here UnoPIM's Bouncer waves the route through for every
     * admin role (it only enforces routes it knows), so any restricted role could
     * change global product search. Both routes are listed: the page and the save.
     */
    [
        'key'   => 'settings.searchable_fields',
        'name'  => 'searchable-fields::app.acl.searchable-fields',
        'route' => 'admin.settings.searchable_fields.index',
        'sort'  => 9,
    ], [
        'key'   => 'settings.searchable_fields.edit',
        'name'  => 'searchable-fields::app.acl.edit',
        'route' => 'admin.settings.searchable_fields.store',
        'sort'  => 1,
    ],
];
