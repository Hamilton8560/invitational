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

        Schema::create('website_ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events', 'onDelete');
            $table->foreignId('product_id')->constrained('products', 'onDelete');
            $table->foreignId('buyer_id')->constrained('users', 'onDelete');
            $table->enum('ad_placement', ["header","sidebar","footer","popup"]);
            $table->string('company_name', 255);
            $table->text('ad_image_url')->nullable();
            $table->text('ad_link_url')->nullable();
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
        Schema::dropIfExists('website_ads');
    }
};
