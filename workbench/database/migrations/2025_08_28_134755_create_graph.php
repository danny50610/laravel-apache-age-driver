<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::apacheAgeHasGraph('graph_name')) {
            DB::apacheAgeDropGraph('graph_name', true);
        }

        DB::apacheAgeCreateGraph('graph_name');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::apacheAgeDropGraph('graph_name', true);
    }
};
