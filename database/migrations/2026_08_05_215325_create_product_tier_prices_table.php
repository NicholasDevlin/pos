<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_tier_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_uom_id')->constrained();
            $table->char('tier', 1);
            $table->bigInteger('price');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_tier_prices');
    }
};
