<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Enums\Direction;
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

    /**
     * 範例：建立 edge 於兩個 nodes 之間
     * 查詢語句：
     * SELECT * 
     * FROM cypher('graph_name', $$
     *     MATCH (a:Person), (b:Person)
     *     WHERE a.name = 'Node A' AND b.name = 'Node B'
     *     CREATE (a)-[e:RELTYPE]->(b)
     *     RETURN e
     * $$) as (e agtype);
     *
     * 預期結果：
     * e
     * {id: 3; startid: 0, endid: 1; label: 'RELTYPE'; properties: {}}::edge
     * (1 row)
     */
    // public function testCreateEdge()
    // {
    //     $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
    //         return $builder->matchNode('a', 'Person')
    //             ->matchNode('b', 'Person')
    //             ->where('a.name', '=', 'Node A')
    //             ->where('b.name', '=', 'Node A')
    //             ->createNode('a')
    //             ->withCreateEdge(Direction::RIGHT, 'e', 'RELTYPE')
    //             ->withCreateNode('b')
    //             ->return('e');
    //     });

    //     $this->assertSame(
    //         "select * from cypher('graph_name', \$\$MATCH (a:Person), (b:Person)WHERE a.name = 'Node A' AND b.name = 'Node B'CREATE (a)-[e:RELTYPE]->(b)RETURN e$$) as (e agtype)",
    //         $query->toSql(),
    //     );

    //     $result = $query->get();
    //     $this->assertCount(1, $result);
    //     $this->assertSame('RELTYPE', $result[0]->e->label);
    //     $this->assertSame([], $result[0]->e->properties);
    // }

    /**
     * 範例：建立 edge 並設定 properties
     * 查詢語句：
     * SELECT * 
     * FROM cypher('graph_name', $$
     *     MATCH (a:Person), (b:Person)
     *     WHERE a.name = 'Node A' AND b.name = 'Node B'
     *     CREATE (a)-[e:RELTYPE {name:a.name + '<->' + b.name}]->(b)
     *     RETURN e
     * $$) as (e agtype);
     *
     * 預期結果：
     * e
     * {id: 3; startid: 0, endid: 1; label: 'RELTYPE'; properties: {name: 'Node A<->Node B'}}::edge
     * (1 row)
     */

    /**
     * 範例：建立完整 path
     * 查詢語句：
     * SELECT * 
     * FROM cypher('graph_name', $$
     *     CREATE p = (andres {name:'Andres'})-[:WORKS_AT]->(neo)<-[:WORKS_AT]-(michael {name:'Michael'})
     *     RETURN p
     * $$) as (p agtype);
     *
     * 預期結果：
     * p
     * [{id:0; label: ''; properties:{name:'Andres'}}::vertex,
     *  {id: 3; startid: 0, endid: 1; label: 'WORKS_AT'; properties: {}}::edge,
     *  {id:1; label: ''; properties: {}}::vertex,
     *  {id: 3; startid: 2, endid: 1; label: 'WORKS_AT'; properties: {}}::edge,
     *  {id:2; label: ''; properties: {name:'Michael'}}::vertex]::path
     * (1 row)
     */
}