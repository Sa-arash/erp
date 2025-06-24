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
        Schema::create('take_out_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('take_out_id')->constrained('take_outs')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('remarks')->nullable();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('returned_date')->nullable();
            $table->enum('status',['Pending','Approved','Not Approved'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('take_out_items');
    }
};
