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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('finance_id')->nullable()->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('loan_code');
            $table->bigInteger('request_amount');
            $table->bigInteger('amount')->nullable();
            $table->integer('number_of_installments')->nullable();
            $table->integer('number_of_payed_installments')->default(0);
            $table->timestamp('request_date');
            $table->timestamp('answer_date')->nullable();
            $table->timestamp('first_installment_due_date')->nullable();
            $table->text('description')->nullable();
            $table->enum('status',['waiting','ApproveManager','ApproveAdmin','ApproveFinance','progressed','rejected','accepted','finished']);
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
