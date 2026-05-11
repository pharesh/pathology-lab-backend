<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->string('parameter_name', 100);
            $table->string('observed_value', 100);
            $table->string('unit', 30)->nullable();
            $table->boolean('is_abnormal')->default(false);
            $table->text('remarks')->nullable();
            $table->string('entered_by', 100);
            $table->timestamp('entered_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
