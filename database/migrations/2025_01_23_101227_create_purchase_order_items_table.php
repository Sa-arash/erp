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
            $table->string('sku', 50);
            $table->text('item_description');
            $table->string('unit', 50);
            $table->integer('qty');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('taxes', 10, 2)->nullable();
            $table->decimal('freights', 10, 2)->nullable();

            $table->string('payment_type', 50);
            $table->string('prepared_by_logistic', 100);
            $table->string('checked_by_finance', 100);
            $table->string('approved_by', 100);
            $table->string('vendor_name_and_signature', 255);
            $table->date('vendor_date');
            $table->boolean('vendor_stamp')->default(false);
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
