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
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type',['vendor','customer','both',"employee"]);
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('status',['Green','Red','Gray'])->default('Green');
            $table->string('account_number')->nullable();
            $table->string('account_code_vendor')->nullable();
            $table->string('account_code_customer')->nullable();
            $table->foreignId('account_vendor')->nullable()->constrained('accounts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('account_customer')->nullable()->constrained('accounts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
