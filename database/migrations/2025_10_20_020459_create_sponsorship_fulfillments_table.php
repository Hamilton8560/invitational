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

        Schema::create('sponsorship_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsorship_id')->constrained('sponsorships')->onDelete('cascade');
            $table->foreignId('sponsor_package_benefit_id')->constrained('sponsor_package_benefits')->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->json('proof_files')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsorship_fulfillments');
    }
};
