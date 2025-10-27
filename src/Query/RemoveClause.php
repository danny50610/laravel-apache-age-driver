<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Illuminate\Database\Query\Grammars\Grammar;

class RemoveClause implements Clause
{
    public function __construct(
        protected readonly string|array $propertyName,
    ) {
    }

    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        $propertyPart = is_array($this->propertyName) ? implode(', ', $this->propertyName) : $this->propertyName;

        return "REMOVE {$propertyPart}";
    }
}