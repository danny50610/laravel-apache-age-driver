<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Query\Builder;
use Danny50610\LaravelApacheAgeDriver\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class LimitTest extends TestCase
{
    public function testLimit()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (:limitTest { name: 'Danny' }), (:limitTest { name: 'Charlie' }), (:limitTest { name: 'Alice' })
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder
                ->matchNode('n', 'limitTest')
                ->with(['n.name as name'])
                ->orderBy('n.name')
                ->limit(2)
                ->return(['name']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (n:limitTest) WITH n.name as name  ORDER BY n.name LIMIT 2 RETURN name$$) as (name agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(2, $result);
        $this->assertSame('"Alice"', $result[0]->name);
        $this->assertSame('"Charlie"', $result[1]->name);
    }

    public function testLimitWithExpression()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (:limitTest { name: 'Danny' }), (:limitTest { name: 'Charlie' }), (:limitTest { name: 'Alice' }), (:limitTest { name: 'Apple' }), (:limitTest { name: 'Kevin' })
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder
                ->matchNode('n', 'limitTest')
                ->with(['n.name as name'])
                ->orderBy('n.name')
                ->limit(DB::raw('toInteger(3 * rand()) + 1'))
                ->return(['name']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (n:limitTest) WITH n.name as name  ORDER BY n.name LIMIT toInteger(3 * rand()) + 1 RETURN name$$) as (name agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $resultCount = count($result);
        $this->assertTrue(1 <= $resultCount && $resultCount <= 4);
    }
}
