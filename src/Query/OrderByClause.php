<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Illuminate\Database\Query\Grammars\Grammar;

class OrderByClause implements Clause
{
    public function __construct(
        protected readonly string $column,
        protected readonly string $direction = 'ASC',
    ) {
    }

    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        $directionPart = strtoupper($this->direction) === 'DESC' ? ' DESC' : '';

        return "{$this->column}{$directionPart}";
    }
}
