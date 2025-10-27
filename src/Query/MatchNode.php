<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Illuminate\Database\Query\Grammars\Grammar;
use Override;

class MatchNode implements Clause
{
    use Concerns\WithProperties;

    public function __construct(
        public readonly ?string $name,
        protected readonly ?string $label,
        protected readonly array $properties,
    ) {
    }

    #[Override]
    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        // ex: ()
        // ex: (a)
        // ex: (a:Home)
        // ex: (b:Home {name: $v1})
        $namePart = $this->name ? $this->name : '';
        $labelPart = $this->label ? ":{$this->label}" : '';
        $propertiesJsonPart = empty($this->properties) ? '' : ' ' . $this->propertiesToString($grammar, $parameters, $parametersCount);
        return "({$namePart}{$labelPart}{$propertiesJsonPart})";
    }
}
