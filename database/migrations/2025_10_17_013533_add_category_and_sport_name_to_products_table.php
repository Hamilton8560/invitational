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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('category', ['youth', 'adult', 'senior', 'general'])->default('general')->after('type');
            $table->string('sport_name', 100)->nullable()->after('category');
            $table->integer('display_order')->default(0)->after('division_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['category', 'sport_name', 'display_order']);
        });
    }
};
