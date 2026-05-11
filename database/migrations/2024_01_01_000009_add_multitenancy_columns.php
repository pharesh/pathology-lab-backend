<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
            $table->enum('role', ['admin', 'staff'])->after('lab_id')->default('admin');
        });

        foreach (['patients', 'tests', 'orders', 'bills'] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->cascadeOnDelete();
            });
        }

        // Migrate existing data: assign all records to a single default lab
        if (DB::table('users')->count() > 0) {
            $adminEmail = DB::table('users')->orderBy('id')->value('email') ?? 'admin@pathlab.com';
            $labId = DB::table('labs')->insertGetId([
                'name'       => 'Pathology Lab',
                'email'      => $adminEmail,
                'is_active'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('users')->update(['lab_id' => $labId]);
            DB::table('patients')->update(['lab_id' => $labId]);
            DB::table('tests')->update(['lab_id' => $labId]);
            DB::table('orders')->update(['lab_id' => $labId]);
            DB::table('bills')->update(['lab_id' => $labId]);
        }
    }

    public function down(): void
    {
        foreach (['bills', 'orders', 'tests', 'patients'] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->dropForeign(['lab_id']);
                $table->dropColumn('lab_id');
            });
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn(['lab_id', 'role']);
        });
    }
};
