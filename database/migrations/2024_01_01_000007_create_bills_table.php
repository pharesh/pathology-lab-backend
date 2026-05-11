<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_uid', 20)->unique();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->decimal('subtotal', 10, 2);
            $table->enum('discount_type', ['flat', 'percent'])->nullable();
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->enum('payment_method', ['cash', 'upi', 'card', 'other'])->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
