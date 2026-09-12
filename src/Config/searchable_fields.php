<?php

return [
    /**
     * Attribute types an admin may tick as searchable.
     *
     * The "all" search builds one clause per field: a `match_phrase_prefix` for
     * text/textarea on Elasticsearch and a `LIKE` on the database path. Types
     * whose stored value is an id, a file path or a boolean produce clauses that
     * can never match what a person types, so they are kept out of the picker.
     * Widen this list only if you know the stored representation is searchable.
     */
    'attribute_types' => ['text', 'textarea'],

    /**
     * Upper bound on how many fields may be searched at once.
     *
     * Every extra field is an extra OR clause on every keystroke-sized query.
     * Elasticsearch caps internal clause expansion (`too_many_clauses`) and the
     * database path adds one `LOWER(...) LIKE` per field over the products table.
     */
    'max_fields' => 10,

    /**
     * Cache the selected field list for this many seconds. 0 disables caching.
     */
    'cache_ttl' => 300,

    /**
     * Cache store used for that list. null = the application default store.
     *
     * If your web and queue containers run separate cache stores (e.g. the `file`
     * or `array` driver inside each image), point this at a shared store such as
     * redis, otherwise a change saved in the web container stays invisible to the
     * others until the TTL expires.
     */
    'cache_store' => null,
];
