<?php

namespace Astek\SearchableFields\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSearchableFieldsRequest extends FormRequest
{
    /**
     * Access is decided by UnoPIM's Bouncer against this package's Config/acl.php
     * entry for `admin.settings.searchable_fields.store`, which runs in the
     * `admin` route middleware before this request is validated.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $types = (array) config('searchable_fields.attribute_types', ['text', 'textarea']);

        return [
            /*
             * A posted code must be an attribute that actually exists and whose
             * type the picker allows. Without this, any string reaches the search
             * configuration, where an unknown code is silently skipped by both
             * filters and an image or boolean attribute produces clauses that can
             * never match what a person types.
             */
            'fields'    => ['sometimes', 'array'],
            'fields.*'  => ['string', Rule::exists('attributes', 'code')->whereIn('type', $types)],

            /*
             * The codes rendered on the submitted page. They decide which stored
             * codes this submit is allowed to remove.
             */
            'visible'   => ['sometimes', 'array'],
            'visible.*' => ['string'],
        ];
    }
}
