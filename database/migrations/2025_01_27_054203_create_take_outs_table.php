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
        Schema::create('take_outs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->date('return_date')->nullable();
            $table->text('itemsOut')->nullable();
            $table->string('to');
            $table->string('from');
            $table->text('reason')->nullable();
            $table->enum('mood',['Approved','Approved Manager','Pending','NotApproved'])->default('Pending');
            $table->enum('status',['Returnable','Non-Returnable']);
            $table->enum('type',['Modification','Personal Belonging','Domestic Waste','Construction Waste']);
            $table->enum('gate_status',['Pending','CheckedIn','CheckedOut','Canceled'])->default('Pending');
            $table->timestamp('InSide_date')->nullable();
            $table->timestamp('OutSide_date')->nullable();
            $table->text('inSide_comment')->nullable();
            $table->text('OutSide_comment')->nullable();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('employees')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('take_outs');
    }
};
