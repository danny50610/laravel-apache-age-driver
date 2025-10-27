<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Danny50610\LaravelApacheAgeDriver\Query\Concerns\MatchClause;
use Illuminate\Database\Query\Grammars\Grammar;

class MatchRaw implements Clause, MatchClause
{
    public function __construct(
        protected readonly string $queryString,
        protected readonly array $bindings,
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
