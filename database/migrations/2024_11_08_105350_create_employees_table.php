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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('fullName');
            $table->string('NIC')->nullable()   ;
            $table->string('email');
            $table->text('emergency_contact')->nullable();
            $table->string('ID_number');
            $table->enum('type_of_ID',['New','Renewal','Mutilated','Loss','Theft'])->nullable();
            $table->enum('card_status',['National Staff' => 'National Staff', 'International Staff' => 'International Staff', 'National Contractor' => 'National Contractor', 'International Contractor' => 'International Contractor', 'VIP' => 'VIP', 'International Resident' => 'International Resident'])->nullable();
            $table->text('immunization')->nullable();
            $table->boolean('covid_vaccine_certificate')->nullable();
            $table->string('phone_number');
            $table->boolean('has_bank')->default(0);
            $table->date('birthday')->nullable();
            $table->timestamp('joining_date')->nullable();
            $table->timestamp('leave_date')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('address2')->nullable();
            $table->string('post_code',100)->nullable();
            $table->foreignId('duty_id')->constrained('duties')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('cart')->nullable();
            $table->string('bank')->nullable();
            $table->string('tin')->nullable();
            $table->string('branch')->nullable();
            $table->bigInteger('base_salary')->nullable()->default(0);
            $table->bigInteger('daily_salary')->nullable()->default(0);
            $table->bigInteger('benefit_salary')->default(0);
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('gender',['male','female','other']);
            $table->enum('marriage',['divorced','widowed','married','single'])->nullable();
            $table->integer('count_of_child')->nullable()->default(0);
            $table->string('emergency_phone_number')->nullable();
            $table->string('pic')->nullable();
            $table->string('signature_pic')->nullable();
            $table->bigInteger('warehouse_id')->nullable();
            $table->bigInteger('structure_id')->nullable();
            $table->string('blood_group')->nullable();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
