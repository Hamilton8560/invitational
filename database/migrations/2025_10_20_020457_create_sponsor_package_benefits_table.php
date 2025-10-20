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

        Schema::create('sponsor_package_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsor_package_id')->constrained('sponsor_packages')->onDelete('cascade');
            $table->string('benefit_type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('requires_asset_upload')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsor_package_benefits');
    }
};
