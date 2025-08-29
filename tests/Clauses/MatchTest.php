<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Override;

class MatchTest extends TestCase
{
    public function testMatchVertex()
    {
        $query = DB::apacheAgeCypher('graph_name', 'MATCH (v:Home) RETURN v', [], '(v agtype)');

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v:Home) RETURN v$$) as (v agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        dump($result[0]->v);
    }

    public function testMatchEdge()
    {
        $query = DB::apacheAgeCypher('graph_name', "MATCH ({name: 'Node A'})-[e:RELTYPE]->({name: 'Node B'}) RETURN e", [], '(r agtype)');

        // $this->assertSame(
        //     "select * from cypher('graph_name', \$\$MATCH ({name: 'Node A'})-[r]->({name: 'Node B'}) RETURN r$$) as (r agtype)",
        //     $query->toSql(),
        // );

        $result = $query->get();
        $this->assertCount(1, $result);
        dump($result[0]);
    }
}
