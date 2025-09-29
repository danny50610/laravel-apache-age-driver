<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Illuminate\Database\Query\Grammars\Grammar;
use Override;

class MatchNode extends MatchBase
{
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
