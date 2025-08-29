<?php

namespace Danny50610\LaravelApacheAgeDriver;

use Closure;
use Danny50610\LaravelApacheAgeDriver\Listeners\ConfigSearchPath;
use Danny50610\LaravelApacheAgeDriver\Services\ApacheAgeService;
use function Illuminate\Support\enum_value;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ApacheAgeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(ApacheAgeService::class, function (Application $app) {
            return new ApacheAgeService();
        });
    }

    public function boot(): void
    {
        PostgresConnection::macro('apacheAgeCypher', function ($graphName, $queryString, array $parameters, $as) {
            /** @var PostgresConnection $this */
            return $this->query()->apacheAgeCypherFrom(enum_value($graphName), $queryString, $parameters, $as);
        });

        Builder::macro('apacheAgeCypherFrom', function ($graphName, $queryString, array $parameters, $as) {
            /** @var Builder $this */
            $this->from = new Expression("cypher('{$graphName}', $\${$queryString}$$) as {$as}");

            return $this;
        });

        // TODO: create middleware for set search_path
    }
}
