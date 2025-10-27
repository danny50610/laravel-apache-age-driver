<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Enums\Direction;
use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Danny50610\LaravelApacheAgeDriver\Query\Concerns\VariableLengthInfo;
use Illuminate\Database\Query\Grammars\Grammar;
use Override;

class MatchEdge implements Clause
{
    use Concerns\WithProperties;

    public function __construct(
        protected readonly Direction $direction,
        public readonly ?string $name,
        protected readonly ?string $label,
        protected readonly array $properties,
        protected readonly ?VariableLengthInfo $variableLengthInfo = null,
    ) {
    }

    #[Override]
    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        // ex: -[r]->
        // ex: <-[r]-
        // ex: -[r:Home]->
        // ex: -[:acted_in {role: $v1}]->
        // ex: -[:acted_in *2{role: $v1}]->
        $startArrow = $this->direction->startArrow();
        $endArrow = $this->direction->endArrow();
        $namePart = $this->name ? $this->name : '';
        $labelPart = $this->label ? ":{$this->label}" : '';
        $lengthPart = $this->variableLengthInfo ? $this->variableLengthInfo->toString() : '';
        $propertiesJsonPart = empty($this->properties) ? '' : ' ' . $this->propertiesToString($grammar, $parameters, $parametersCount);

        return "{$startArrow}[{$namePart}{$labelPart}{$lengthPart}{$propertiesJsonPart}]{$endArrow}";
    }
}
