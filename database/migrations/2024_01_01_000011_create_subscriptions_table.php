<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->enum('status', ['trial', 'active', 'grace', 'expired', 'cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('payment_ref')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
