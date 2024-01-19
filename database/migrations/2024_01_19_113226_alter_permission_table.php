<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->text('notes')->after('name')->nullable();
        });

        Permission::create(['name' => 'logs.show.all', 'notes' => 'Show all logs.']);
        Permission::create(['name' => 'logs.show.scope', 'notes' => 'Show scope logs.']);
        Permission::create(['name' => 'logs.show.own', 'notes' => 'Show own logs.']);

        Schema::table('roles', function (Blueprint $table) {
            $table->text('notes')->after('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        Permission::whereIn('name', ['logs.show.all', 'logs.show.scope', 'logs.show.own'])
            ->delete();

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
