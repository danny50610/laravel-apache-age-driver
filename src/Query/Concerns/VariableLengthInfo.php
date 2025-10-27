<?php

namespace Danny50610\LaravelApacheAgeDriver\Query\Concerns;

class VariableLengthInfo
{
    public function __construct(
        public readonly ?int $minLength,
        public readonly ?int $maxLength,
    ) {
    }

    public function toString(): string
    {
        // ex: *3
        // ex: *3..5
        // ex: *3..
        // ex: *..5
        // ex: *

        $result = '*';
        if (!is_null($this->minLength) && !is_null($this->maxLength) && $this->minLength !== $this->maxLength) {
            $result .= "{$this->minLength}..{$this->maxLength}";
        } elseif ($this->minLength === $this->maxLength) {
            $result .= "{$this->minLength}";
        } elseif (!is_null($this->minLength) && is_null($this->maxLength)) {
            $result .= "{$this->minLength}..";
        } elseif (is_null($this->minLength) && !is_null($this->maxLength)) {
            $result .= "..{$this->maxLength}";
        }

        return $result;
    }
}