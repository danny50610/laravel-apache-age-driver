<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

abstract class CreateBase
{
    use Concerns\WithProperties;

    public readonly ?string $name;

    abstract public function toQueryString(array &$parameters, int &$parametersCount): string;
}