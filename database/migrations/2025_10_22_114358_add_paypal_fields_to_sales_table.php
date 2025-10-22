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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('paypal_order_id')->nullable()->after('stripe_customer_id');
            $table->string('paypal_payer_id')->nullable()->after('paypal_order_id');
            $table->string('paypal_capture_id')->nullable()->after('paypal_payer_id');

            $table->index('paypal_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['paypal_order_id']);
            $table->dropColumn(['paypal_order_id', 'paypal_payer_id', 'paypal_capture_id']);
        });
    }
};
