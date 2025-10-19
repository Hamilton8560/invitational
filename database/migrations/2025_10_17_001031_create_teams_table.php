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

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events', 'onDelete');
            $table->foreignId('division_id')->constrained('divisions', 'onDelete');
            $table->foreignId('owner_id')->constrained('users', 'onDelete');
            $table->string('name', 255);
            $table->integer('max_players');
            $table->integer('current_players')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
