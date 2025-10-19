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
        Schema::table('age_groups', function (Blueprint $table) {
            $table->enum('category', ['youth', 'adult', 'senior'])->default('adult')->after('name');
            $table->integer('display_order')->default(0)->after('category');
        });

        // Update existing records with appropriate categories
        DB::table('age_groups')->where('name', 'LIKE', 'U%')->update(['category' => 'youth']);
        DB::table('age_groups')->whereIn('name', ['35+', '50+'])->update(['category' => 'senior']);
        DB::table('age_groups')->where('name', 'Open')->update(['category' => 'adult']);

        // Set display orders
        DB::table('age_groups')->where('name', 'U10')->update(['display_order' => 10]);
        DB::table('age_groups')->where('name', 'U12')->update(['display_order' => 20]);
        DB::table('age_groups')->where('name', 'U14')->update(['display_order' => 30]);
        DB::table('age_groups')->where('name', 'U16')->update(['display_order' => 40]);
        DB::table('age_groups')->where('name', 'U18')->update(['display_order' => 50]);
        DB::table('age_groups')->where('name', 'Open')->update(['display_order' => 100]);
        DB::table('age_groups')->where('name', '35+')->update(['display_order' => 200]);
        DB::table('age_groups')->where('name', '50+')->update(['display_order' => 210]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('age_groups', function (Blueprint $table) {
            $table->dropColumn(['category', 'display_order']);
        });
    }
};
