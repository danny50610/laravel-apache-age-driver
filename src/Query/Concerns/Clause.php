<?php

namespace Danny50610\LaravelApacheAgeDriver\Query\Concerns;

use Illuminate\Database\Query\Grammars\Grammar;

interface Clause
{
    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string;
}
