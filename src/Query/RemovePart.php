<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Illuminate\Database\Query\Grammars\Grammar;

class RemovePart
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