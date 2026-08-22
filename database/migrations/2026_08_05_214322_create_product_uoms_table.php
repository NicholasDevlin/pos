<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_uoms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained();
            $table->foreignId('units_of_measure_id')->constrained('units_of_measure');
            $table->string('sku')->unique()->nullable();
            $table->integer('conversion_factor')->default(1);
            $table->bigInteger('cost_price')->nullable();
            $table->bigInteger('base_price');
            $table->boolean('is_default')->default(false);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_uoms');
    }
};
