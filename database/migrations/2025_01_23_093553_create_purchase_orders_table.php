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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->cascadeOnDelete()->cascadeOnUpdate();

            $table->date('date_of_delivery')->nullable();
            $table->string('location_of_delivery', 255)->nullable();
            $table->string('purchase_orders_number')->unique();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->decimal('exchange_rate', 50, 8)->nullable();
            $table->date('date_of_po');
            $table->enum('status', [
                'GRN',
                'GRN And inventory',
                'Inventory',
                'pending',
                'Approved', // approve ceo
                 'rejected',
                'Approve Logistic Head', // review
                'Approve Verification', // verified
            ])->default('pending');
            $table->foreignId('prepared_by')->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('bid_id')->nullable()->constrained('bids')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->cascadeOnDelete()->cascadeOnUpdate();
            // $table->boolean('quotations')->default(false);
            // $table->boolean('bid_summary')->default(false);
            // $table->boolean('request_form')->default(false);
            $table->foreignId('purchase_request_id')->nullable()->constrained('purchase_requests')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('vendor_id')->constrained('parties')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();




            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
