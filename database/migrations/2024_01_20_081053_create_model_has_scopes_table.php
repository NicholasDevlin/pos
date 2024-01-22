<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('model_has_scopes', function (Blueprint $table) {
            $table->morphs('model');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->foreignId('division_id')->constrained()->onDelete('cascade');

            $table->unique(['model_type', 'model_id', 'location_id', 'division_id'], 'model_has_scopes_model_location_id_division_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_scopes');
    }
};
