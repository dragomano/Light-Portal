<?php

declare(strict_types=1);

namespace Tests;

use ReflectionClass;
use ReflectionException;

class ReflectionAccessor
{
    private ReflectionClass $reflection;

    private ?object $object;

    public function __construct(object|string $objectOrClass)
    {
        try {
            $this->reflection = new ReflectionClass($objectOrClass);
        } catch (ReflectionException) {}

        if (is_string($objectOrClass)) {
            $this->object = null;
        } else {
            $this->object = $objectOrClass;
        }
    }

    public function setProperty(mixed $property, mixed $value): void
    {
        try {
            $prop = $this->reflection->getProperty($property);
            $prop->setValue($this->object, $value);
        } catch (ReflectionException) {}
    }

    public function getProperty(string $property): mixed
    {
        try {
            $prop = $this->reflection->getProperty($property);

            return $prop->getValue($this->object);
        } catch (ReflectionException $e) {
            return $e->getMessage();
        }
    }

    public function callMethod(string $method, array $args = []): mixed
    {
        try {
            $method = $this->reflection->getMethod($method);

            return $method->invokeArgs($this->object, $args);
        } catch (ReflectionException $e) {
            return $e->getMessage();
        }
    }
}
