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
        Schema::create('factor_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factor_id')->constrained('factors')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('title');
            $table->integer('quantity');
            $table->foreignId('unit_id')->constrained('units')->cascadeOnUpdate()->cascadeOnDelete();
            $table->bigInteger('unit_price');
            $table->integer('discount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factor_items');
    }
};
