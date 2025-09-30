<?php

namespace Danny50610\LaravelApacheAgeDriver\Parser;

use Danny50610\LaravelApacheAgeDriver\Parser\Type\AgtypeListImpl;
use Danny50610\LaravelApacheAgeDriver\Parser\Type\AgtypeMapImpl;
use Danny50610\LaravelApacheAgeDriver\Parser\Type\AgtypeUnrecognizedList;
use Danny50610\LaravelApacheAgeDriver\Parser\Type\AgtypeUnrecognizedMap;
use Danny50610\LaravelApacheAgeDriver\Parser\Type\UnrecognizedObject;
use SplStack;

class AgtypeBaseListenerImpl extends AgtypeBaseListener
{
    private SplStack $objectStack;
    private SplStack $annotationMap;
    private mixed $rootObject;
    private mixed $lastValue;
    private bool $lastValueUndefined;
    private int $objectStackLength;

    public function __construct()
    {
        $this->objectStack = new SplStack();
        $this->annotationMap = new SplStack();
        $this->lastValueUndefined = true;
        $this->objectStackLength = 0;
    }

    private function pushObjectStack($object): void
    {
        $this->objectStackLength += 1;
        $this->objectStack->push($object);
    }

    private function popObjectStack()
    {
        $this->objectStackLength -= 1;
        return $this->objectStack->pop();
    }

    private function peekObjectStack()
    {
        return $this->objectStack->top();
    }

    private function mergeObjectIfTargetIsArray(): void
    {
        if ($this->objectStackLength >= 2) {
            $firstObject = $this->popObjectStack();
            $secondObject = $this->popObjectStack();

            if ($secondObject instanceof AgtypeListImpl) {
                $secondObject->append($firstObject);
                $this->pushObjectStack($secondObject);
            } else {
                $this->pushObjectStack($secondObject);
                $this->pushObjectStack($firstObject);
            }
        }
    }

    private function mergeObjectIfTargetIsMap(string $key, $value): void
    {
        $agtypeMap = $this->peekObjectStack();
        if ($agtypeMap instanceof AgtypeMapImpl) {
            $agtypeMap[$key] = $value;
        }
    }

    private function addObjectValue(): void
    {
        if ($this->objectStackLength !== 0) {
            $currentObject = $this->peekObjectStack();
            if ($currentObject instanceof AgtypeListImpl) {
                $currentObject->append($this->lastValue);
                $this->lastValueUndefined = true;
                return;
            }
        }
        $this->lastValueUndefined = false;
    }

    public function exitStringValue($ctx): void
    {
        $this->lastValue = $this->identString($ctx->STRING()->getText());
        $this->addObjectValue();
    }

    public function exitIntegerValue($ctx): void
    {
        $this->lastValue = (int) $ctx->INTEGER()->getText();
        $this->addObjectValue();
    }

    public function exitFloatValue($ctx): void
    {
        $this->lastValue = (float) $ctx->floatLiteral()->getText();
        $this->addObjectValue();
    }

    public function exitTrueBoolean($ctx): void
    {
        $this->lastValue = true;
        $this->addObjectValue();
    }

    public function exitFalseBoolean($ctx): void
    {
        $this->lastValue = false;
        $this->addObjectValue();
    }

    public function exitNullValue($ctx): void
    {
        $this->lastValue = null;
        $this->addObjectValue();
    }

    public function enterObjectValue($ctx): void
    {
        $agtypeMap = new AgtypeUnrecognizedMap();
        $this->pushObjectStack($agtypeMap);
    }

    public function enterArrayValue($ctx): void
    {
        $agtypeList = new AgtypeUnrecognizedList();
        $this->pushObjectStack($agtypeList);
    }

    public function exitPair($ctx): void
    {
        $name = $this->identString($ctx->STRING()->getText());
        if (!$this->lastValueUndefined) {
            $this->mergeObjectIfTargetIsMap($name, $this->lastValue);
            $this->lastValueUndefined = true;
        } else {
            $lastValue = $this->popObjectStack();
            $currentHeaderObject = $this->peekObjectStack();
            if ($currentHeaderObject instanceof AgtypeListImpl) {
                $currentHeaderObject->append($lastValue);
            } else {
                $this->mergeObjectIfTargetIsMap($name, $lastValue);
            }
        }
    }

    public function exitAgType($ctx): void
    {
        if ($this->objectStack->isEmpty()) {
            $this->rootObject = $this->lastValue;
            return;
        }
        $this->rootObject = $this->popObjectStack();
    }

    public function enterTypeAnnotation($ctx): void
    {
        $this->annotationMap->push($ctx->IDENT()->getText());
    }

    public function exitTypeAnnotation($ctx): void
    {
        $annotation = $this->annotationMap->pop();
        $currentObject = $this->peekObjectStack();
        if ($currentObject instanceof UnrecognizedObject) {
            $currentObject->setAnnotation($annotation);
        }
        $this->mergeObjectIfTargetIsArray();
    }

    private function identString(string $quotedString): string
    {
        return json_decode($quotedString);
    }

    public function getOutput()
    {
        return $this->rootObject;
    }
}