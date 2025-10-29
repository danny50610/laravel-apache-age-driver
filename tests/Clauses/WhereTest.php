<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Query\Builder;
use Danny50610\LaravelApacheAgeDriver\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class WhereTest extends TestCase
{
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

    public function testTwoWhere()
    {
        DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->createNode('v', 'TempNode', ['name' => 'Temp', 'age' => 30])->return('v');
        })->get();

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v')
                ->where('v.name', '=', 'Temp')
                ->where('v.age', '>', 20)
                ->return('v');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v) WHERE v.name = \$v1 AND v.age > \$v2 RETURN v$$, ?) as (v agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":"Temp","v2":20}'],
            $query->getBindings()
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame('TempNode', $result[0]->v->label);
        $this->assertSame(['age' => 30, 'name' => 'Temp'], $result[0]->v->properties);
    }

    public function testOrWhere()
    {
        DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->createNode('v', 'TempNode', ['name' => 'Temp', 'age' => 30])->return('v');
        })->get();

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder->matchNode('v')
                ->where('v.name', '=', 'Temp')
                ->orWhere('v.age', '<', 20)
                ->return('v');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (v) WHERE v.name = \$v1 OR v.age < \$v2 RETURN v$$, ?) as (v agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":"Temp","v2":20}'],
            $query->getBindings()
        );

        $result = $query->get();
        $this->assertCount(1, $result);
        $this->assertSame('TempNode', $result[0]->v->label);
        $this->assertSame(['age' => 30, 'name' => 'Temp'], $result[0]->v->properties);
    }
}
