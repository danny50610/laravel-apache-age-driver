<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Query\Builder;
use Danny50610\LaravelApacheAgeDriver\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class OrderByTest extends TestCase
{
    public function testOrderBy()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (:orderBy { name: 'Danny', age: 30 }), (:orderBy { name: 'Charlie', age: 30 })
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder
                ->matchNode('n', 'orderBy')
                ->with(['n.name as name', 'n.age as age'])
                ->orderBy('n.name')
                ->return(['name', 'age']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (n:orderBy) WITH n.name as name, n.age as age  ORDER BY n.name RETURN name, age$$) as (name agtype, age agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(2, $result);
        $this->assertSame('"Charlie"', $result[0]->name);
        $this->assertSame('"Danny"', $result[1]->name);
    }

    public function testOrderByDesc()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (:orderBy { name: 'Danny', age: 30 }), (:orderBy { name: 'Charlie', age: 30 })
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder
                ->matchNode('n', 'orderBy')
                ->with(['n.name as name', 'n.age as age'])
                ->orderBy('n.name', 'DESC')
                ->return(['name', 'age']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (n:orderBy) WITH n.name as name, n.age as age  ORDER BY n.name DESC RETURN name, age$$) as (name agtype, age agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(2, $result);
        $this->assertSame('"Danny"', $result[0]->name);
        $this->assertSame('"Charlie"', $result[1]->name);
    }

    public function testOrderByMultiple()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (:orderBy { name: 'Danny', age: 30 }), (:orderBy { name: 'Charlie', age: 30 }), (:orderBy { name: 'Apple', age: 22 })
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder
                ->matchNode('n', 'orderBy')
                ->with(['n.name as name', 'n.age as age'])
                ->orderBy('n.age')
                ->orderBy('n.name')
                ->return(['name', 'age']);
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (n:orderBy) WITH n.name as name, n.age as age  ORDER BY n.age , n.name RETURN name, age$$) as (name agtype, age agtype)",
            $query->toSql(),
        );

        $result = $query->get();
        $this->assertCount(3, $result);
        $this->assertSame('"Apple"', $result[0]->name);
        $this->assertSame('"Charlie"', $result[1]->name);
        $this->assertSame('"Danny"', $result[2]->name);
        $this->assertSame('22', $result[0]->age);
        $this->assertSame('30', $result[1]->age);
        $this->assertSame('30', $result[2]->age);
    }
}