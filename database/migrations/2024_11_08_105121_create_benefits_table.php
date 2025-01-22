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
        Schema::create('benefits', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->boolean('price_type')->default(0); // 1 درصدی
            $table->integer('percent')->nullable();
            $table->integer('amount')->nullable();
            $table->enum('type',['allowance','deduction']);
            $table->enum('on_change',['base_salary','gross']);
            $table->boolean('built_in')->default(0);
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['title', 'company_id'], 'unique_title_company');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benefits');
    }
};
