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
            $table->string('stripe_checkout_session_id')->nullable()->after('status');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
            $table->string('stripe_customer_id')->nullable()->after('stripe_payment_intent_id');
            $table->timestamp('last_payment_check_at')->nullable()->after('purchased_at');

            $table->index('stripe_checkout_session_id');
            $table->index(['status', 'last_payment_check_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['stripe_checkout_session_id']);
            $table->dropIndex(['status', 'last_payment_check_at']);
            $table->dropColumn([
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'stripe_customer_id',
                'last_payment_check_at',
            ]);
        });
    }
};
