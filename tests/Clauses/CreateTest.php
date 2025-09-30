<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Enums\Direction;
use Danny50610\LaravelApacheAgeDriver\Models\Edge;
use Danny50610\LaravelApacheAgeDriver\Models\Vertex;
use Danny50610\LaravelApacheAgeDriver\Query\Builder;
use Danny50610\LaravelApacheAgeDriver\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class CreateTest extends TestCase
{
    public function testCreateVertex()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->createNode('n')->setAs(['v']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$CREATE (n)$$) as (v agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(0, $result);
    }

    public function testCreateTwoVertex()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->createNode('n')->createNode('m')->setAs(['v']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$CREATE (n), (m)$$) as (v agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(0, $result);
    }

    public function testCreateVertexWithLabel()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->createNode(null, 'Person')->setAs(['v']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$CREATE (:Person)$$) as (v agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(0, $result);
    }

    public function testCreateVertexWithLabelAndProperties()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->createNode(null, 'Person', ['name' => 'Andres', 'title' => 'Developer'])->setAs(['n']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$CREATE (:Person {name: \$v1, title: \$v2})$$, ?) as (n agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":"Andres","v2":"Developer"}'],
            $query->getBindings()
        );

        $result = $query->get();
        $this->assertCount(0, $result);
    }

    public function testCreateAndReturnNode()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->createNode('a', null, ['name' => 'Andres'])->return('a');
        });

        $this->assertSame(
            // "select * from cypher('graph_name', \$\$CREATE (a {name: 'Andres'}) RETURN a$$) as (a agtype)",
            "select * from cypher('graph_name', \$\$CREATE (a {name: \$v1}) RETURN a$$, ?) as (a agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":"Andres"}'],
            $query->getBindings()
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame('Andres', $result[0]->a->properties['name']);
    }

    public function testCreateEdge()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('a', 'Person')
                ->matchNode('b', 'Person')
                ->where('a.name', '=', 'Node A')
                ->where('b.name', '=', 'Node B')
                ->createNode('a')
                ->withCreateEdge(Direction::RIGHT, 'e', 'RELTYPE')
                ->withCreateNode('b')
                ->return('e');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (a:Person), (b:Person)WHERE a.name = \$v1 AND b.name = \$v2 CREATE (a)-[e:RELTYPE]->(b) RETURN e$$, ?) as (e agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":"Node A","v2":"Node B"}'],
            $query->getBindings()
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame('RELTYPE', $result[0]->e->label);
        $this->assertSame([], $result[0]->e->properties);
    }

    public function testCreateEdgeWithProperties()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('a', 'Person')
                ->matchNode('b', 'Person')
                ->where('a.name', '=', 'Node A')
                ->where('b.name', '=', 'Node B')
                ->createNode('a')
                ->withCreateEdge(Direction::RIGHT, 'e', 'RELTYPE', ['name' => DB::raw("a.name + '<->' + b.name")])
                ->withCreateNode('b')
                ->return('e');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (a:Person), (b:Person)WHERE a.name = \$v1 AND b.name = \$v2 CREATE (a)-[e:RELTYPE {name: a.name + '<->' + b.name}]->(b) RETURN e$$, ?) as (e agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":"Node A","v2":"Node B"}'],
            $query->getBindings()
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame('RELTYPE', $result[0]->e->label);
        $this->assertSame('Node A<->Node B', $result[0]->e->properties['name']);
    }

    public function testCreateCompletePath()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->createNode('andres', null, ['name' => 'Andres'], 'p')
                ->withCreateEdge(Direction::RIGHT, null, 'WORKS_AT')
                ->withCreateNode('neo')
                ->withCreateEdge(Direction::LEFT, null, 'WORKS_AT')
                ->withCreateNode('michael', null, ['name' => 'Michael'])
                ->return('p');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$CREATE p = (andres {name: \$v1})-[:WORKS_AT]->(neo)<-[:WORKS_AT]-(michael {name: \$v2}) RETURN p$$, ?) as (p agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":"Andres","v2":"Michael"}'],
            $query->getBindings()
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $path = $result[0]->p;
        $this->assertCount(5, $path);

        $this->assertTrue($path[0] instanceof Vertex);
        $this->assertNull($path[0]->label);
        $this->assertSame('Andres', $path[0]->properties['name']);

        $this->assertTrue($path[1] instanceof Edge);
        $this->assertSame('WORKS_AT', $path[1]->label);
        $this->assertSame($path[0]->id, $path[1]->startId);
        $this->assertSame($path[2]->id, $path[1]->endId);

        $this->assertTrue($path[2] instanceof Vertex);
        $this->assertNull($path[2]->label);
        $this->assertEmpty($path[2]->properties);

        $this->assertTrue($path[3] instanceof Edge);
        $this->assertSame('WORKS_AT', $path[3]->label);
        $this->assertSame($path[4]->id, $path[3]->startId);
        $this->assertSame($path[2]->id, $path[3]->endId);

        $this->assertTrue($path[4] instanceof Vertex);
        $this->assertNull($path[4]->label);
        $this->assertSame('Michael', $path[4]->properties['name']);
    }

}