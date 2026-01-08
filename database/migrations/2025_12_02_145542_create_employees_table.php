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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string("card_id")->unique();
            $table->string("firstname");
            $table->string("lastname");
            $table->date("dob")->nullable();
            $table->string("email")->nullable()->unique();
            $table->string("telephone")->nullable();
            $table->string("position");
            $table->decimal("salary", 10, 2);
            $table->string('image')->nullable();
            $table->text("address")->nullable();
            $table->timestamps();
            $table->softDeletes(); // optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
