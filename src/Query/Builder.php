<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Enums\Direction;
use Danny50610\LaravelApacheAgeDriver\Query\Concerns\Clause;
use Danny50610\LaravelApacheAgeDriver\Query\Concerns\CreateClause;
use Danny50610\LaravelApacheAgeDriver\Query\Concerns\MatchClause;
use Danny50610\LaravelApacheAgeDriver\Query\Concerns\VariableLengthInfo;
use Illuminate\Database\Query\Grammars\Grammar;
use LogicException;

class Builder
{
    protected ?string $queryString = null;

    // ex: '(a agtype, b agtype, r agtype)'
    protected ?string $as = null;

    protected array $parameters = [];

    /** @var array<int, array<int, Clause>> */
    protected array $rows = [];

    public function raw(string $queryString, string $as, array $parameters): static
    {
        $this->queryString = $queryString;
        $this->as = $as;
        $this->parameters = $parameters;

        return $this;
    }

    public function matchNode(?string $name = null, ?string $label = null, array $properties = [], ?string $assign = null): static
    {
        $this->rows[] = [
            new MatchNode($name, $label, $properties, $assign),
        ];

        return $this;
    }

    public function withMatchNode(?string $name = null, ?string $label = null, array $properties = []): static
    {
        $lastRow =& $this->getLastRow();
        if (!($lastRow[count($lastRow) - 1] instanceof MatchEdge)) {
            throw new LogicException('The last clause is not a MatchEdge');
        }

        $lastRow[] = new MatchNode($name, $label, $properties, null);

        return $this;
    }   

    public function withMatchEdge(Direction $direction, ?string $name = null, ?string $label = null, array $properties = [], ?VariableLengthInfo $variableLengthInfo = null): static
    {
        $lastRow =& $this->getLastRow();
        if (!($lastRow[count($lastRow) - 1] instanceof MatchNode)) {
            throw new LogicException('The last clause is not a MatchNode');
        }

        $lastRow[] = new MatchEdge($direction, $name, $label, $properties, $variableLengthInfo);

        return $this;
    }

    public function matchRaw(string $queryString, array $bindings = []): static
    {
        $this->rows[] = [
            new MatchRaw($queryString, $bindings),
        ];

        return $this;
    }

    protected function &getLastRow(): array
    {
        $lastIndex = count($this->rows) - 1;
        if ($lastIndex < 0) {
            throw new LogicException('Need call matchNode() first');
        }

        return $this->rows[$lastIndex];
    }

    // TODO: orWhere
    public function where(string $column, string $operator, mixed $value): static
    {
        $this->rows[] = [
            new WhereClause($column, $operator, $value),
        ];

        return $this;
    }

    public function return(string $return): static
    {
        $this->rows[] = [
            new ReturnClause($return),
        ];

        return $this;
    }

    public function createNode(?string $name = null, ?string $label = null, array $properties = [], ?string $assign = null): static
    {
        $this->rows[] = [
            new CreateNode($name, $label, $properties, $assign),
        ];

        return $this;
    }

    public function withCreateNode(?string $name = null, ?string $label = null, array $properties = []): static
    {
        $lastRow =& $this->getLastRow();
        if (!($lastRow[count($lastRow) - 1] instanceof CreateEdge)) {
            throw new LogicException('The last clause is not a CreateEdge');
        }
        
        $lastRow[] = new CreateNode($name, $label, $properties, null);

        return $this;
    }

    public function withCreateEdge(Direction $direction, ?string $name = null, ?string $label = null, array $properties = []): static
    {
        $lastRow =& $this->getLastRow();
        if (!($lastRow[count($lastRow) - 1] instanceof CreateNode)) {
            throw new LogicException('The last clause is not a CreateNode');
        }

        $lastRow[] = new CreateEdge($direction, $name, $label, $properties);

        return $this;
    }

    public function createRaw(string $queryString, array $bindings = []): static
    {
        $this->rows[] = [
            new CreateRaw($queryString, $bindings),
        ];

        return $this;
    }

    public function set(array $values): static
    {
        $this->rows[] = [
            new SetClause($values),
        ];

        return $this;
    }

    public function delete(string|array $name, bool $isDetached = false): static
    {
        $this->rows[] = [
            new DeleteClause($name, $isDetached),
        ];

        return $this;
    }

    public function remove(string|array $propertyName): static
    {
        $this->rows[] = [
            new RemoveClause($propertyName),
        ];

        return $this;
    }

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
        $returns = [];
        if (count($this->rows) > 0) {
            foreach ($this->rows as $rowIndex => $row) {
                $rowStringParts = '';
                if ($rowIndex === 0) {
                    if ($row[0] instanceof MatchClause) {
                        $rowStringParts .= 'MATCH ';
                    } elseif ($row[0] instanceof CreateClause) {
                        $rowStringParts .= 'CREATE ';
                    }
                } else {
                    if (!($this->rows[$rowIndex - 1][0] instanceof MatchClause) && $row[0] instanceof MatchClause) {
                        $rowStringParts .= ' MATCH ';
                    } elseif (!($this->rows[$rowIndex - 1][0] instanceof CreateClause) && $row[0] instanceof CreateClause) {
                        $rowStringParts .= ' CREATE ';
                    } elseif (!($this->rows[$rowIndex - 1][0] instanceof WhereClause) && $row[0] instanceof WhereClause) {
                        $rowStringParts .= 'WHERE ';
                    } elseif ($this->rows[$rowIndex - 1][0] instanceof WhereClause && $row[0] instanceof WhereClause) {
                        $rowStringParts .= 'AND ';
                    }
                }

                foreach ($row as $clause) {
                    $rowStringParts .= $clause->toQueryString($grammar, $parameter, $parametersCount);

                    if ($clause instanceof ReturnClause) {
                        $returns = array_merge($returns, $clause->getReturn());
                    }
                }

                if ($rowIndex > 0) {
                    if ($this->rows[$rowIndex][0] instanceof MatchClause && $this->rows[$rowIndex - 1][0] instanceof MatchClause) {
                        $rowStringParts = ', ' . $rowStringParts;
                    } elseif ($this->rows[$rowIndex][0] instanceof CreateClause && $this->rows[$rowIndex - 1][0] instanceof CreateClause) {
                        $rowStringParts = ', ' . $rowStringParts;
                    } else {
                        $rowStringParts = ' ' . $rowStringParts;
                    }
                }
                $this->queryString .= $rowStringParts;
            }
        }

        if (is_null($this->as)) {
            if (count($returns) == 1 && $returns[0] === '*') {
                $returns = collect($this->rows)
                    ->flatMap(fn ($row) => $row)
                    ->filter(fn ($clause) => $clause instanceof MatchNode || $clause instanceof MatchEdge)
                    ->map(fn ($clause) => $clause->name)
                    ->values()
                    ->all();
            }
        
            $this->as = '(' . collect($returns)
                    ->map(fn ($item) => $item . ' agtype')
                    ->join(', ') . ')';
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
