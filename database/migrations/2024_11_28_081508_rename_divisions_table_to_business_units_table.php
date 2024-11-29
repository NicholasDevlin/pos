<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('divisions', 'business_units');

        Schema::table('business_units', function (Blueprint $table) {
            $table->dropIndex('divisions_name_unique');
            $table->unique('name');

            $table->dropIndex('divisions_real_name_unique');
            $table->unique('real_name');

            $table->dropIndex('divisions_short_name_unique');
            $table->unique('short_name');

            $table->dropIndex('divisions_code_name_unique');
            $table->unique('code_name');
        });

        Schema::table('model_has_scopes', function (Blueprint $table) {
            $table->dropUnique('model_has_scopes_model_location_id_division_id_unique');
            $table->renameColumn('division_id', 'business_unit_id');

            $table->unique(['model_type', 'model_id', 'location_id', 'business_unit_id'], 'model_has_scopes_model_location_id_business_unit_id_unique');
        });

        DB::statement('ALTER TABLE `model_has_scopes` RENAME INDEX `model_has_scopes_division_id_foreign` TO `model_has_scopes_business_unit_id_foreign`');

        DB::statement(<<<QUERY
UPDATE activity_log
SET properties = REPLACE(properties, 'division_id', 'business_unit_id')
WHERE properties LIKE '%division_id%'
QUERY);

        Activity::query()
            ->where('subject_type', 'App\Models\UserManagement\Division')
            ->update(['subject_type' => 'App\Models\UserManagement\BusinessUnit']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Activity::query()
            ->where('subject_type', 'App\Models\UserManagement\BusinessUnit')
            ->update(['subject_type' => 'App\Models\UserManagement\Division']);

        DB::statement(<<<QUERY
UPDATE activity_log
SET properties = REPLACE(properties, 'business_unit_id', 'division_id')
WHERE properties LIKE '%business_unit_id%'
QUERY);

        DB::statement('ALTER TABLE `model_has_scopes` RENAME INDEX `model_has_scopes_business_unit_id_foreign` TO `model_has_scopes_division_id_foreign`');

        Schema::table('model_has_scopes', function (Blueprint $table) {
            $table->dropUnique('model_has_scopes_model_location_id_business_unit_id_unique');
            $table->renameColumn('business_unit_id', 'division_id');

            $table->unique(['model_type', 'model_id', 'location_id', 'division_id'], 'model_has_scopes_model_location_id_division_id_unique');
        });

        Schema::rename('business_units', 'divisions');

        Schema::table('divisions', function (Blueprint $table) {
            $table->dropIndex('business_units_name_unique');
            $table->unique('name');

            $table->dropIndex('business_units_real_name_unique');
            $table->unique('real_name');

            $table->dropIndex('business_units_short_name_unique');
            $table->unique('short_name');

            $table->dropIndex('business_units_code_name_unique');
            $table->unique('code_name');
        });
    }
};
