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
        Schema::create('asset_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('person')->nullable();
            $table->timestamp('date');
            $table->timestamp('approve_date')->nullable();
            $table->text('description')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('type', ['GatePass','Assigned', 'Returned','Transaction']);
            $table->enum('status', ['Pending', 'Approve','Reject'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_employees');
    }
};
