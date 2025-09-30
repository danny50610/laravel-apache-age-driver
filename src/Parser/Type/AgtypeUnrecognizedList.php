<?php

namespace Danny50610\LaravelApacheAgeDriver\Parser\Type;

use Override;

class AgtypeUnrecognizedList extends AgtypeListImpl implements UnrecognizedObject, AgtypeAnnotation
{
    private string $annotation;

    #[Override]
    public function getAnnotation(): string {
        return $this->annotation;
    }

    #[Override]
    public function setAnnotation(string $annotation): void {
        $this->annotation = $annotation;
    }
}
