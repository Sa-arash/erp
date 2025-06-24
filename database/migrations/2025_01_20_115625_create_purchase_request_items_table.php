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
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('description')->nullable();
            $table->string('quantity');
            $table->float('estimated_unit_cost')->nullable();
            $table->text('clarification_comment')->nullable();
            $table->text('verification_comment')->nullable();
            $table->text('approval_comment')->nullable();

            $table->enum('clarification_decision', [
                'approve',
                'reject',
                'Revise',
            ])->nullable();
            $table->enum('verification_decision', [
                'approve',
                'reject',
                'Revise',
            ])->nullable();
            $table->enum('approval_decision', [
                'approve',
                'reject',
                'Revise',
            ])->nullable();

            $table->enum('status', [
                'pending',
                'rejected',
                'approve',
            ])->default('pending');


            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};
