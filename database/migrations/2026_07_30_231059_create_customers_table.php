<?php

use App\Models\MasterData\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address');
            $table->char('tier', 1);
            $table->text('notes')->nullable();
            $table->char('status', 1)->default(Customer::STATUS_ACTIVE);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
