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
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_item_id')->constrained('purchase_request_items')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnUpdate()->cascadeOnDelete();

            $table->float('unit_rate')->nullable();
            $table->decimal('taxes', 10, 2)->nullable();
            $table->decimal('freights', 10, 2)->nullable();
            $table->timestamp('date')->default(now());
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
