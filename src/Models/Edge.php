<?php

namespace Danny50610\LaravelApacheAgeDriver\Models;

class Edge
{
    public function __construct(
        public int $id,
        public ?string $label,
        public array $properties,
        public int $startId,
        public int $endId,
    ) {}
}