<?php

namespace Danny50610\LaravelApacheAgeDriver;

use Closure;
use Danny50610\LaravelApacheAgeDriver\Query\AfterQuery;
use Danny50610\LaravelApacheAgeDriver\Query\Builder as QueryBuilder;
use Danny50610\LaravelApacheAgeDriver\Services\ApacheAgeService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

use function Illuminate\Support\enum_value;

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
        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event) {
            if ($this->app->runningInConsole()) {
                return;
            }

            DB::statement('SET SESSION search_path = ag_catalog, public;');
        });

        PostgresConnection::macro('apacheAgeCreateGraph', function (string $graphName) {
            /** @var PostgresConnection $this */
            return $this->select("SELECT * FROM ag_catalog.create_graph(?);", [$graphName]);
        });

        PostgresConnection::macro('apacheAgeDropGraph', function (string $graphName, bool $cascade) {
            /** @var PostgresConnection $this */
            return $this->select("SELECT * FROM ag_catalog.drop_graph(?, ?);", [$graphName, $cascade]);
        });

        PostgresConnection::macro('apacheAgeHasGraph', function (string $graphName) {
            /** @var PostgresConnection $this */
            return $this->select("SELECT count(*) FROM ag_catalog.ag_graph WHERE name = ?", [$graphName])[0]->count > 0;
        });

        PostgresConnection::macro('apacheAgeCypher', function ($graphName, Closure $closure) {
            $builder = new QueryBuilder();
            $builder = $closure($builder);
            $builder->build();

            $queryString = $builder->getQueryString();
            $as = $builder->getAs();
            $parameters = $builder->getParameters();

            /** @var PostgresConnection $this */
            return $this->query()->apacheAgeCypherFrom(enum_value($graphName), $queryString, $parameters, $as);
        });

        Builder::macro('apacheAgeCypherFrom', function ($graphName, $queryString, array $parameters, $as) {
            /** @var Builder $this */
            $expression = "cypher('{$graphName}', {$queryString}";
            if (count($parameters) > 0) {
                $expression .= ", ?";
            }
            $expression .= ") as {$as}";

            $this->from = new Expression($expression);
            if (count($parameters) > 0) {
                $this->addBinding(json_encode($parameters), 'from');
            }

            $this->afterQuery(Closure::fromCallable(new AfterQuery()));

            return $this;
        });
    }
}
