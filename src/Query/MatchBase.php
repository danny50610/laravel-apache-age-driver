<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

abstract class MatchBase
{
    use Concerns\WithProperties;

    public readonly ?string $name;
    
    abstract public function toQueryString(): string;
}