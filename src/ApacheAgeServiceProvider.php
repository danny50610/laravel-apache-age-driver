<?php

namespace Danny50610\LaravelApacheAgeDriver;

use Danny50610\LaravelApacheAgeDriver\Models\Edge;
use Danny50610\LaravelApacheAgeDriver\Models\Vertex;
use Danny50610\LaravelApacheAgeDriver\Services\ApacheAgeService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use stdClass;

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
        PostgresConnection::macro('apacheAgeCypher', function ($graphName, $queryString, array $parameters, $as) {
            /** @var PostgresConnection $this */
            return $this->query()->apacheAgeCypherFrom(enum_value($graphName), $queryString, $parameters, $as);
        });

        Builder::macro('apacheAgeCypherFrom', function ($graphName, $queryString, array $parameters, $as) {
            /** @var Builder $this */
            $this->from = new Expression("cypher('{$graphName}', $\${$queryString}$$) as {$as}");

            $this->afterQuery(function (\Illuminate\Support\Collection $results) {
                return $results->transform(function ($row) {
                    $newRow = new stdClass();
                    foreach ($row as $key => $value) {
                        // $value
                        // ex: "{"id": 844424930131969, "label": "Home", "properties": {}}::vertex"
                        // ex: "{"id": 1407374883553281, "label": "RELTYPE", "end_id": 1125899906842626, "start_id": 1125899906842625, "properties": {}}::edge"


                        $newValue = null;
                        if (Str::endsWith($value, '::vertex')) {
                            $valueMap = json_decode(substr($value, 0, -8), true);
                            $newValue = new Vertex(
                                $valueMap['id'],
                                $valueMap['label'],
                                $valueMap['properties'],
                            );
                        } elseif (Str::endsWith($value, '::edge')) {
                            $valueMap = json_decode(substr($value, 0, -6), true);
                            $newValue = new Edge(
                                $valueMap['id'],
                                $valueMap['label'],
                                $valueMap['properties'],
                                $valueMap['start_id'],
                                $valueMap['end_id'],
                            );
                        }

                        if (!is_null($newValue)) {
                            $newRow->{$key} = $newValue;
                        } else {
                            $newRow->{$key} = $value;
                        }
                    }

                    return $newRow;
                });
            });

            return $this;
        });

        // TODO: create global middleware for set search_path (auto register)
    }
}
