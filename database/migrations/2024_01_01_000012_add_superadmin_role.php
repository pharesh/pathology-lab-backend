<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite has no ENUM — the column already accepts any string value
        if (DB::getDriverName() === 'sqlite') return;

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','staff','superadmin') NOT NULL DEFAULT 'staff'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') return;

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','staff') NOT NULL DEFAULT 'staff'");
    }
};
