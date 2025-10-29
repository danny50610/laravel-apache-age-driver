<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Illuminate\Database\Query\Grammars\Grammar;

class WithClause implements Clause
{
    public function __construct(
        protected readonly string|array $withs = [],
    ) {
    }

    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        if (is_array($this->withs)) {
            $withsPart = implode(', ', $this->withs);
        } else {
            $withsPart = $this->withs;
        }

        return "WITH $withsPart";
    }
}