<?php

namespace Danny50610\LaravelApacheAgeDriver\Models;

class Vertex
{
    public function __construct(
        public int $id,
        public ?string $label,
        public array $properties,
    ) {}
}
