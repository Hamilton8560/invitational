<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run on PostgreSQL (SQLite doesn't support DROP CONSTRAINT)
        if (DB::getDriverName() === 'pgsql') {
            // Drop the existing check constraint
            DB::statement('ALTER TABLE products DROP CONSTRAINT products_type_check');

            // Add the new check constraint with 'banner' and 'website_ad' included
            DB::statement("ALTER TABLE products ADD CONSTRAINT products_type_check CHECK (type::text = ANY (ARRAY['team_registration'::character varying, 'individual_registration'::character varying, 'spectator_ticket'::character varying, 'advertising'::character varying, 'booth'::character varying, 'banner'::character varying, 'website_ad'::character varying]::text[]))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run on PostgreSQL (SQLite doesn't support DROP CONSTRAINT)
        if (DB::getDriverName() === 'pgsql') {
            // Drop the constraint with 'banner' and 'website_ad'
            DB::statement('ALTER TABLE products DROP CONSTRAINT products_type_check');

            // Restore the previous constraint with only 'booth'
            DB::statement("ALTER TABLE products ADD CONSTRAINT products_type_check CHECK (type::text = ANY (ARRAY['team_registration'::character varying, 'individual_registration'::character varying, 'spectator_ticket'::character varying, 'advertising'::character varying, 'booth'::character varying]::text[]))");
        }
    }
};
