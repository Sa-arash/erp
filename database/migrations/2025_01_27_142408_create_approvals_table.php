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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();

            $table->morphs('approvable');
            $table->dateTime('approve_date')->nullable();
            $table->text('comment')->nullable();
            $table->enum('status', ['approve', 'notApprove', 'pending'])->default('pending');
            
            
            
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
