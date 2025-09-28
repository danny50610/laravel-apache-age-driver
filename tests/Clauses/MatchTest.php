<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Enums\Direction;
use Danny50610\LaravelApacheAgeDriver\Query\Builder;
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
            "select * from cypher('graph_name', \$\$MATCH (v:Box {no: 3}) RETURN v$$) as (v agtype)",
            // "select * from cypher('graph_name', \$\$MATCH (v:Box {no: \$v1}) RETURN v$$, ?) as (v agtype)",
            $query->toSql(),
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
            "select * from cypher('graph_name', \$\$MATCH (a {name: 'Node A'})-[r]->(b {name: 'Node B'}) RETURN *$$) as (a agtype, r agtype, b agtype)",
            $query->toSql(),
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

    // TODO: testMatchPath

    // TODO: testMatchPREPARE
}
