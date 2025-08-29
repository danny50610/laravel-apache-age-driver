<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Workbench\Database\Factories\UserFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // UserFactory::new()->times(10)->create();

        // UserFactory::new()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        DB::transaction(function () {
            DB::statement('SET SESSION search_path = ag_catalog, public;');

            DB::statement("
                SELECT * FROM cypher('graph_name', $$
                    CREATE (n:Home)
                $$) as (a agtype);
            ");

            DB::statement("
                SELECT * FROM cypher('graph_name', $$
                    CREATE (a:Person {name: 'Node A'}), (b:Person {name: 'Node B'})
                    RETURN a
                $$) as (a agtype);
            ");

            DB::statement("
                SELECT * FROM cypher('graph_name', $$
                    MATCH (a:Person), (b:Person)
                    WHERE a.name = 'Node A' AND b.name = 'Node B'
                    CREATE (a)-[e:RELTYPE]->(b)
                    RETURN e
                $$) as (e agtype);
            ");
        });
    }
}
