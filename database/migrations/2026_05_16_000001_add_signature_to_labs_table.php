<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('labs', function (Blueprint $table) {
            $table->string('doctor_name')->nullable();
            $table->string('doctor_designation')->nullable();
            $table->string('signature_image')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('labs', function (Blueprint $table) {
            $table->dropColumn(['doctor_name', 'doctor_designation', 'signature_image']);
        });
    }
};
