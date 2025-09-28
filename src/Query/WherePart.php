<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

class WherePart
{
    public function __construct(
        protected readonly string $column,
        protected readonly string $operator,
        protected readonly string $value,
    ) {
    }

    public function toQueryString(array &$parameters, int &$parametersCount): string
    {
        // ex: a.name = $v1
        if (is_string($this->value)) {
            $resultValue = addslashes($this->value);
        } else {
            $resultValue = $this->value;
        }

        $parameters['v' . $parametersCount] = $resultValue;
        $result =  "{$this->column} {$this->operator} \$v{$parametersCount}";
        $parametersCount += 1;

        return $result;
    }
}
