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

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events', 'onDelete');
            $table->foreignId('product_id')->constrained('products', 'onDelete');
            $table->foreignId('buyer_id')->constrained('users', 'onDelete');
            $table->string('banner_location', 255)->nullable();
            $table->string('company_name', 255);
            $table->text('banner_image_url')->nullable();
            $table->string('contact_email', 255);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
