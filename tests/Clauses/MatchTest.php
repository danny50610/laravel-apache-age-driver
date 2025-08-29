<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class MatchTest extends TestCase
{
    // TODO: test get(), cursor(), pluck()
    public function testMatchVertex()
    {
        $query = DB::apacheAgeCypher('graph_name', 'MATCH (v:Home) RETURN v', [], '(v agtype)');

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v:Home) RETURN v$$) as (v agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame($result[0]->v->label, 'Home');
        $this->assertSame($result[0]->v->properties, []);
    }

    public function testMatchEdge()
    {
        $query = DB::apacheAgeCypher('graph_name', "MATCH (a {name: 'Node A'})-[r]->(b {name: 'Node B'}) RETURN *", [], '(a agtype, r agtype, b agtype)');

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (a {name: 'Node A'})-[r]->(b {name: 'Node B'}) RETURN *$$) as (a agtype, r agtype, b agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame($result[0]->a->label, 'Person');
        $this->assertSame($result[0]->a->properties, ['name' => 'Node A']);

        $this->assertSame($result[0]->r->label, 'RELTYPE');
        $this->assertSame($result[0]->r->properties, []);
        $this->assertSame($result[0]->r->startId, $result[0]->a->id);
        $this->assertSame($result[0]->r->endId, $result[0]->b->id);

        $this->assertSame($result[0]->b->label, 'Person');
        $this->assertSame($result[0]->b->properties, ['name' => 'Node B']);
    }
}
