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
            $table->string('paypal_product_id')->nullable()->after('stripe_environment');
            $table->string('paypal_environment')->nullable()->after('paypal_product_id');
            $table->timestamp('paypal_last_synced_at')->nullable()->after('paypal_environment');
        });

        Schema::table('sponsor_packages', function (Blueprint $table) {
            $table->string('paypal_product_id')->nullable()->after('stripe_environment');
            $table->string('paypal_environment')->nullable()->after('paypal_product_id');
            $table->timestamp('paypal_last_synced_at')->nullable()->after('paypal_environment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['paypal_product_id', 'paypal_environment', 'paypal_last_synced_at']);
        });

        Schema::table('sponsor_packages', function (Blueprint $table) {
            $table->dropColumn(['paypal_product_id', 'paypal_environment', 'paypal_last_synced_at']);
        });
    }
};
