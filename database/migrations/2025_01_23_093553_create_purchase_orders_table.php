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
            // $table->string('vendor_contact', 100);
            // $table->text('vendor_address'); 
            $table->enum('payment_type', ['Cheque','Cash','BankTransfer','Other']);
            $table->date('date_of_delivery'); 
            $table->string('location_of_delivery', 255); 
            $table->string('project_and_exp_code', 100);
            $table->string('po_no', 50);
            $table->string('purchase_orders_number')->unique();
            $table->string('currency', 10);
            $table->decimal('exchange_rate', 10, 2)->nullable(); 
            $table->date('date_of_po');
            $table->enum('status', [
                'pending',
                'approved',
            ])->default('pending');
            
            $table->foreignId('prepared_by')->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('checked_by_finance')->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('approved_by')->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreignId('bid_id')->constrained('bids')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete()->cascadeOnUpdate();

            // $table->boolean('quotations')->default(false); 
            // $table->boolean('bid_summary')->default(false);
            // $table->boolean('request_form')->default(false); 



            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete()->cascadeOnUpdate();
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
