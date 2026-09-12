<?php

use Illuminate\Support\Facades\Route;
use Astek\SearchableFields\Http\Controllers\SearchableFieldController;

Route::group(['middleware' => ['admin'], 'prefix' => config('app.admin_url')], function (): void {
    Route::controller(SearchableFieldController::class)->prefix('settings/searchable-fields')->group(function (): void {
        Route::get('', 'index')->name('admin.settings.searchable_fields.index');
        Route::post('', 'store')->name('admin.settings.searchable_fields.store');
    });
});
