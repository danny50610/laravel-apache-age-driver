<?php

namespace Danny50610\LaravelApacheAgeDriver\Query\Concerns;

use Illuminate\Contracts\Database\Query\Expression as ExpressionContract;
use Illuminate\Database\Query\Grammars\Grammar;

trait WithProperties
{
    protected readonly array $properties;

    protected function propertiesToString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        $result = '{';
        $result .= collect($this->properties)->map(function($value, $key) use ($grammar, &$parameters, &$parametersCount) {
            if (! $value instanceof ExpressionContract) {
                $parameters['v' . $parametersCount] = $value;
                $result = $key . ': $v' . $parametersCount;
                $parametersCount += 1;
            } else {
                $result = $key . ': ' . $grammar->getValue($value);
            }

            return $result;
        })->join(', ');
        $result .= '}';

        return $result;
    }
}