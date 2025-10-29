<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Enums\Direction;
use Danny50610\LaravelApacheAgeDriver\Query\Builder;
use Danny50610\LaravelApacheAgeDriver\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class WithTest extends TestCase
{
    public function testWith()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE ({name: 'David'}), ({name: 'Danny'})
            $$) as (a agtype);
        ");
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                MATCH (david {name: 'David'}), (danny {name: 'Danny'})
                CREATE (david)-[:FRIEND]->(danny)-[:FRIEND]->(),
                    (david)-[:FRIEND]->(danny)-[:FRIEND]->()
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('david', null, ['name' => 'David'])
                ->withMatchEdge(Direction::RIGHT)
                ->withMatchNode('otherPerson')
                ->withMatchEdge(Direction::RIGHT)
                ->withMatchNode()
                ->with(['otherPerson', 'count(*) AS foaf'])
                ->where('foaf', '>', 1)
                ->return('otherPerson.name')
                ->setAs(['name']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (david {name: \$v1})-[]->(otherPerson)-[]->() WITH otherPerson, count(*) AS foaf WHERE foaf > \$v2 RETURN otherPerson.name$$, ?) as (name agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":"David","v2":1}'],
            $query->getBindings()
        );

        $result = $query->get();

        $this->assertCount(1, $result);
    }
}
