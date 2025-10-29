<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Closure;
use Danny50610\LaravelApacheAgeDriver\Query\Builder;
use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Danny50610\LaravelApacheAgeDriver\Query\Concerns\WhereClause;
use Illuminate\Database\Query\Grammars\Grammar;

class WhereClosureClause implements Clause, WhereClause
{
    public function __construct(
        protected readonly Closure $column,
        protected readonly string $boolean = 'and',
    ) {
    }

    public function getBoolean(): string
    {
        return $this->boolean;
    }

    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        $builder = new Builder();
        ($this->column)($builder);
        $builder->build($grammar, $parameters, $parametersCount);

        return '(' . $builder->getUnDoubleSignQueryString() . ')';
    }
}
