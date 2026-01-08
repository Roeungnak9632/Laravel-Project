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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("expenseType_id");
            $table->string("name");
            $table->text("descrition")->nullable();
            $table->decimal("amount");
            $table->enum('expense_status', ['pending', 'paid', 'cancel'])
                ->default('pending');
            $table->date("expense_date");
            $table->string("create_by");
            $table->foreign("expenseType_id")->references("id")->on("expense_types");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
