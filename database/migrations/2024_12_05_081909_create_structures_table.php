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
        Schema::create('structures', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->bigInteger('sort')->nullable();
            $table->string('type')->nullable();
            $table->boolean('location')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('structures')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('structures');
    }
};
