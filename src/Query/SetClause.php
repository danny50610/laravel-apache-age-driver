<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Illuminate\Database\Query\Grammars\Grammar;

class SetClause implements Clause
{
    public function __construct(
        protected readonly array $values,
    ) {
    }

    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        // ex: a.name = $v1, b.name = $v2
        $resultList = [];
        foreach ($this->values as $key => $value) {
            if (is_string($value)) {
                $resultValue = addslashes($value);
            } else {
                $resultValue = $value;
            }
            $parameters['v' . $parametersCount] = $resultValue;
            $resultList[] = "{$key} = \$v{$parametersCount}";
            $parametersCount += 1;

        }

        return 'SET ' . implode(', ', $resultList);
    }
}