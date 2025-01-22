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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('title',120);
            $table->string('country');
            $table->string('address')->nullable();
            $table->string('contact_information')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->text('company_registration_document')->nullable();
            $table->string('currency');
            $table->float('overtime_rate')->default(1);
            $table->string('daily_working_hours')->nullable();
            $table->string('weekend_days')->nullable();
            $table->integer('account_bank')->nullable();
            $table->integer('vendor_account')->nullable();
            $table->integer('customer_account')->nullable();
            $table->integer('category_account')->nullable();
//          $table->text('company_type');
            $table->text('logo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
