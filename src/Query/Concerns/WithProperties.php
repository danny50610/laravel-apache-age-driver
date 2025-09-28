<?php

namespace Danny50610\LaravelApacheAgeDriver\Query\Concerns;

trait WithProperties
{
    protected readonly array $properties;

    protected function propertiesToString(array &$parameters, int &$parametersCount): string
    {
        // 只考慮第一層
        // TODO: 支援巢狀
        $result = '{';
        $result .= collect($this->properties)->map(function($value, $key) use (&$parameters, &$parametersCount) {
            if (is_string($value)) {
                $resultValue = addslashes($value);
            } else {
                $resultValue = $value;
            }

            $parameters['v' . $parametersCount] = $resultValue;
            $result =  $key . ': $v' . $parametersCount;
            $parametersCount += 1;

            return $result;
        })->join(', ');
        $result .= '}';

        return $result;
    }
}