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

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ["pending","completed","failed","refunded"])->default('pending');
            $table->string('paddle_transaction_id', 255)->unique()->nullable();
            $table->string('paddle_subscription_id', 255)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->foreignId('individual_player_id')->nullable()->constrained('individual_players')->onDelete('cascade');
            $table->foreignId('booth_id')->nullable()->constrained('booths')->onDelete('cascade');
            $table->foreignId('banner_id')->nullable()->constrained('banners')->onDelete('cascade');
            $table->foreignId('website_ad_id')->nullable()->constrained('website_ads')->onDelete('cascade');
            $table->timestamp('purchased_at')->useCurrent();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
