<?php

namespace Danny50610\LaravelApacheAgeDriver\Query\Concerns;

use Illuminate\Contracts\Database\Query\Expression as ExpressionContract;
use Illuminate\Database\Query\Grammars\Grammar;

trait WithProperties
{
    protected readonly array $properties;

    protected function propertiesToString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        // 只考慮第一層
        // TODO: 支援巢狀
        $result = '{';
        $result .= collect($this->properties)->map(function($value, $key) use ($grammar, &$parameters, &$parametersCount) {
            if (is_string($value)) {
                $resultValue = addslashes($value);
            } else {
                $resultValue = $value;
            }

            if (! $value instanceof ExpressionContract) {
                $parameters['v' . $parametersCount] = $resultValue;
                $result = $key . ': $v' . $parametersCount;
                $parametersCount += 1;
            } else {
                $result = $key . ': ' . $grammar->getValue($resultValue);
            }

            return $result;
        })->join(', ');
        $result .= '}';

        return $result;
    }
}