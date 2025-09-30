<?php

namespace Danny50610\LaravelApacheAgeDriver\Query;

use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\InputStream;
use Antlr\Antlr4\Runtime\Tree\ParseTreeWalker;
use Danny50610\LaravelApacheAgeDriver\Models\Edge;
use Danny50610\LaravelApacheAgeDriver\Models\Path;
use Danny50610\LaravelApacheAgeDriver\Models\Vertex;
use Danny50610\LaravelApacheAgeDriver\Parser\AgtypeBaseListenerImpl;
use Danny50610\LaravelApacheAgeDriver\Parser\AgtypeLexer;
use Danny50610\LaravelApacheAgeDriver\Parser\AgtypeParser;
use Danny50610\LaravelApacheAgeDriver\Parser\Type\AgtypeListImpl;
use Exception;
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
        // ex: "[{"id": 281474976710657, "label": "", "properties": {"name": "Andres"}}::vertex, {"id": 1970324836974594, "label": "WORKS_AT", "end_id": 281474976710658, "start_id": 281474976710657, "properties": {}}::edge, {"id": 281474976710658, "label": "", "properties": {}}::vertex, {"id": 1970324836974593, "label": "WORKS_AT", "end_id": 281474976710658, "start_id": 281474976710659, "properties": {}}::edge, {"id": 281474976710659, "label": "", "properties": {"name": "Michael"}}::vertex]::path"

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
        } elseif (Str::endsWith($value, '::path')) {
            $newValue = new Path();
            
            $inputStream = InputStream::fromString($value);
            $lexer = new AgtypeLexer($inputStream);
            $tokes = new CommonTokenStream($lexer);
            $parser = new AgtypeParser($tokes);

            // lexer.removeErrorListeners();
            // lexer.addErrorListener(baseErrorListener);

            // parser.removeErrorListeners();
            // parser.addErrorListener(baseErrorListener);

            $agtypeListener = new AgtypeBaseListenerImpl();

            $walker = new ParseTreeWalker();
            $walker->walk($agtypeListener, $parser->agType());

            $output = $agtypeListener->getOutput();
            if ($output instanceof AgtypeListImpl) {

            } else {
                throw new Exception('Expected List');
            }
        }

        return $newValue;
    }
}
