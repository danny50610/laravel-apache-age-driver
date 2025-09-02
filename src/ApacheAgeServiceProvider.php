<?php

namespace Danny50610\LaravelApacheAgeDriver;

use Closure;
use Danny50610\LaravelApacheAgeDriver\Models\Edge;
use Danny50610\LaravelApacheAgeDriver\Models\Vertex;
use Danny50610\LaravelApacheAgeDriver\Query\AfterQuery;
use Danny50610\LaravelApacheAgeDriver\Services\ApacheAgeService;
use function Illuminate\Support\enum_value;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use stdClass;

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
        /** TODO:
            DB::apacheAgeCypher('graph_name', function () {
                return $query->match('()->[]->()')
            }, '(v astype)');
         */

        PostgresConnection::macro('apacheAgeCypher', function ($graphName, $queryString, array $parameters, $as) {
            /** @var PostgresConnection $this */
            return $this->query()->apacheAgeCypherFrom(enum_value($graphName), $queryString, $parameters, $as);
        });

        Builder::macro('apacheAgeCypherFrom', function ($graphName, $queryString, array $parameters, $as) {
            /** @var Builder $this */
            $this->from = new Expression("cypher('{$graphName}', $\${$queryString}$$) as {$as}");

            $this->afterQuery(Closure::fromCallable(new AfterQuery()));

            return $this;
        });

        // TODO: create global middleware for set search_path (auto register)
    }
}
