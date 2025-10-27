<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Enums\Direction;
use Danny50610\LaravelApacheAgeDriver\Models\Path;
use Danny50610\LaravelApacheAgeDriver\Query\Builder;
use Danny50610\LaravelApacheAgeDriver\Query\Concerns\VariableLengthInfo;
use Danny50610\LaravelApacheAgeDriver\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class MatchTest extends TestCase
{
    public function testMatchVertex()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'Home')->return('v');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v:Home) RETURN v$$) as (v agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame('Home', $result[0]->v->label);
        $this->assertSame([], $result[0]->v->properties);
    }

    public function testMatchVertexUseRaw()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->raw('MATCH (v:Home) RETURN v', '(v agtype)', []);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v:Home) RETURN v$$) as (v agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame('Home', $result[0]->v->label);
        $this->assertSame([], $result[0]->v->properties);
    }

    public function testMatchVertexUseRawWithParameters()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->raw('MATCH (v:Box {no: $v1}) RETURN v', '(v agtype)', ['v1' => 3]);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v:Box {no: \$v1}) RETURN v$$, ?) as (v agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":3}'],
            $query->getBindings()
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame('Box', $result[0]->v->label);
        $this->assertSame(['no' => 3], $result[0]->v->properties);
    }

    public function testMatchVertexPluck()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'Box')->return('v');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v:Box) RETURN v$$) as (v agtype)",
            $query->toSql(),
        );

        $result = $query->pluck('v');
        $this->assertCount(5, $result);
        $this->assertSame('Box', $result[0]->label);
    }

    public function testMatchVertexCursor()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'Box')->return('v');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v:Box) RETURN v$$) as (v agtype)",
            $query->toSql(),
        );

        $results = $query->cursor();
        $count = 0;
        foreach ($results as $result) {
            $count += 1;
            $this->assertSame('Box', $result->v->label);
        }
        $this->assertSame(5, $count);
    }

    public function testMatchVertexWithProperties()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v', 'Box', ['no' => 3])->return('v');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v:Box {no: \$v1}) RETURN v$$, ?) as (v agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":3}'],
            $query->getBindings()
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame('Box', $result[0]->v->label);
        $this->assertSame(['no' => 3], $result[0]->v->properties);
    }

    public function testMatchEdge()
    {
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('a', null, ['name' => 'Node A'])
                ->withMatchEdge(Direction::RIGHT, 'r', null)
                ->withMatchNode('b', null, ['name' => 'Node B'])
                ->return('*');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (a {name: \$v1})-[r]->(b {name: \$v2}) RETURN *$$, ?) as (a agtype, r agtype, b agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":"Node A","v2":"Node B"}'],
            $query->getBindings()
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame('Person', $result[0]->a->label);
        $this->assertSame(['name' => 'Node A'], $result[0]->a->properties);

        $this->assertSame('RELTYPE', $result[0]->r->label);
        $this->assertSame([], $result[0]->r->properties);
        $this->assertSame($result[0]->a->id, $result[0]->r->startId);
        $this->assertSame($result[0]->b->id, $result[0]->r->endId);

        $this->assertSame('Person', $result[0]->b->label);
        $this->assertSame(['name' => 'Node B'], $result[0]->b->properties);
    }

    public function testMatchVariableLengthEdges()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (:startNode), (:endNode)
            $$) as (a agtype);
        ");

        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                MATCH (startnode:startNode), (endnode:endNode)
                CREATE (startnode)-[:RELTYPE]->(endnode),
                    (startnode)-[:RELTYPE]->()-[:RELTYPE]->(endnode),
                    (startnode)-[:RELTYPE]->()-[:RELTYPE]->()-[:RELTYPE]->(endnode),
                    (startnode)-[:RELTYPE]->()-[:RELTYPE]->()-[:RELTYPE]->()-[:RELTYPE]->(endnode),
                    (startnode)-[:RELTYPE]->()-[:RELTYPE]->()-[:RELTYPE]->()-[:RELTYPE]->()-[:RELTYPE]->(endnode)
            $$) as (a agtype);
        ");

        // (start:startNode)-[*2]->(end:endNode)
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('startend', 'startNode', [], 'p')
                ->withMatchEdge(Direction::RIGHT, null, null, [], new VariableLengthInfo(2, 2))
                ->withMatchNode('endnode', 'endNode')
                ->return('p');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH p = (startend:startNode)-[*2]->(endnode:endNode) RETURN p$$) as (p agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(1, $result);

        // (start:startNode)-[*3..5]->(end:endNode)
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('startend', 'startNode', [], 'p')
                ->withMatchEdge(Direction::RIGHT, null, null, [], new VariableLengthInfo(3, 5))
                ->withMatchNode('endnode', 'endNode')
                ->return('p');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH p = (startend:startNode)-[*3..5]->(endnode:endNode) RETURN p$$) as (p agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(3, $result);

        // (start:startNode)-[*3..]->(end:endNode)
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('startnode', 'startNode', [], 'p')
                ->withMatchEdge(Direction::RIGHT, null, null, [], new VariableLengthInfo(3, null))
                ->withMatchNode('endnode', 'endNode')
                ->return('p');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH p = (startnode:startNode)-[*3..]->(endnode:endNode) RETURN p$$) as (p agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(3, $result);

        // (start:startNode)-[*..5]->(end:endNode)
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('startnode', 'startNode', [], 'p')
                ->withMatchEdge(Direction::RIGHT, null, null, [], new VariableLengthInfo(null, 5))
                ->withMatchNode('endnode', 'endNode')
                ->return('p');
        });

        $result = $query->get();
        $this->assertCount(5, $result);

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH p = (startnode:startNode)-[*..5]->(endnode:endNode) RETURN p$$) as (p agtype)",
            $query->toSql(),
        );

        // (start:startNode)-[*]->(end:endNode)
        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('startnode', 'startNode', [], 'p')
                ->withMatchEdge(Direction::RIGHT, null, null, [], new VariableLengthInfo(null, null))
                ->withMatchNode('endnode', 'endNode')
                ->return('p');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH p = (startnode:startNode)-[*]->(endnode:endNode) RETURN p$$) as (p agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(5, $result);
        $this->assertTrue($result[0]->p instanceof Path);
    }

    public function testMatchId()
    {
        $node = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->createNode('v', 'TempNode', ['name' => 'Temp'])->return('v');
        })->get();

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) use ($node) {
            return $builder->matchNode('v')
                ->where('id(v)', '=', $node[0]->v->id)
                ->return('v');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v) WHERE id(v) = \$v1 RETURN v$$, ?) as (v agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":' . $node[0]->v->id . '}'],
            $query->getBindings()
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame('TempNode', $result[0]->v->label);
        $this->assertSame(['name' => 'Temp'], $result[0]->v->properties);
        $this->assertSame($node[0]->v->id, $result[0]->v->id);
    }

    public function testMatchRaw()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (a:Person {name: 'Danny'})-[r:KNOWS]->(b:Person)
            $$) as (a agtype);
        ");
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (a:Person {name: 'Alice'})-[r:KNOWS]->(b:Person)
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchRaw('(a:Person {name: $v1})-[r:KNOWS]->(b:Person)', ['Alice'])
                ->return('a');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (a:Person {name: \$v1})-[r:KNOWS]->(b:Person) RETURN a$$, ?) as (a agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":"Alice"}'],
            $query->getBindings()
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame(['name' => 'Alice'], $result[0]->a->properties);
    }

}
