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
    {Schema::create('company_budgets', function (Blueprint $table) {
            $table->id();
            $table->string('company');
            $table->integer('month');
            $table->integer('year');
            $table->bigInteger('budget_amount');
            $table->timestamps();

            $table->unique(['company','month','year']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_budgets');
    }
};
