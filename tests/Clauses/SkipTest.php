<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Query\Builder;
use Danny50610\LaravelApacheAgeDriver\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class SkipTest extends TestCase
{
    public function testSkip()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (:skipTest { name: 'Danny' }), (:skipTest { name: 'Charlie' }), (:skipTest { name: 'Alice' })
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder
                ->matchNode('n', 'skipTest')
                ->with(['n.name as name'])
                ->orderBy('n.name')
                ->skip(1)
                ->return(['name']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (n:skipTest) WITH n.name as name  ORDER BY n.name SKIP 1 RETURN name$$) as (name agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(2, $result);
        $this->assertSame('"Charlie"', $result[0]->name);
        $this->assertSame('"Danny"', $result[1]->name);
    }

    public function testSkipWithExpression()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (:skipTest { name: 'Danny' }), (:skipTest { name: 'Charlie' }), (:skipTest { name: 'Alice' }), (:skipTest { name: 'Apple' }), (:skipTest { name: 'Kevin' })
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder
                ->matchNode('n', 'skipTest')
                ->with(['n.name as name'])
                ->orderBy('n.name')
                ->skip(DB::raw('(3 * rand()) + 1'))
                ->return(['name']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (n:skipTest) WITH n.name as name  ORDER BY n.name SKIP (3 * rand()) + 1 RETURN name$$) as (name agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $resultCount = count($result);
        $this->assertTrue(1 <= $resultCount && $resultCount <= 4);
    }
}
