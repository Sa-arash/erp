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
        Schema::create('asset_employee_items', function (Blueprint $table) {
            $table->id();
            $table->timestamp('due_date')->nullable();
            $table->foreignId('asset_employee_id')->constrained('asset_employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('description')->nullable();
            $table->timestamp('return_date')->nullable();
            $table->enum('type',['Gate Pass','Assigned', 'Returned','Transaction']);
            $table->timestamp('return_approval_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_employee_items');
    }
};
