<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Illuminate\Database\Query\Grammars\Grammar;
use Override;

class CreateNode extends CreateBase
{
    use Concerns\WithProperties;

    public function __construct(
        public readonly ?string $name,
        protected readonly ?string $label,
        protected readonly array $properties,
        protected readonly ?string $assign,
    ) {
    }

    #[Override]
    public function toQueryString(Grammar $grammar, array &$parameters, int &$parametersCount): string
    {
        // ex: ()
        // ex: (a)
        // ex: (a:Home)
        // ex: (b:Home {name: $v1})
        $assignPart = $this->assign ? "{$this->assign} = " : '';
        $namePart = $this->name ? $this->name : '';
        $labelPart = $this->label ? ":{$this->label}" : '';
        $propertiesJsonPart = empty($this->properties) ? '' : ' ' . $this->propertiesToString($grammar, $parameters, $parametersCount);
        return "$assignPart({$namePart}{$labelPart}{$propertiesJsonPart})";
    }
}