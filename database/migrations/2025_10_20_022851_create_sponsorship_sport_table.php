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

        Schema::create('sponsorship_sport', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsorship_id')->constrained('sponsorships')->onDelete('cascade');
            $table->foreignId('sport_id')->constrained('sports')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['sponsorship_id', 'sport_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sponsorship_sport');
        Schema::enableForeignKeyConstraints();
    }
};
