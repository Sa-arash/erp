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
        $table->dateTime('request_date');
        $table->date('end_date')->nullable();
        $table->string('purchase_number')->unique();
        $table->text('description')->nullable();
        $table->boolean('is_quotation')->default(0);
        $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete()->cascadeOnUpdate();

        $table->enum('status', [
            'Requested',
            'Clarification',
            'Verification',
            'Approval',
            'Finished',
            'Rejected',
        ])->default('Requested');
        $table->enum('priority_level',['Low' , 'Medium','High'])->nullable();
        $table->boolean('need_change')->default(0);

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
