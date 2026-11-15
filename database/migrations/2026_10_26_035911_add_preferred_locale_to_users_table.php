<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'member')->update(['role' => 'customer']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('preferred_locale', 2)->default('en')->after('role');
            $table->string('role', 20)->default('customer')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('preferred_locale');
            $table->string('role', 20)->default('member')->change();
        });

        DB::table('users')->where('role', 'customer')->update(['role' => 'member']);
    }
};
