<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Illuminate\Database\Query\Grammars\Grammar;

class WhereClause implements Clause
{
    public function __construct(
        protected readonly string $column,
        protected readonly string $operator,
        protected readonly mixed $value,
        protected readonly string $boolean = 'and',
    ) {
    }

    public function getBoolean(): string
    {
        return $this->boolean;
    }

    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        // ex: a.name = $v1
        if (is_string($this->value)) {
            $resultValue = addslashes($this->value);
        } else {
            $resultValue = $this->value;
        }

        $parameters['v' . $parametersCount] = $resultValue;
        $result =  "{$this->column} {$this->operator} \$v{$parametersCount}";
        $parametersCount += 1;

        return $result;
    }
}
