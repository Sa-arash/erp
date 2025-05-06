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
        Schema::create('typeleaves', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('days');
            $table->boolean('is_payroll');
            $table->string('description')->nullable();
            $table->boolean('built_in')->default(0);
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['title', 'company_id'], 'unique_title_company');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('typeleaves');
    }
};
