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
            $table->foreignId('event_time_slot_id')->nullable()->after('event_id')->constrained('event_time_slots')->onDelete('set null');
            $table->decimal('cash_prize', 10, 2)->nullable()->after('price');
            $table->enum('format', ['round_robin', 'single_elimination', 'double_elimination', 'pool_play'])->nullable()->after('cash_prize');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['event_time_slot_id']);
            $table->dropColumn(['event_time_slot_id', 'cash_prize', 'format']);
        });
    }
};
