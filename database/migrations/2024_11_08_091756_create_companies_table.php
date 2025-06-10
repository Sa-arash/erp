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
            $table->string('title', 120);
            $table->string('country');
            $table->string('address')->nullable();
            $table->string('contact_information')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->text('company_registration_document')->nullable();
            $table->float('overtime_rate')->default(1);
            $table->string('daily_working_hours')->nullable();
            $table->string('weekend_days')->nullable();
            $table->integer('account_bank')->nullable();
            $table->integer('account_cash')->nullable();
            $table->integer('vendor_account')->nullable();
            $table->integer('customer_account')->nullable();
            $table->integer('category_account')->nullable();
            $table->text('product_accounts')->nullable();
            $table->text('product_expence_accounts')->nullable();
            $table->text('product_service_accounts')->nullable();
            $table->integer('warehouse_id')->nullable();
            $table->integer('structure_asset_id')->nullable();
            $table->integer('security_id')->nullable();
            $table->string('title_security')->nullable();
            $table->string('logo_security')->nullable();
            $table->string('agency')->nullable();
            $table->string('visitrequest_model')->nullable();
            $table->string('visitrequest_color')->nullable();
            $table->string('SOP_number')->nullable();
            $table->string('description_security')->nullable();
            $table->string('effective_date_security')->nullable();
            $table->string('supersedes_security')->nullable();
            $table->string('stamp_finance')->nullable();
            $table->string('signature_finance')->nullable();
            $table->text('asset_types')->nullable();
            $table->text('asset_depreciation_years')->nullable();
            $table->text('person_grope')->nullable();
            $table->text('asset_qualities')->nullable();
            $table->text('warehouse_type')->nullable();
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
