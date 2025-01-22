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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->bigInteger('creditor')->default(0);
            $table->bigInteger('debtor')->default(0);
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->foreignId('financial_period_id')->constrained('financial_periods')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
