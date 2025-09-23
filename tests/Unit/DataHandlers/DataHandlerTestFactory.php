<?php

declare(strict_types=1);

namespace Tests\Unit\DataHandlers;

use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\RequestInterface;
use InvalidArgumentException;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;
use Tests\AppMockRegistry;
use Tests\Fixtures;

class DataHandlerTestFactory
{
    use DataHandlerTestTrait;

    private const AVAILABLE_ENTITIES = [
        'blocks',
        'pages',
        'categories',
        'tags',
        'params',
        'translations'
    ];

    public function createMockData(string $entity, int $count = 1): array
    {
        if (! in_array($entity, self::AVAILABLE_ENTITIES)) {
            throw new InvalidArgumentException(
                "Unknown entity type: $entity. Available types: " .
                implode(', ', self::AVAILABLE_ENTITIES)
            );
        }

        return match ($entity) {
            'blocks'      => Fixtures::getBlocksData($count),
            'pages'       => Fixtures::getPagesData($count),
            'categories'  => Fixtures::getCategoriesData($count),
            'tags'        => Fixtures::getTagsData($count),
            'params'      => Fixtures::getParamsData($count),
            'translation' => Fixtures::getTranslationData($count),
            default       => [],
        };
    }

    public function createMockRequest(string $entity, array $options = []): RequestInterface
    {
        $defaultMethods = [
            'isEmpty' => $options['isEmpty'] ?? false,
            'hasNot'  => $options['hasNot'] ?? false,
            'get'     => $options['get'] ?? null,
            'has'     => $options['has'] ?? false,
            'post'    => $options['post'] ?? [],
            'files'   => $options['files'] ?? [],
        ];

        $entityMethods = $this->getEntitySpecificRequestMethods($entity, $options);
        $allMethods = array_merge($defaultMethods, $entityMethods);

        return $this->mockRequestMethods($allMethods);
    }

    public function createMockDatabase(array $options = []): Mock|(MockInterface&DatabaseInterface)
    {
        $mock = $this->createDatabaseMock();

        if (isset($options['additionalExpectations'])) {
            foreach ($options['additionalExpectations'] as $method => $returnValue) {
                $mock->shouldReceive($method)->andReturn($returnValue);
            }
        }

        AppMockRegistry::set(DatabaseInterface::class, $mock);

        return $mock;
    }

    public function createTestEnvironment(string $entity, array $options = []): array
    {
        $environment = [];

        $environment['data'] = $this->createMockData($entity, $options['dataCount'] ?? 1);

        $environment['request'] = $this->createMockRequest($entity, $options['requestOptions'] ?? []);

        if (! isset($options['skipDatabase']) || $options['skipDatabase'] === false) {
            $environment['database'] = $this->createMockDatabase($options['databaseOptions'] ?? []);
        }

        if (isset($options['additionalMocks'])) {
            foreach ($options['additionalMocks'] as $interface => $mockOptions) {
                $environment['additionalMocks'][$interface] = $this->createCustomMock($interface, $mockOptions);
            }
        }

        return array_merge($environment, $this->setupDataHandlerTestEnvironment($options));
    }

    private function createCustomMock(string $interface, array $options): Mockery\MockInterface
    {
        $mock = Mockery::mock($interface);

        if (isset($options['methods'])) {
            foreach ($options['methods'] as $method => $returnValue) {
                if (is_callable($returnValue)) {
                    $mock->shouldReceive($method)->andReturnUsing($returnValue);
                } else {
                    $mock->shouldReceive($method)->andReturn($returnValue);
                }
            }
        }

        AppMockRegistry::set($interface, $mock);

        return $mock;
    }

    private function getEntitySpecificRequestMethods(string $entity, array $options): array
    {
        $methods = [];

        $methods['isEmpty'] = $options['isEmpty'] ?? fn($key) => $key !== $entity;
        $methods['hasNot']  = $options['hasNot'] ?? fn($key) => $key !== $entity;
        $methods['has']     = $options['has'] ?? fn($key) => $key === $entity;

        switch ($entity) {
            case 'blocks':
                $methods['get'] = $options['get'] ?? fn($key) =>
                    $key === 'blocks' ? [1, 2, 3] : null;
                break;

            case 'pages':
                $methods['get'] = $options['get'] ?? fn($key) =>
                    $key === 'pages' ? [1] : null;
                break;

            case 'categories':
                $methods['get'] = $options['get'] ?? fn($key) =>
                    $key === 'categories' ? [1, 2] : null;
                break;

            case 'tags':
                $methods['get'] = $options['get'] ?? fn($key) =>
                    $key === 'tags' ? [1, 2, 3, 4] : null;
                break;
        }

        return $methods;
    }

    public static function withDefaults(): self
    {
        return new self();
    }

    public function cleanup(): void
    {
        $this->tearDownDataHandlerTestEnvironment();
    }
}
