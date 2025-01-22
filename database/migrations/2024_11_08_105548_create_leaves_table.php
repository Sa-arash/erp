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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('typeleave_id')->constrained('typeleaves')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamp('start_leave');
            $table->timestamp('end_leave')->nullable();
            $table->integer('days');
            $table->text('document')->nullable();
            $table->text('description')->nullable();
            $table->enum('status',['pending','rejected','accepted'])->default('pending');
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
