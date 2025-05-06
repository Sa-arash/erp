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
            $table->foreignId('urgent_typeleave_id')->constrained('urgent_typeleaves')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamp('time_out');
            $table->timestamp('time_in')->nullable();
            $table->integer('hours');
            $table->timestamp('date')->nullable();



            $table->text('document')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status',['pending','rejected','accepted','approveHead'])->default('pending');
            $table->text('comment')->nullable();
            $table->timestamp('approval_date')->nullable();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
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
