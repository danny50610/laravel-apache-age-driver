<?php


namespace Danny50610\LaravelApacheAgeDriver\Tests\Clauses;

use Danny50610\LaravelApacheAgeDriver\Enums\Direction;
use Danny50610\LaravelApacheAgeDriver\Query\Builder;
use Danny50610\LaravelApacheAgeDriver\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class SetTest extends TestCase
{
    public function testSet()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (:SetNodeTest { name: 'Alice' })
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder
                ->matchNode('a', 'SetNodeTest', ['name' => 'Alice'])
                ->set(['a.name' => 'Danny'])
                ->return('a');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (a:SetNodeTest {name: \$v1}) SET a.name = \$v2 RETURN a$$, ?) as (a agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":"Alice","v2":"Danny"}'],
            $query->getBindings()
        );

        $result = $query->get();

        $this->assertCount(1, $result);
        $this->assertSame(['name' => 'Danny'], $result[0]->a->properties);
    }

    public function testSetNull()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (:SetNodeTest { name: 'Bob' })
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder
                ->matchNode('a', 'SetNodeTest', ['name' => 'Bob'])
                ->set(['a.name' => null])
                ->return('a');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (a:SetNodeTest {name: \$v1}) SET a.name = \$v2 RETURN a$$, ?) as (a agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":"Bob","v2":null}'],
            $query->getBindings()
        );

        $result = $query->get();

        $this->assertCount(1, $result);
        $this->assertSame([], $result[0]->a->properties);
    }

    public function testSetMultipleProperties()
    {
        DB::statement("
            SELECT * FROM cypher('graph_name', $$
                CREATE (:SetNodeTest { name: 'Charlie', age: 30 })
            $$) as (a agtype);
        ");

        $query = DB::apacheAgeCypher('graph_name', function (Builder $builder) {
            return $builder
                ->matchNode('a', 'SetNodeTest', ['name' => 'Charlie'])
                ->set(['a.name' => 'Charles', 'a.age' => 31])
                ->return('a');
        });

        $this->assertSame(
            "select * from cypher('graph_name', \$\$MATCH (a:SetNodeTest {name: \$v1}) SET a.name = \$v2, a.age = \$v3 RETURN a$$, ?) as (a agtype)",
            $query->toSql(),
        );

        $this->assertSame(
            ['{"v1":"Charlie","v2":"Charles","v3":31}'],
            $query->getBindings()
        );

        $result = $query->get();

        $this->assertCount(1, $result);
        $this->assertSame(['age' => 31, 'name' => 'Charles'], $result[0]->a->properties);
    }
}