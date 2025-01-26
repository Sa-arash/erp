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
            $table->decimal('taxes', 10, 2)->nullable();
            $table->decimal('freights', 10, 2)->nullable();
            
            
            
            // $table->string('vendor_name_and_signature', 255);
            // $table->date('vendor_date');
            // $table->boolean('vendor_stamp')->default(false);
            
            
            
            
            
            
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete()->cascadeOnUpdate();
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
