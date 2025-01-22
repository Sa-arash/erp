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
        $table->string('employee_id');
        $table->string('purchase_number')->unique();
       
        $table->text('description')->nullable();
        $table->enum('status', [
            'requested',
            'warehouse_checked',
            'department_manager_approved',
            'department_manager_rejected',

            'ceo_approved',
            'ceo_rejected',

            'purchased',
            'not_purchased'
        ])->default('requested');


        $table->text('warehouse_comment')->nullable();
        $table->text('department_manager_comment')->nullable();
        $table->text('ceo_comment')->nullable();
        $table->text('general_comment')->nullable();
        $table->date('warehouse_status_date')->nullable();
        $table->date('department_manager_status_date')->nullable();
        $table->date('ceo_status_date')->nullable();

        $table->date('purchase_date')->nullable();
        $table->timestamps();


      

         $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
         $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete()->cascadeOnUpdate();
       
         $table->foreignId('structure_id')->constrained('structures')->cascadeOnDelete()->cascadeOnUpdate();
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
