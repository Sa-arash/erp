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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('party_id')->constrained('parties')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamp('date');
            $table->text('file')->nullable();
            $table->string('currency')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('employee_operation_id')->nullable()->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
