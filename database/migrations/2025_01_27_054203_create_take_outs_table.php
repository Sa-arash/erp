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
        Schema::create('take_outs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('to');
            $table->string('from');
            $table->text('reason')->nullable();
            $table->enum('status',['Returnable','Non-Returnable']);
            $table->enum('type',['Modification','Personal Belonging','Domestic Waste','Construction Waste']);
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('head_department_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('take_outs');
    }
};
