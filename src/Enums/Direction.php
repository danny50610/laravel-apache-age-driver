<?php

namespace Danny50610\LaravelApacheAgeDriver\Enums;

enum Direction: string
{
    case RIGHT = '->';
    case LEFT = '<-';
    case BOTH = '-';

    public function startArrow(): string 
    {
        return match ($this) {
            Direction::LEFT => '<-',
            Direction::RIGHT => '-',
            Direction::BOTH => '-',
        };
    }

    public function endArrow(): string 
    {
        return match ($this) {
            Direction::LEFT => '-',
            Direction::RIGHT => '->',
            Direction::BOTH => '-',
        };
    }
}
