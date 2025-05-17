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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->bigInteger('quantity');
            $table->string('description');
            $table->boolean('type');
            $table->boolean('transaction')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
