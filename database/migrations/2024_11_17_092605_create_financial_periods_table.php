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
        Schema::create('financial_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('start_date')->default(now());
            $table->timestamp('end_date')->default(now());
            $table->enum('status',['Before','During','End'])->default('Before');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_periods');
    }
};
