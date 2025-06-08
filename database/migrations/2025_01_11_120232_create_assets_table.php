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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable();
            $table->string('serial_number', 250)->nullable();
            $table->string('type')->nullable();
            $table->string('manufacturer')->nullable();
            $table->text('description')->nullable();
            $table->text('note')->nullable();
            $table->string('po_number')->nullable();
            $table->string('market_value')->nullable();
            $table->string('scrap_value')->nullable();
            $table->string('model', 250)->nullable();
            $table->string('quality')->nullable();
            $table->decimal('price', 20, 2)->nullable();
            $table->date('buy_date')->nullable();
            $table->date('warranty_date')->nullable();
            $table->date('guarantee_date')->nullable();
            $table->unsignedTinyInteger('depreciation_years')->default(1);
            $table->decimal('depreciation_amount', 15, 2)->nullable();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('status', ['inuse', 'inStorageUsable', 'storageUnUsable', 'underRepair', 'outForRepair', 'loanedOut'])->default('inStorageUsable');
            $table->text('attributes')->nullable();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('check_out_to')->nullable()->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('check_out_person')->nullable()->constrained('persons')->nullOnDelete();
            $table->foreignId('party_id')->nullable()->constrained('parties')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('structure_id')->constrained('structures')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
