{{--
    Only utility classes that already occur in UnoPIM's own views are used here.
    The admin theme compiles Tailwind from `packages/*/src/Resources`, which does
    not include a package installed under `vendor/`, so a class that core never
    uses would simply not exist in the stylesheet.
--}}
<x-admin::layouts>
    <x-slot:title>
        @lang('searchable-fields::app.index.title')
    </x-slot>

    @php
        $canEdit = bouncer()->hasPermission('settings.searchable_fields.edit');
    @endphp

    <div class="flex justify-between items-center mb-4">
        <div>
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('searchable-fields::app.index.title')
            </p>

            <p class="text-gray-600 dark:text-gray-300">
                @lang('searchable-fields::app.index.info')
            </p>

            <p class="text-gray-600 dark:text-gray-300">
                @if (empty($selected))
                    @lang('searchable-fields::app.index.selected-none')
                @else
                    @lang('searchable-fields::app.index.selected', ['codes' => implode(', ', $selected)])
                @endif
            </p>

            <p class="text-xs text-gray-400">
                @lang('searchable-fields::app.index.limit', ['max' => $maxFields])
                @lang('searchable-fields::app.index.page-hint')
            </p>
        </div>
    </div>

    <form
        method="GET"
        action="{{ route('admin.settings.searchable_fields.index') }}"
        class="flex items-center gap-2 mb-4"
    >
        <input
            type="text"
            name="search"
            value="{{ $search }}"
            placeholder="@lang('searchable-fields::app.index.search-placeholder')"
            class="w-full rounded-md border bg-white px-3 py-1.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 focus:outline-none dark:border-cherry-800 dark:bg-cherry-800 dark:text-gray-300 dark:hover:border-gray-400"
        >

        <button type="submit" class="secondary-button">
            @lang('searchable-fields::app.index.filter-btn')
        </button>

        @if ($search !== '')
            <a href="{{ route('admin.settings.searchable_fields.index') }}" class="transparent-button">
                @lang('searchable-fields::app.index.reset-btn')
            </a>
        @endif
    </form>

    <form method="POST" action="{{ route('admin.settings.searchable_fields.store') }}">
        @csrf

        @if ($canEdit)
            <div class="flex justify-end mb-4">
                <button type="submit" class="primary-button">
                    @lang('searchable-fields::app.index.save-btn')
                </button>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-1 bg-white dark:bg-gray-900 rounded p-5">
            @forelse ($attributeList as $attribute)
                {{-- Tells the controller which codes this page could change, so a
                     selection made on another page survives this submit. --}}
                <input type="hidden" name="visible[]" value="{{ $attribute->code }}">

                <label class="flex items-center gap-2 py-1 cursor-pointer">
                    <input
                        type="checkbox"
                        name="fields[]"
                        value="{{ $attribute->code }}"
                        @checked(in_array($attribute->code, $selected, true))
                        @disabled(! $canEdit)
                    >

                    <span class="text-gray-800 dark:text-white">{{ $attribute->code }}</span>

                    <span class="text-xs text-gray-400">{{ $attribute->type }}</span>
                </label>
            @empty
                <p class="text-gray-600 dark:text-gray-300">
                    @lang('searchable-fields::app.index.empty')
                </p>
            @endforelse
        </div>

        @if ($attributeList->hasPages())
            <div class="flex justify-between items-center mt-4">
                <p class="text-xs text-gray-400">
                    {{ $attributeList->firstItem() }}-{{ $attributeList->lastItem() }} / {{ $attributeList->total() }}
                </p>

                <div class="flex items-center gap-2">
                    @if (! $attributeList->onFirstPage())
                        <a href="{{ $attributeList->previousPageUrl() }}" class="secondary-button">
                            @lang('searchable-fields::app.index.previous')
                        </a>
                    @endif

                    @if ($attributeList->hasMorePages())
                        <a href="{{ $attributeList->nextPageUrl() }}" class="secondary-button">
                            @lang('searchable-fields::app.index.next')
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </form>
</x-admin::layouts>
