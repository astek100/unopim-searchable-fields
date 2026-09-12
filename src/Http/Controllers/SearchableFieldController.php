<?php

namespace Astek\SearchableFields\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Astek\SearchableFields\Http\Requests\UpdateSearchableFieldsRequest;
use Astek\SearchableFields\Repositories\SearchableFieldRepository;
use Astek\SearchableFields\Support\FieldSelection;
use Webkul\Attribute\Repositories\AttributeRepository;

class SearchableFieldController extends Controller
{
    public function __construct(
        protected SearchableFieldRepository $searchableFields,
        protected AttributeRepository $attributeRepository,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        /*
         * Filtered by type and paginated in the database: an installation with
         * thousands of attributes would otherwise load and render every one of
         * them as a checkbox.
         */
        $query = $this->attributeRepository->getModel()->newQuery()
            ->select(['code', 'type'])
            ->whereIn('type', (array) config('searchable_fields.attribute_types', ['text', 'textarea']))
            ->orderBy('code');

        if ($search !== '') {
            $query->where('code', 'LIKE', '%'.$search.'%');
        }

        return view('searchable-fields::index', [
            'attributeList' => $query->paginate(50)->withQueryString(),
            'selected'      => $this->searchableFields->codes(),
            'search'        => $search,
            'maxFields'     => (int) config('searchable_fields.max_fields', 10),
        ]);
    }

    public function store(UpdateSearchableFieldsRequest $request): RedirectResponse
    {
        $codes = FieldSelection::merge(
            $this->searchableFields->codes(),
            (array) $request->input('visible', []),
            (array) $request->input('fields', []),
        );

        $max = (int) config('searchable_fields.max_fields', 10);

        if ($max > 0 && count($codes) > $max) {
            return back()->with('error', trans('searchable-fields::app.index.too-many', ['max' => $max]));
        }

        $this->searchableFields->save($codes);

        return redirect()->route('admin.settings.searchable_fields.index')
            ->with('success', $codes === []
                ? trans('searchable-fields::app.index.saved-default')
                : trans('searchable-fields::app.index.saved', ['count' => count($codes)]));
    }
}
