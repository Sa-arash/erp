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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type',['creditor','debtor']);
            $table->string('stamp')->nullable();
            $table->string('code');
            $table->enum('level',['main','group','general','subsidiary','detail'])->default('main');
            $table->enum('group',['Asset','Liabilitie','Equity','Income','Expense']);
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->boolean('built_in')->default(0);
            $table->text('description')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['group','code', 'company_id'], 'unique_code_company');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
