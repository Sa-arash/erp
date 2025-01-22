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
            $table->text('ceo_comment')->nullable();
            $table->enum('ceo_decision', [
                'pending',
                'purchase',
                'approve',
                'reject',
                'assigne',
            ])->default('pending');


            $table->enum('status', [
                'purchased',
                'assigned',
                'pending',
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
