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
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->string('cheque_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('account_number')->nullable();
            $table->bigInteger('amount');
            $table->timestamp('issue_date')->nullable();
            $table->boolean('type')->default(0);
            $table->timestamp('due_date')->nullable();
            $table->enum('status',['issued','paid','returned','blocked','pending','cancelled','post_dated']);
            $table->string('payer_name')->nullable();
            $table->string('payee_name')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};
