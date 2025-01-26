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
        Schema::create('separations', function (Blueprint $table) {
            $table->id();
            $table->text('comments_signature')->nullable();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('approved_by')->nullable()->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('attested_by')->nullable()->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('date');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('separations');
    }
};
