<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 100)->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('registration_no', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labs');
    }
};
