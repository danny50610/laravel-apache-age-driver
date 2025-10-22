<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Enums\Direction;
use Danny50610\LaravelApacheAgeDriver\Query\Builder;
use Danny50610\LaravelApacheAgeDriver\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class RemoveTest extends TestCase
{
    public function testRemoveAndReturn()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (n:RemoveTest {prop1: 'value1', prop2: 'value2'})
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('n', 'RemoveTest')
                ->remove('n.prop1')
                ->return('n');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (n:RemoveTest) REMOVE n.prop1 RETURN n$$) as (n agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            [],
            $query->getBindings()
        );

        $result = $query->get();

        $this->assertCount(1, $result);
        $this->assertEquals(['prop2' => 'value2'], $result[0]->n->properties);
    }

    public function testRemoveTwoPropertiesSameTime()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (n:RemoveTest {prop1: 'value1', prop2: 'value2'})
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('n', 'RemoveTest')
                ->remove(['n.prop1', 'n.prop2'])
                ->return('n');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (n:RemoveTest) REMOVE n.prop1, n.prop2 RETURN n$$) as (n agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            [],
            $query->getBindings()
        );

        $result = $query->get();

        $this->assertCount(1, $result);
        $this->assertEquals([], $result[0]->n->properties);
    }

    public function testRemoveTwoPropertiesWithTwoRemove()
    {
DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (n:RemoveTest {prop1: 'value1', prop2: 'value2'})
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('n', 'RemoveTest')
                ->remove('n.prop1')
                ->remove('n.prop2')
                ->return('n');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (n:RemoveTest) REMOVE n.prop1 REMOVE n.prop2 RETURN n$$) as (n agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            [],
            $query->getBindings()
        );

        $result = $query->get();

        $this->assertCount(1, $result);
        $this->assertEquals([], $result[0]->n->properties);
    }
}
