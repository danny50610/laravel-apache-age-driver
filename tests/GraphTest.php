<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests;

use Illuminate\Support\Facades\DB;
use Danny50610\LaravelApacheAgeDriver\Tests\TestCase;

class GraphTest extends TestCase
{
    public function test_apacheAgeCreateGraph_executes_query()
    {
        $result = DB::apacheAgeCreateGraph('test_graph');

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertTrue(property_exists($result[0], 'create_graph'));
    }

    public function test_apacheAgeDropGraph_executes_query()
    {
        // 先建立 graph，確保 drop 不會失敗
        DB::apacheAgeCreateGraph('test_graph');
        $result = DB::apacheAgeDropGraph('test_graph', true);

        $this->assertIsArray($result);

        $this->assertNotEmpty($result);
        $this->assertTrue(property_exists($result[0], 'drop_graph'));
    }

    public function test_apacheAgeHasGraph_executes_query_and_returns_boolean()
    {
        $this->assertFalse(DB::apacheAgeHasGraph('test_graph'));

        DB::apacheAgeCreateGraph('test_graph');
        $this->assertTrue(DB::apacheAgeHasGraph('test_graph'));
    }
}
