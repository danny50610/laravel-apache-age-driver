<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Illuminate\Database\Query\Grammars\Grammar;

class DeleteClause implements Clause
{
    public function __construct(
        protected readonly string|array $name,
        protected readonly bool $isDetached = false,
    ) {
    }

    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        $detachedPart = $this->isDetached ? 'DETACH' : '';
        $namePart = is_array($this->name) ? implode(', ', $this->name) : $this->name;

        return "{$detachedPart} DELETE {$namePart}";
    }
}