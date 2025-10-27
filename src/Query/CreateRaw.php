<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Danny50610\LaravelApacheAgeDriver\Query\Concerns\CreateClause;
use Illuminate\Database\Query\Grammars\Grammar;

class CreateRaw implements Clause, CreateClause
{
    public function __construct(
        public readonly string $queryString,
        public readonly array $bindings = [],
    ) {
    }

    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        foreach ($this->bindings as $binding) {
            $parameters['v' . $parametersCount] = $binding;
            $parametersCount += 1;
        }

        return $this->queryString;
    }
}