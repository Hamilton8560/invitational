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
        Schema::disableForeignKeyConstraints();

        Schema::create('individual_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events', 'onDelete');
            $table->foreignId('division_id')->constrained('divisions', 'onDelete');
            $table->foreignId('user_id')->constrained('users', 'onDelete');
            $table->decimal('skill_rating', 3, 1)->nullable();
            $table->string('emergency_contact_name', 255);
            $table->string('emergency_contact_phone', 20);
            $table->boolean('waiver_signed')->default(false);
            $table->timestamp('waiver_signed_at')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individual_players');
    }
};
