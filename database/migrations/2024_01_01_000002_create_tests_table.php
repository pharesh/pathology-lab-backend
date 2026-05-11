<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('test_code', 20)->unique();
            $table->string('test_name', 150);
            $table->string('category', 100);
            $table->enum('sample_type', ['blood', 'urine', 'stool', 'swab', 'other']);
            $table->decimal('price', 10, 2);
            $table->unsignedTinyInteger('turnaround_hours')->default(24);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
