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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_code')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('account_type')->nullable();
            $table->string('currency')->nullable();
            $table->string('iban')->nullable();
            $table->string('swift_code')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('type')->default(0);
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
