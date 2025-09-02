<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Danny50610\LaravelApacheAgeDriver\Models\Edge;
use Danny50610\LaravelApacheAgeDriver\Models\Vertex;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use stdClass;

class AfterQuery
{
    public function __invoke(Collection $results)
    {
        return $results->transform(function ($row) {
            if (!($row instanceof stdClass)) {
                if (is_string($row)) {
                    $newValue = $this->tryValueToModel($row);
                    if (!is_null($newValue)) {
                        return $newValue;
                    }
                }
                return $row;
            }

            $newRow = new stdClass();
            foreach ($row as $colunm => $value) {
                $newValue = $this->tryValueToModel($value);

                if (!is_null($newValue)) {
                    $newRow->{$colunm} = $newValue;
                } else {
                    $newRow->{$colunm} = $value;
                }
            }

            return $newRow;
        });
    }

    protected function tryValueToModel($value)
    {
        // $value
        // ex: "{"id": 844424930131969, "label": "Home", "properties": {}}::vertex"
        // ex: "{"id": 1407374883553281, "label": "RELTYPE", "end_id": 1125899906842626, "start_id": 1125899906842625, "properties": {}}::edge"

        $newValue = null;
        if (Str::endsWith($value, '::vertex')) {
            $valueMap = json_decode(substr($value, 0, -8), true);
            $newValue = new Vertex(
                $valueMap['id'],
                $valueMap['label'],
                $valueMap['properties'],
            );
        } elseif (Str::endsWith($value, '::edge')) {
            $valueMap = json_decode(substr($value, 0, -6), true);
            $newValue = new Edge(
                $valueMap['id'],
                $valueMap['label'],
                $valueMap['properties'],
                $valueMap['start_id'],
                $valueMap['end_id'],
            );
        }

        return $newValue;
    }
}
