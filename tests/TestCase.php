<?php

namespace Danny50610\LaravelApacheAgeDriver\Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Override;
use Workbench\Database\Seeders\DatabaseSeeder;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;
    use RefreshDatabase;

    protected $seeder = DatabaseSeeder::class;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET SESSION search_path = ag_catalog, public;');
    }
}
