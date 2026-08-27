<?php

use App\Models\Transaction\Sale;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained();
            $table->string('customer_name');
            $table->string('customer_address');
            $table->char('customer_tier', 1);
            $table->string('series_number');

            $table->integer('discount_percentage')->nullable();
            $table->bigInteger('discount_nominal')->nullable();
            $table->integer('tax_percentage')->nullable();
            $table->decimal('grand_total', 18, 2);

            $table->text('notes')->nullable();
            $table->char('status', 1)->default(Sale::STATUS_NEW);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')->constrained();
            $table->foreignId('product_uom_id')->constrained();
            $table->string('product_name');
            $table->string('uom');

            $table->integer('quantity');
            $table->bigInteger('price');
            $table->integer('discount_percentage')->nullable();
            $table->bigInteger('discount_nominal')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
