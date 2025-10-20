<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('sponsorship_id')->nullable()->after('website_ad_id')->constrained('sponsorships')->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['sponsorship_id']);
            $table->dropColumn('sponsorship_id');
        });

        Schema::enableForeignKeyConstraints();
    }
};
