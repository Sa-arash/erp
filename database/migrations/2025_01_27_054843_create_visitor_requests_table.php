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
        Schema::create('visitor_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->date('visit_date');
            $table->time('arrival_time');
            $table->time('departure_time');
            $table->text('purpose');
            $table->json('visitors_detail')->nullable();
            $table->string('driver_vehicle_detail')->nullable();
            $table->date('approval_date')->nullable();
            $table->date('valid_until');
            $table->enum('status', ['approved', 'notApproved'])->default('notApproved');
            $table->timestamps();
                

            $table->foreignId('requested_by')->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('approved_by')->nullable()->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_requests');
    }
};
