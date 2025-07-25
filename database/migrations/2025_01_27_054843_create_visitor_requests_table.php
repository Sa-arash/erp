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
            $table->string('SN_code')->nullable();
            $table->date('visit_date')->nullable();
            $table->text('visiting_dates')->nullable();
            $table->integer('trip')->nullable();
            $table->time('arrival_time');
            $table->time('departure_time');
            $table->boolean('ICON')->default(0);
            $table->string('agency')->nullable();
            $table->text('armed')->nullable();
            $table->text('purpose');
            $table->json('visitors_detail')->nullable();
            $table->json('driver_vehicle_detail')->nullable();
            $table->date('approval_date')->nullable();
            $table->enum('status', ['approved','Pending', 'notApproved'])->default('Pending');
            $table->longText('entry_and_exit')->nullable();
            $table->dateTime('read_at_reception')->nullable();
            $table->foreignId('requested_by')->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('approved_by')->nullable()->constrained('employees')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
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
