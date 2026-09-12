<?php

return [
    'menu' => [
        'searchable-fields' => 'Searchable Fields',
    ],

    'acl' => [
        'searchable-fields' => 'Searchable Fields',
        'edit'              => 'Edit',
    ],

    'index' => [
        'title'              => 'Searchable Fields',
        'info'               => 'The search box above the product grid searches the fields ticked here. With nothing ticked it behaves exactly like a stock UnoPIM and searches SKU and name only.',
        'save-btn'           => 'Save',
        'search-placeholder' => 'Filter attributes by code',
        'filter-btn'         => 'Filter',
        'reset-btn'          => 'Reset',
        'selected'           => 'Currently searched: :codes',
        'selected-none'      => 'Currently searched: SKU and name (default).',
        'limit'              => 'At most :max fields can be searched at once.',
        'empty'              => 'No attribute of a searchable type matches.',
        'type'               => 'Type',
        'page-hint'          => 'Save before moving to another page: a page saves the boxes it shows.',
        'previous'           => 'Previous',
        'next'               => 'Next',
        'saved'              => 'The search now covers :count field(s).',
        'saved-default'      => 'Selection cleared. The search is back to SKU and name.',
        'too-many'           => 'Not saved: at most :max fields can be searched at once.',
    ],
];
