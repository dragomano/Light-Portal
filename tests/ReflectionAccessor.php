<?php

declare(strict_types=1);

namespace Tests;

use ReflectionClass;
use ReflectionException;

class ReflectionAccessor
{
    private object $object;

    public function __construct(object $object)
    {
        $this->object = $object;
    }

    public function setProtectedProperty(mixed $property, mixed $value): void
    {
        $reflection = new ReflectionClass($this->object);

        try {
            $prop = $reflection->getProperty($property);
            $prop->setValue($this->object, $value);
        } catch (ReflectionException) {}
    }

    public function getProtectedProperty(mixed $property)
    {
        $reflection = new ReflectionClass($this->object);

        try {
            $prop = $reflection->getProperty($property);

            return $prop->getValue($this->object);
        } catch (ReflectionException $e) {
            return $e->getMessage();
        }
    }

    public function callProtectedMethod(string $method, array $args = []): mixed
    {
        $reflection = new ReflectionClass($this->object);

        try {
            $m = $reflection->getMethod($method);

            $params = $m->getParameters();
            $invokeArgs = [];

            foreach ($params as $i => $param) {
                if (array_key_exists($i, $args)) {
                    if ($param->isPassedByReference()) {
                        $invokeArgs[$i] = &$args[$i];
                    } else {
                        $invokeArgs[$i] = $args[$i];
                    }
                } else {
                    $invokeArgs[$i] = $param->isDefaultValueAvailable()
                        ? $param->getDefaultValue()
                        : null;
                }
            }

            return $m->invokeArgs($this->object, $invokeArgs);
        } catch (ReflectionException $e) {
            return $e->getMessage();
        }
    }
}
