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
        Schema::create('grns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('company_id')->constrained('companies')->nullOnDelete();
            $table->foreignId('manager_id')->constrained('employees')->nullOnDelete();
            $table->string('number');
            $table->dateTime('received_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grns');
    }
};
