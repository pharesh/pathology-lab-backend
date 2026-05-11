<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_uid', 20)->unique();
            $table->string('name', 100);
            $table->unsignedTinyInteger('age');
            $table->enum('age_unit', ['years', 'months', 'days'])->default('years');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('phone', 15);
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('referred_by', 100)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
