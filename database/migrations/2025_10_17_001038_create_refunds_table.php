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

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales', 'onDelete');
            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();
            $table->enum('status', ["pending","approved","rejected","completed"])->default('pending');
            $table->string('paddle_refund_id', 255)->nullable();
            $table->foreignId('requested_by')->constrained('users', 'onDelete');
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users', 'onDelete');
            $table->foreignId('requested_by_id');
            $table->foreignId('processed_by_id');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
