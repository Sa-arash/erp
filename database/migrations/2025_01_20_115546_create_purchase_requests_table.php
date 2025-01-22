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
    Schema::create('purchase_requests', function (Blueprint $table) {
        $table->id();
        $table->date('request_date');
        $table->string('purchase_number')->unique();
        $table->text('description')->nullable();
        $table->enum('status', [
            'Requested',
            'FinishedCeo',
            'Finished',
            'Rejected',
        ])->default('Requested');
        $table->text('comment')->nullable();
        $table->timestamps();
         $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
         $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
