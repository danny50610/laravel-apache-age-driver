<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Illuminate\Contracts\Database\Query\Expression as ExpressionContract;
use Illuminate\Database\Query\Grammars\Grammar;

class SkipClause implements Clause
{
    public function __construct(
        protected readonly mixed $count,
    ) {
    }

    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        if ($this->count instanceof ExpressionContract) {
            $countPart = $grammar->getValue($this->count);
        } elseif (is_int($this->count)) {
            $countPart = $this->count;
        } else {
            $countPart = intval($this->count, 10);
        }

        return "SKIP {$countPart}";
    }
}
