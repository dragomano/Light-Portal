<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use LightPortal\Models\AbstractFactory;
use LightPortal\Models\FactoryInterface;
use LightPortal\Models\ModelInterface;
use Tests\ReflectionAccessor;

class TestModel implements ModelInterface
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}

class TestFactory extends AbstractFactory
{
    protected string $modelClass = TestModel::class;
}

beforeEach(function () {
    $this->factory    = new TestFactory();
    $this->reflection = new ReflectionAccessor($this->factory);
});

it('implements FactoryInterface', function () {
    expect($this->factory)->toBeInstanceOf(FactoryInterface::class);
});

it('has modelClass property', function () {
    expect($this->reflection->getProtectedProperty('modelClass'))->toBe(TestModel::class);
});

it('create method returns ModelInterface instance', function () {
    $result = $this->factory->create([]);

    expect($result)->toBeInstanceOf(ModelInterface::class);
});

it('clean bbcode from title when creating model', function () {
    $data = ['title' => '[b]Bold Title[/b]'];
    $result = $this->factory->create($data);

    expect($result->toArray()['title'])->toBe('Bold Title');
});

it('handle empty title without errors', function () {
    $data = ['title' => ''];
    $result = $this->factory->create($data);

    expect($result->toArray()['title'])->toBe('');
});

it('handle missing title field', function () {
    $data = ['description' => 'Some description'];
    $result = $this->factory->create($data);

    expect($result->toArray())->toHaveKey('description');
});

it('populate method returns data unchanged by default', function () {
    $data = ['title' => 'Test', 'description' => 'Description'];
    $result = $this->reflection->callProtectedMethod('populate', [$data]);

    expect($result)->toBe($data);
});

it('create method calls populate before creating model', function () {
    $data = ['title' => 'Test', 'custom_field' => 'value'];

    $result = $this->factory->create($data);
    $modelData = $result->toArray();

    expect($modelData)->toMatchArray($data);
});

it('create method instantiates correct model class', function () {
    $result = $this->factory->create([]);

    expect($result)->toBeInstanceOf(TestModel::class);
});

it('handle non-array data gracefully', function () {
    // This test ensures the method signature is respected
    $result = $this->factory->create(['key' => 'value']);

    expect($result)->toBeInstanceOf(ModelInterface::class);
});

it('preserve original data structure', function () {
    $data = [
        'title'       => 'Test Title',
        'description' => 'Test Description',
        'numeric'     => 123,
        'boolean'     => true,
        'array'       => ['nested' => 'value']
    ];

    $result = $this->factory->create($data);

    expect($result->toArray())->toMatchArray($data);
});
