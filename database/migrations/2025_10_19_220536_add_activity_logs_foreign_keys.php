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

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('event_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            $table->index(['subject_type', 'subject_id']);
            $table->foreign('event_id')->references('id')->on('events')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('checkins', function (Blueprint $table) {
            $table->index('sale_id');
            $table->index('event_id');
            $table->index('user_id');
            $table->index('checked_in_by');
            $table->index('checked_in_at');
            $table->index('check_in_type');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('checked_in_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('set null');
            $table->foreign('individual_player_id')->references('id')->on('individual_players')->onDelete('set null');
            $table->foreign('booth_id')->references('id')->on('booths')->onDelete('set null');
            $table->foreign('banner_id')->references('id')->on('banners')->onDelete('set null');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('checkins', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropForeign(['event_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['checked_in_by']);
            $table->dropForeign(['team_id']);
            $table->dropForeign(['individual_player_id']);
            $table->dropForeign(['booth_id']);
            $table->dropForeign(['banner_id']);
        });
    }
};
