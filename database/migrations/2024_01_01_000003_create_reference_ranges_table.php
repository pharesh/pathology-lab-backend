<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('tests')->cascadeOnDelete();
            $table->string('parameter_name', 100);
            $table->string('unit', 30)->nullable();
            $table->decimal('min_value', 10, 3)->nullable();
            $table->decimal('max_value', 10, 3)->nullable();
            $table->string('text_range', 100)->nullable();
            $table->enum('gender_filter', ['male', 'female', 'all'])->default('all');
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->enum('age_unit', ['years', 'months', 'days'])->default('years');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_ranges');
    }
};
