<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Enums\Direction;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Support\Arr;
use LogicException;

class Builder
{
    protected ?string $queryString = null;

    protected ?string $as = null;

    protected array $parameters = [];

    /** @var array<int, array<MatchBase>> */
    protected array $matches = [];

    /** @var array<int, WherePart> */
    protected array $wheres = [];

    /** @var array<int, array<CreateNode>> */
    protected array $creates = [];

    protected array $returns = [];

    public function raw(string $queryString, string $as, array $parameters): static
    {
        $this->queryString = $queryString;
        $this->as = $as;
        $this->parameters = $parameters;

        return $this;
    }

    public function matchNode(?string $name = null, ?string $label = null, array $properties = []): static
    {
        /** @var array<int, MatchBase>  */
        $newMatches = [];
        $newMatches[] = new MatchNode($name, $label, $properties);

        $this->matches[] = $newMatches;

        return $this;
    }

    public function withMatchNode(?string $name = null, ?string $label = null, array $properties = []): static
    {
        $lastMatches =& $this->getLastMatches();
        $lastMatches[] = new MatchNode($name, $label, $properties);

        return $this;
    }   

    public function withMatchEdge(Direction $direction, ?string $name = null, ?string $label = null, array $properties = []): static
    {
        $lastMatches =& $this->getLastMatches();
        $lastMatches[] = new MatchEdge($direction, $name, $label, $properties);

        return $this;
    }

    protected function &getLastMatches(): array
    {
        $lastIndex = count($this->matches) - 1;
        if ($lastIndex < 0) {
            throw new LogicException('Need call matchNode() first');
        }
        $lastMatches =& $this->matches[$lastIndex];
        $lastMatchesIndex = count($lastMatches) - 1;
        if ($lastMatchesIndex < 0) {
            throw new LogicException('Need call matchNode() first');
        }

        return $lastMatches;
    }

    protected function &getLastCreates(): array
    {
        $lastIndex = count($this->creates) - 1;
        if ($lastIndex < 0) {
            throw new LogicException('Need call createNode() first');
        }
        $lastCreates =& $this->creates[$lastIndex];
        $lastCreatesIndex = count($lastCreates) - 1;
        if ($lastCreatesIndex < 0) {
            throw new LogicException('Need call createNode() first');
        }

        return $lastCreates;
    }

    // TODO: matchRaw (append)

    // TODO: orWhere
    public function where(string $column, string $operator, string $value): static
    {
        $this->wheres[] = new WherePart($column, $operator, $value);

        return $this;
    }

    public function return(string $return): static
    {
        $this->returns[] = $return;

        return $this;
    }

    public function createNode(?string $name = null, ?string $label = null, array $properties = [], ?string $assign = null): static
    {
        $newCreates = [];
        $newCreates[] = new CreateNode($name, $label, $properties, $assign);

        $this->creates[] = $newCreates;

        return $this;
    }

    public function withCreateNode(?string $name = null, ?string $label = null, array $properties = []): static
    {
        $lastCreates =& $this->getLastCreates();
        $lastCreates[] = new CreateNode($name, $label, $properties, null);

        return $this;
    }

    public function withCreateEdge(Direction $direction, ?string $name = null, ?string $label = null, array $properties = []): static
    {
        $lastCreates =& $this->getLastCreates();
        $lastCreates[] = new CreateEdge($direction, $name, $label, $properties);

        return $this;
    }

    // TODO: createRaw (append)

    public function setAs(array $asList): static
    {
        $this->as = '(' . collect($asList)
            ->map(fn ($item) => $item . ' agtype')
            ->join(', ') . ')';

        return $this;
    }

    public function build(Grammar $grammar)
    {
        if (!is_null($this->queryString)) {
            return;
        }

        $parameter = [];
        $parametersCount = 1;

        $this->queryString = '';
        if (count($this->matches) > 0) {
            $this->queryString .= 'MATCH ';
            $this->queryString .= collect($this->matches)
                ->map(function ($matches) use ($grammar, &$parameter, &$parametersCount) {
                    return collect($matches)->map(function ($match) use ($grammar, &$parameter, &$parametersCount) {
                        return $match->toQueryString($grammar, $parameter, $parametersCount);
                    })->join('');
                })->join(', ');
        }

        if (count($this->wheres) > 0) {
            $this->queryString .= 'WHERE ';
            $this->queryString .= collect($this->wheres)
                ->map(function ($where) use (&$parameter, &$parametersCount) {
                    return $where->toQueryString($parameter, $parametersCount);
                })->join(' AND ');
            $this->queryString .= ' ';
        }

        if (count($this->creates) > 0) {
            $this->queryString .= 'CREATE ';
            $this->queryString .= collect($this->creates)
                ->map(function ($creates) use ($grammar, &$parameter, &$parametersCount) {
                    return collect($creates)->map(function ($create) use ($grammar, &$parameter, &$parametersCount) {
                        return $create->toQueryString($grammar, $parameter, $parametersCount);
                    })->join('');
                })->join(', ');
        }

        if (count($this->returns) > 0) {
            $this->queryString .= ' RETURN ';
            $this->queryString .= Arr::join($this->returns, ', ');

            // ex: '(a agtype, b agtype, r agtype)'
            if (count($this->returns) == 1 && $this->returns[0] === '*') {
                $returns = collect($this->matches)
                    ->map(fn($matches) => collect($matches)->map(fn($match) => $match->name)->filter())
                    ->flatten();
            } else {
                $returns = $this->returns;
            }

            if (is_null($this->as)) {
                $this->as = '(' . collect($returns)
                        ->map(fn ($item) => $item . ' agtype')
                        ->join(', ') . ')';
            }
        }

        $this->parameters = $parameter;
    }

    public function getQueryString(): string
    {
        return '$$' . $this->queryString . '$$';
    }

    public function getAs(): string
    {
        return $this->as;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
