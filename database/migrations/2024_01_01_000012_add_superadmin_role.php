<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MongoDB and SQLite are schema-less / no ENUM — skip raw SQL
        if (in_array(DB::getDriverName(), ['sqlite', 'mongodb'])) return;

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','staff','superadmin') NOT NULL DEFAULT 'staff'");
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['sqlite', 'mongodb'])) return;

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','staff') NOT NULL DEFAULT 'staff'");
    }
};
