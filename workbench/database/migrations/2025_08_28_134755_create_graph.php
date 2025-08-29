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
        if (DB::select("SELECT count(*) FROM ag_catalog.ag_graph WHERE name = 'graph_name'")[0]->count === 1) {
            DB::statement("SELECT * FROM ag_catalog.drop_graph('graph_name', true)");
        }

        DB::statement("SELECT * FROM ag_catalog.create_graph('graph_name')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("SELECT * FROM ag_catalog.drop_graph('graph_name', true)");
    }
};
