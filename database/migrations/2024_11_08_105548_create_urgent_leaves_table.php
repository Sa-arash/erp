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
        Schema::create('urgent_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->time('time_out');
            $table->time('time_in')->nullable();
            $table->integer('hours')->nullable();
            $table->timestamp('date')->nullable();

            $table->text('reason')->nullable();
            $table->enum('status',['pending','rejected','accepted','approveHead'])->default('pending');
            $table->text('comment')->nullable();
            $table->timestamp('approval_date')->nullable();
            $table->time('checkOUT')->nullable();
            $table->time('checkIN')->nullable();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
