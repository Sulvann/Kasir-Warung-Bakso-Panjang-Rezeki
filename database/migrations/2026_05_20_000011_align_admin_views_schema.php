<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ingredients') && Schema::hasColumn('ingredients', 'unit')) {
            DB::statement("ALTER TABLE ingredients MODIFY unit ENUM('gram', 'kg', 'pcs', 'pack', 'Gram', 'Kg', 'Pcs', 'Kantong') NOT NULL");
            DB::table('ingredients')->where('unit', 'gram')->update(['unit' => 'Gram']);
            DB::table('ingredients')->where('unit', 'kg')->update(['unit' => 'Kg']);
            DB::table('ingredients')->where('unit', 'pcs')->update(['unit' => 'Pcs']);
            DB::table('ingredients')->where('unit', 'pack')->update(['unit' => 'Kantong']);
            DB::statement("ALTER TABLE ingredients MODIFY unit ENUM('Gram', 'Kg', 'Pcs', 'Kantong') NOT NULL");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ingredients') && Schema::hasColumn('ingredients', 'unit')) {
            DB::statement("ALTER TABLE ingredients MODIFY unit ENUM('gram', 'kg', 'pcs', 'pack', 'Gram', 'Kg', 'Pcs', 'Kantong') NOT NULL");
            DB::table('ingredients')->where('unit', 'Gram')->update(['unit' => 'gram']);
            DB::table('ingredients')->where('unit', 'Kg')->update(['unit' => 'kg']);
            DB::table('ingredients')->where('unit', 'Pcs')->update(['unit' => 'pcs']);
            DB::table('ingredients')->where('unit', 'Kantong')->update(['unit' => 'pack']);
            DB::statement("ALTER TABLE ingredients MODIFY unit ENUM('gram', 'kg', 'pcs', 'pack') NOT NULL");
        }
    }
};
