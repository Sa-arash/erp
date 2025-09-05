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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->string('product_id', 50);
            $table->text('description');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 20, 2);
            $table->decimal('taxes', 10, 2)->nullable();
            $table->decimal('freights', 10, 2)->nullable();
            $table->foreignId('unit_id')->constrained('units')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('exchange_rate',20,2)->nullable();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('purchase_request_item_id')->nullable()->constrained('purchase_request_item')->nullOnDelete();
            $table->enum('receive_status', [
                'Pending',
                'Rejected',
                'Approved',
            ])->default('Pending');
            $table->text('receive_comment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
