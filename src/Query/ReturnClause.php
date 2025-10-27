<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Illuminate\Database\Query\Grammars\Grammar;

class ReturnClause implements Clause
{
    public function __construct(
        protected readonly string|array $returns,
        protected readonly bool $distinct = false,
    ) {
    }

    public function getReturn(): array
    {
        if (is_string($this->returns)) {
            return [$this->returns];
        }

        return $this->returns;
    }

    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        $distinctPart = $this->distinct ? 'DISTINCT ' : '';
        if (is_array($this->returns)) {
            $returnsPart = implode(', ', $this->returns);
        } else {
            $returnsPart = $this->returns;
        }

        return "RETURN {$distinctPart}{$returnsPart}";
    }
}