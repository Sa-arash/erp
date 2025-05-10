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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete()->cascadeOnUpdate();
            $table->dateTime('start_date');
            $table->dateTime('start_task')->nullable();
            $table->dateTime('end_task')->nullable();
            $table->dateTime('deadline');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority_level',['Low','Medium','High']);
            $table->enum('status',['Processing','Completed','Canceled'])->default('Processing');
            $table->text('document')->nullable();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
