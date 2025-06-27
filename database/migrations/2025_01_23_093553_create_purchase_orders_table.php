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
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('finance_id')->nullable()->constrained('employees')->nullOnDelete();

            $table->date('date_of_delivery')->nullable();
            $table->string('location_of_delivery', 255)->nullable();
            $table->string('purchase_orders_number')->unique();
            $table->date('date_of_po');
            $table->enum('status', [
                'GRN',
                'Asset & Inventory',
                'Inventory',
                'Asset',
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
