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
        Schema::create('payroll_months', function (Blueprint $table) {
            $table->id();
            $table->enum('approved_by', ['admin', 'HR', 'cashier'])->nullable();
            $table->string('monthly');
            $table->date('date_month');
            $table->enum('status', ['pending', 'approved', 'draft'])
                ->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_months');
    }
};
