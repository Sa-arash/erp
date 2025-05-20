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
            $table->date('visit_date');
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
            $table->enum('gate_status',['Pending','CheckedIn','CheckedOut','Canceled'])->default('Pending');
            $table->timestamp('InSide_date')->nullable();
            $table->timestamp('OutSide_date')->nullable();
            $table->text('inSide_comment')->nullable();
            $table->text('OutSide_comment')->nullable();
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
