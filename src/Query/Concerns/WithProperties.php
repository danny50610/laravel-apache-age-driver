<?php

namespace Danny50610\LaravelApacheAgeDriver\Query\Concerns;

trait WithProperties
{
    protected function propertiesToString(): string
    {
        // 只考慮第一層
        $result = '{';
        $result .= collect($this->properties)->map(function($value, $key) {
            if (is_string($value)) {
                $escapedValue = addslashes($value);
                return $key . ': \'' . $escapedValue . '\'';
            }
            return $key . ': ' . $value;
        })->join(', ');
        $result .= '}';

        return $result;
    }
}