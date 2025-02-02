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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('request_date');
            $table->enum('type',['On-site Service','Purchase Order','TakeOut For Reaper'])->nullable();
            $table->enum('status',['Pending','Complete','In Progress','Canceled'])->default('Pending');
            $table->text('images')->nullable();
            $table->date('answer_date')->nullable();
            $table->date('service_date')->nullable();
            $table->text('note')->nullable();
            $table->text('reply')->nullable();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('PO_number')->nullable();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
