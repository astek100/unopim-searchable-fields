<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Deliberately seeds nothing.
     *
     * An empty table means "not configured", and the filters then fall back to the
     * field list core passes in (sku and name). Installing this package therefore
     * changes no search result until an admin ticks something, and uninstalling it
     * loses nothing.
     */
    public function up(): void
    {
        Schema::create('unopim_searchable_fields', function (Blueprint $table): void {
            $table->id();
            $table->string('attribute_code')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unopim_searchable_fields');
    }
};
