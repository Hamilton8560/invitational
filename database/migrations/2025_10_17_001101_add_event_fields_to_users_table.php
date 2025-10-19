<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->string('phone', 20)->nullable()->after('email');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->enum('role', ['admin', 'team_owner', 'player', 'vendor', 'spectator'])->default('player')->after('date_of_birth');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_recovery_codes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'phone', 'date_of_birth', 'role', 'two_factor_enabled']);
        });
    }
};
