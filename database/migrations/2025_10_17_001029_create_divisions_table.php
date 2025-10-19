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
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('age_group_id')->nullable()->constrained('age_groups')->onDelete('set null');
            $table->foreignId('skill_level_id')->nullable()->constrained('skill_levels')->onDelete('set null');
            $table->string('name', 255);
            $table->enum('gender', ['male', 'female', 'coed', 'open'])->default('open');
            $table->integer('team_size')->default(1);
            $table->integer('max_teams')->nullable();
            $table->integer('max_players')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('divisions');
    }
};
