<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Illuminate\Database\Query\Grammars\Grammar;

abstract class MatchBase
{
    use Concerns\WithProperties;

    public readonly ?string $name;

    abstract public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string;
}