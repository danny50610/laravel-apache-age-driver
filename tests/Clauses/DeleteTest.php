<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Enums\Direction;
use Danny50610\LaravelApacheAgeDriver\Query\Builder;
use Danny50610\LaravelApacheAgeDriver\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class DeleteTest extends TestCase
{
    public function testDeleteNode()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (n:DeleteNodeTest)
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'DeleteNodeTest')->delete('v')->setAs(['a']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v:DeleteNodeTest)  DELETE v$$) as (a agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            [],
            $query->getBindings()
        );

        $query->get();

        $result = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'DeleteNodeTest')->return('v');
        })->get();

        $this->assertCount(0, $result);
    }

    public function testDeleteEdge()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (n:DeleteNodeTest)-[:DeleteEdgeTest]->(m:DeleteNodeTest2)
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode(null, 'DeleteNodeTest')
                ->withMatchEdge(Direction::RIGHT, 'e', 'DeleteEdgeTest')
                ->withMatchNode(null, 'DeleteNodeTest2')
                ->delete('e')
                ->setAs(['a']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (:DeleteNodeTest)-[e:DeleteEdgeTest]->(:DeleteNodeTest2)  DELETE e$$) as (a agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            [],
            $query->getBindings()
        );

        $query->get();

        // Verify edge deletion
        $result = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('a', 'DeleteNodeTest')
                ->withMatchEdge(Direction::RIGHT, 'e', 'DeleteEdgeTest')
                ->withMatchNode('b', 'DeleteNodeTest2')
                ->return('e');
        })->get();

        $this->assertCount(0, $result);

        // Verify nodes still exist
        $resultNodes = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('n', 'DeleteNodeTest')->return('n');
        })->get();

        $this->assertCount(1, $resultNodes);

        $resultNodes2 = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('m', 'DeleteNodeTest2')->return('m');
        })->get();

        $this->assertCount(1, $resultNodes2);
    }

    public function testDeleteDetached()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (n:DeleteDetachedTest)-[:REL]->(m:DeleteDetachedTest2)
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'DeleteDetachedTest')->delete('v', true)->setAs(['a']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v:DeleteDetachedTest) DETACH DELETE v$$) as (a agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            [],
            $query->getBindings()
        );

        $query->get();

        // Verify edge also deletion
        $result = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('a', 'DeleteDetachedTest')
                ->withMatchEdge(Direction::RIGHT, 'e', 'REL')
                ->withMatchNode('b', 'DeleteDetachedTest2')
                ->return('e');
        })->get();

        $this->assertCount(0, $result);

        $result = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'DeleteDetachedTest')->return('v');
        })->get();

        $this->assertCount(0, $result);

        $result2 = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'DeleteDetachedTest2')->return('v');
        })->get();

        $this->assertCount(1, $result2);
    }

    public function testDeleteTwoNodeSameTime()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (:DeleteNodeTest), (:DeleteNodeTest2)
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'DeleteNodeTest')
                ->matchNode('m', 'DeleteNodeTest2')
                ->delete(['v', 'm'])
                ->setAs(['a']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v:DeleteNodeTest), (m:DeleteNodeTest2)  DELETE v, m$$) as (a agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            [],
            $query->getBindings()
        );

        $query->get();

        $result = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'DeleteNodeTest')->return('v');
        })->get();

        $this->assertCount(0, $result);

        $result2 = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'DeleteNodeTest2')->return('v');
        })->get();

        $this->assertCount(0, $result2);
    }

    public function testDeleteTwoNodeTypeWithTwoDelete()
    {
         DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (:DeleteNodeTest), (:DeleteNodeTest2)
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'DeleteNodeTest')
                ->matchNode('m', 'DeleteNodeTest2')
                ->delete('v')
                ->delete('m')
                ->setAs(['a']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v:DeleteNodeTest), (m:DeleteNodeTest2)  DELETE v  DELETE m$$) as (a agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            [],
            $query->getBindings()
        );

        $query->get();

        $result = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'DeleteNodeTest')->return('v');
        })->get();

        $this->assertCount(0, $result);

        $result2 = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'DeleteNodeTest2')->return('v');
        })->get();

        $this->assertCount(0, $result2);
    }

    public function testDeleteThenReturn()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (n:DeleteThenReturnTest {name: 'to be deleted'})
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'DeleteThenReturnTest')
                ->delete('v')
                ->return('v');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v:DeleteThenReturnTest)  DELETE v RETURN v$$) as (v agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            [],
            $query->getBindings()
        );

        $result = $query->get();

        $this->assertCount(1, $result);
        $this->assertSame('DeleteThenReturnTest', $result[0]->v->label);
        $this->assertSame(['name' => 'to be deleted'], $result[0]->v->properties);
    }
}