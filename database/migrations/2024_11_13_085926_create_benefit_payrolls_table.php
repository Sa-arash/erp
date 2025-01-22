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
        Schema::create('benefit_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnUpdate()->cascadeOnDelete();
            $table->bigInteger('amount')->default(0);
            $table->float('percent')->default(0);
            $table->foreignId('benefit_id')->constrained('benefits')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benfit_payrolls');
    }
};
