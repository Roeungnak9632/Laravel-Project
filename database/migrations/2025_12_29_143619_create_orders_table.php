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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('sub_total', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->nullable()->default(0);
            $table->decimal('total_amount', 10, 2);

            $table->decimal('paid_amount', 10, 2)->nullable()->default(0);
            $table->decimal('change_amount', 10, 2)->nullable()->default(0);

            $table->string('payment_method');
            $table->text('note')->nullable();

            $table->enum('status', ['paid', 'hold', 'cancel'])->default('paid');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
