<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Enums\Direction;
use Override;

class MatchEdge extends MatchBase
{
    public function __construct(
        protected readonly Direction $direction,
        public readonly ?string $name,
        protected readonly ?string $label,
        protected readonly array $properties,
    ) {
    }

    #[Override]
    public function toQueryString(array &$parameters, int &$parametersCount): string
    {
        // ex: -[r]->
        // ex: <-[r]-
        // ex: -[r:Home]->
        // ex: -[:acted_in {role: $v1}]->
        $startArrow = $this->direction === Direction::LEFT ? '<-' : '-';
        $endArrow = $this->direction === Direction::LEFT ? '-' : '->';
        $namePart = $this->name ? $this->name : '';
        $labelPart = $this->label ? ":{$this->label}" : '';
        $propertiesJsonPart = empty($this->properties) ? '' : ' ' . $this->propertiesToString($parameters, $parametersCount);

        return "{$startArrow}[{$namePart}{$labelPart}{$propertiesJsonPart}]{$endArrow}";
    }
}
