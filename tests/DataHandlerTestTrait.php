<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use InvalidArgumentException;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use LightPortal\Database\Operations\PortalDelete;
use LightPortal\Database\Operations\PortalInsert;
use LightPortal\Database\Operations\PortalReplace;
use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\Operations\PortalUpdate;
use LightPortal\Database\PortalAdapterInterface;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Database\PortalTransactionInterface;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\RequestInterface;
use Mockery;
use ReflectionClass;
use ReflectionException;
use SimpleXMLElement;

trait DataHandlerTestTrait
{
    protected function mockRequestMethods(array $methods = []): RequestInterface
    {
        $requestMock = mock(RequestInterface::class);

        $defaultMethods = [
            'isEmpty' => false,
            'hasNot'  => false,
            'get'     => null,
            'has'     => false,
            'post'    => [],
            'files'   => [],
        ];

        $allMethods = array_merge($defaultMethods, $methods);

        foreach ($allMethods as $method => $returnValue) {
            if (is_callable($returnValue)) {
                $requestMock->shouldReceive($method)->andReturnUsing($returnValue);
            } else {
                $requestMock->shouldReceive($method)->andReturn($returnValue);
            }
        }

        AppMockRegistry::set(RequestInterface::class, $requestMock);

        return $requestMock;
    }

    protected function assertExtendsBaseClass(object $object, string $expectedBaseClass, ?string $entity = null): void
    {
        expect($object)->toBeInstanceOf($expectedBaseClass);

        if ($entity !== null && method_exists($object, 'getEntity')) {
            expect($object->getEntity())->toBe($entity);
        }
    }


    protected function setupDataHandlerTestEnvironment(array $options = []): array
    {
        $mocks = [];

        $mocks['request'] = $this->mockRequestMethods($options['requestMethods'] ?? []);

        if (isset($options['additionalMocks'])) {
            foreach ($options['additionalMocks'] as $interface => $mock) {
                AppMockRegistry::set($interface, $mock);
                $mocks[$interface] = $mock;
            }
        }

        return $mocks;
    }

    protected function createPartialMockWithMethods(
        string $class,
        array $constructorArgs = [],
        array $methods = []
    ): Mockery\MockInterface
    {
        $mock = mock($class, $constructorArgs)->makePartial()->shouldAllowMockingProtectedMethods();

        foreach ($methods as $method => $returnValue) {
            $mock->shouldReceive($method)->andReturn($returnValue);
        }

        return $mock;
    }

    protected function tearDownDataHandlerTestEnvironment(): void
    {
        AppMockRegistry::clear();
        Mockery::close();
    }

    protected function createErrorHandlerMock(array $options = []): Mockery\MockInterface
    {
        $mock = mock(ErrorHandlerInterface::class);
        $mock->shouldReceive('fatal')->andReturnNull();
        $mock->shouldReceive('log')->andReturnNull();
        $mock->shouldReceive('handle')->andReturnNull();
        $mock->shouldReceive('setLevel')->andReturnNull();
        $mock->shouldReceive('getLevel')->andReturn('error');
        $mock->shouldReceive('clear')->andReturnNull();
        $mock->shouldReceive('getLogs')->andReturn([]);

        if (isset($options['additionalExpectations'])) {
            foreach ($options['additionalExpectations'] as $method => $returnValue) {
                if (is_callable($returnValue)) {
                    $mock->shouldReceive($method)->andReturnUsing($returnValue);
                } else {
                    $mock->shouldReceive($method)->andReturn($returnValue);
                }
            }
        }

        return $mock;
    }

    protected function createDatabaseMock(): Mockery\MockInterface
    {
        $connectionMock = mock(ConnectionInterface::class);

        $transactionMock = mock(PortalTransactionInterface::class);
        $transactionMock->allows([
            'begin'    => $connectionMock,
            'commit'   => $connectionMock,
            'rollback' => $connectionMock,
        ]);

        $resultMock = mock(ResultInterface::class);
        $resultMock->allows([
            'getAffectedRows'   => 1,
            'getGeneratedValue' => 1,
        ]);

        $whereMock = mock();
        $whereMock->allows([
            'in'      => $whereMock,
            'equalTo' => $whereMock,
            'or'      => $whereMock,
        ]);

        $updateMock = mock(PortalUpdate::class);
        $updateMock->allows([
            'set'     => $updateMock,
            'where'   => $whereMock,
            'execute' => $resultMock,
            'table'   => $updateMock,
            'columns' => $updateMock,
        ]);

        $replaceMock = mock(PortalReplace::class);
        $replaceMock->allows([
            'setConflictKeys' => $replaceMock,
            'batch'           => $replaceMock,
        ]);

        $insertMock = mock(PortalInsert::class)
            ->shouldReceive('batch')->andReturnSelf()
            ->getMock();

        $selectMock = mock(PortalSelect::class);
        $selectMock->allows([
            'from'    => $selectMock,
            'join'    => $selectMock,
            'where'   => $whereMock,
            'orderBy' => $selectMock,
            'limit'   => $selectMock,
            'offset'  => $selectMock,
        ]);

        $portalSqlMock = mock(PortalSqlInterface::class);
        $portalSqlMock->allows([
            'getPrefix'      => 'lp_',
            'tableExists'    => true,
            'columnExists'   => true,
            'getAdapter'     => mock(PortalAdapterInterface::class),
            'getTransaction' => $transactionMock,
            'select'         => $selectMock,
            'insert'         => $insertMock,
            'update'         => $updateMock,
            'delete'         => mock(PortalDelete::class),
            'replace'        => $replaceMock,
            'execute'        => $resultMock,
            'query'          => $resultMock,
        ]);

        return $portalSqlMock;
    }

    protected function createTranslationHandlerMock(array $options = []): Mockery\MockInterface
    {
        $defaults = [
            'extractTranslationsReturn' => [],
            'extractParamsReturn'       => [],
            'extractCommentsReturn'     => [],
            'replaceTranslationsCount'  => 'once',
            'replaceParamsCount'        => 'once',
            'replaceCommentsCount'      => 'once',
            'insertDataReturn'          => [1],
            'startTransactionCount'     => 'once',
            'finishTransactionCount'    => 'once',
        ];

        $config = array_merge($defaults, $options);

        $mock = mock();

        $mock->shouldReceive('extractTranslations')
            ->andReturn($config['extractTranslationsReturn']);

        $mock->shouldReceive('extractParams')
            ->andReturn($config['extractParamsReturn']);

        $mock->shouldReceive('extractComments')
            ->andReturn($config['extractCommentsReturn']);

        $mock->shouldReceive('insertData')
            ->andReturn($config['insertDataReturn']);

        $countMethods = [
            'replaceTranslations' => $config['replaceTranslationsCount'],
            'replaceParams'       => $config['replaceParamsCount'],
            'replaceComments'     => $config['replaceCommentsCount'],
        ];

        foreach ($countMethods as $method => $count) {
            switch ($count) {
                case 'once':
                    $mock->shouldReceive($method)->once()->andReturn([1]);

                    break;

                case 'twice':
                    $mock->shouldReceive($method)->twice()->andReturn([1]);

                    break;

                case 'never':
                    $mock->shouldReceive($method)->never();

                    break;

                default:
                    if (is_int($count)) {
                        $mock->shouldReceive($method)->times($count)->andReturn([1]);
                    } else {
                        $mock->shouldReceive($method)->once()->andReturn([1]);
                    }

                    break;
            }
        }

        $transactionMethods = [
            'startTransaction'  => $config['startTransactionCount'],
            'finishTransaction' => $config['finishTransactionCount'],
        ];

        foreach ($transactionMethods as $method => $count) {
            switch ($count) {
                case 'once':
                    $mock->shouldReceive($method)->once();

                    break;

                case 'twice':
                    $mock->shouldReceive($method)->twice();

                    break;

                case 'never':
                    $mock->shouldReceive($method)->never();

                    break;

                default:
                    if (is_int($count)) {
                        $mock->shouldReceive($method)->times($count);
                    } else {
                        $mock->shouldReceive($method)->once();
                    }

                    break;
            }
        }

        return $mock;
    }

    /**
     * @throws Exception
     */
    protected function createXmlMock(array $xmlData = [], string $entity = 'blocks'): SimpleXMLElement
    {
        if (empty($xmlData)) {
            $xmlString = '<?xml version="1.0" encoding="UTF-8"?><root><item id="1"><title>Test</title><content>Test Content</content></item></root>';
        } else {
            $xmlString = $this->arrayToXml($xmlData, $entity);
        }

        return new SimpleXMLElement($xmlString);
    }

    private function arrayToXml(array $data, string $entity = 'blocks'): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<light_portal>';
        $xml .= '<' . $entity . '>';

        foreach ($data as $key => $value) {
            if ($key === 'item' && is_array($value)) {
                foreach ($value as $item) {
                    $xml .= '<item';
                    $idKey = $entity . '_id';
                    if (is_array($item) && isset($item[$idKey])) {
                        $xml .= ' ' . $idKey . '="' . htmlspecialchars((string) $item[$idKey]) . '"';
                    }

                    $xml .= '>';
                    if (is_array($item)) {
                        foreach ($item as $itemKey => $itemValue) {
                            if ($itemKey === $idKey) {
                                continue; // Already in attribute
                            }

                            if (is_array($itemValue)) {
                                // Handle different array types
                                $xml .= '<' . $itemKey . '>';
                                if ($this->isMultilingualArray($itemValue)) {
                                    // Multilingual content: titles, contents, descriptions
                                    foreach ($itemValue as $lang => $content) {
                                        $xml .= '<' . rtrim($itemKey, 's') . ' lang="' . htmlspecialchars($lang) . '">';
                                        if (is_array($content)) {
                                            $xml .= json_encode($content); // For nested arrays
                                        } else {
                                            $xml .= htmlspecialchars((string) $content);
                                        }

                                        $xml .= '</' . rtrim($itemKey, 's') . '>';
                                    }
                                } elseif ($this->isCommentsArray($itemValue)) {
                                    // Comments array
                                    foreach ($itemValue as $comment) {
                                        $xml .= '<' . rtrim($itemKey, 's') . '>';
                                        foreach ($comment as $commentKey => $commentValue) {
                                            $xml .= '<' . $commentKey . '>' . htmlspecialchars((string) $commentValue) . '</' . $commentKey . '>';
                                        }

                                        $xml .= '</' . rtrim($itemKey, 's') . '>';
                                    }
                                } else {
                                    // Other arrays like params
                                    foreach ($itemValue as $subKey => $subValue) {
                                        $xml .= '<' . $subKey . '>' . htmlspecialchars((string) $subValue) . '</' . $subKey . '>';
                                    }
                                }

                                $xml .= '</' . $itemKey . '>';
                            } else {
                                $xml .= '<' . $itemKey . '>' . htmlspecialchars((string) $itemValue) . '</' . $itemKey . '>';
                            }
                        }
                    }
                    $xml .= '</item>';
                }
            }
        }

        $xml .= '</' . $entity . '>';
        $xml .= '</light_portal>';

        return $xml;
    }

    private function isMultilingualArray(array $array): bool
    {
        $languages = ['english', 'russian', 'german', 'french', 'spanish', 'italian', 'dutch', 'polish', 'portuguese', 'chinese'];

        foreach (array_keys($array) as $key) {
            if (in_array($key, $languages)) {
                return true;
            }
        }

        return false;
    }

    private function isCommentsArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        $firstItem = reset($array);

        return is_array($firstItem) && isset($firstItem['id'], $firstItem['parent_id'], $firstItem['author_id'], $firstItem['message']);
    }

    protected function createXmlElementMocks(array $elementData): array
    {
        $mocks = [];

        foreach ($elementData as $element) {
            $elementMock = mock(SimpleXMLElement::class);

            // Set up element attributes and content
            if (is_array($element)) {
                foreach ($element as $key => $value) {
                    if ($key === 'attributes') {
                        // Mock attributes method
                        $elementMock->shouldReceive('attributes')
                            ->andReturnUsing(function () use ($value) {
                                $attrXml = '<root';
                                foreach ($value as $attrKey => $attrValue) {
                                    $attrXml .= ' ' . $attrKey . '="' . htmlspecialchars($attrValue) . '"';
                                }
                                $attrXml .= '/>';
                                return new SimpleXMLElement($attrXml);
                            });
                    } else {
                        // Mock __toString for element content with null handling
                        $elementMock->shouldReceive('__toString')
                            ->andReturn($this->convertValueToString($value));
                    }
                }
            } else {
                // Simple string content
                $elementMock->shouldReceive('__toString')
                    ->andReturn($this->convertValueToString($element));
            }

            // Mock count method for children
            $elementMock->shouldReceive('count')
                ->andReturn(0);

            // Mock children method to return empty array
            $elementMock->shouldReceive('children')
                ->andReturn([]);

            // Mock xpath method
            $elementMock->shouldReceive('xpath')
                ->with(Mockery::any())
                ->andReturn([]);

            // Mock attributes method with empty result if not set
            $elementMock->shouldReceive('attributes')
                ->andReturn(new SimpleXMLElement('<root/>'));

            $mocks[] = $elementMock;
        }

        return $mocks;
    }

    protected function convertValueToString($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    protected function createXmlImporterMock(string $importerClass, array $options = []): Mockery\MockInterface
    {
        $mock = mock($importerClass)->makePartial()->shouldAllowMockingProtectedMethods();

        $defaults = [
            'xmlData'                   => [],
            'entity'                    => 'blocks',
            'parseXmlReturn'            => true,
            'processItemsReturn'        => null,
            'extractTranslationsReturn' => [],
            'extractParamsReturn'       => [],
            'extractCommentsReturn'     => $config['xmlData']['item'][0]['comments'] ?? [],
            'insertDataReturn'          => [1],
        ];

        $config = array_merge($defaults, $options);

        $mock->shouldReceive('parseXml')
            ->andReturn($config['parseXmlReturn']);

        $mock->shouldReceive('extractTranslations')
            ->andReturn($config['extractTranslationsReturn']);

        $mock->shouldReceive('extractParams')
            ->andReturn($config['extractParamsReturn']);

        $mock->shouldReceive('extractComments')
            ->andReturn($config['extractCommentsReturn']);

        $mock->shouldReceive('insertData')
            ->andReturn($config['insertDataReturn']);

        if ($config['processItemsReturn'] !== null) {
            $mock->shouldReceive('processItems')
                ->andReturn($config['processItemsReturn']);
        }

        if (! empty($config['xmlData'])) {
            $xmlMock = $this->createXmlMock($config['xmlData'], $config['entity']);
            $reflection = new ReflectionClass($importerClass);

            if ($reflection->hasProperty('xml')) {
                $xmlProperty = $reflection->getProperty('xml');
                $xmlProperty->setValue($mock, $xmlMock);
            }
        }

        $mock->shouldReceive('startTransaction')->andReturn(true);
        $mock->shouldReceive('finishTransaction')->andReturn(true);
        $mock->shouldReceive('replaceTranslations')->andReturn([1]);
        $mock->shouldReceive('replaceParams')->andReturn([1]);
        $mock->shouldReceive('replaceComments')->andReturn([1]);

        return $mock;
    }

    /**
     * @throws ReflectionException
     */
    protected function setXmlOnImport(object $import, string $xmlString): void
    {
        $xml = simplexml_load_string($xmlString);
        $reflection = new ReflectionClass($import);
        $xmlProperty = $reflection->getProperty('xml');
        $xmlProperty->setValue($import, $xml);
    }

    protected function setupImportMocks(Mockery\MockInterface $import, array $options = []): void
    {
        $defaults = [
            'parseXml'            => true,
            'extractTranslations' => [],
            'extractParams'       => [],
            'extractComments'     => [],
            'insertDataReturn'    => [1],
            'replaceTranslations' => null,
            'replaceParams'       => null,
            'replaceComments'     => null,
            'tableName'           => 'lp_blocks', // Default table name
            'insertDataArgs'      =>  ['replace', Mockery::any(), Mockery::any(), ['id']], // Default args
        ];

        $config = array_merge($defaults, $options);

        $import->shouldReceive('parseXml')->andReturn($config['parseXml']);

        if ($config['extractTranslations'] !== null) {
            $import->shouldReceive('extractTranslations')->andReturn($config['extractTranslations']);
        }

        if ($config['extractParams'] !== null) {
            $import->shouldReceive('extractParams')->andReturn($config['extractParams']);
        }

        if ($config['extractComments'] !== null) {
            $import->shouldReceive('extractComments')->andReturn($config['extractComments']);
        }

        if ($config['insertDataReturn'] !== null) {
            $import->shouldReceive('insertData')
                ->with(
                    $config['insertDataArgs'][0],
                    $config['insertDataArgs'][1],
                    $config['insertDataArgs'][2],
                    $config['insertDataArgs'][3],
                    $config['insertDataArgs'][4] ?? ['id']
                )
                ->andReturn($config['insertDataReturn']);
        }

        if ($config['replaceTranslations'] !== null) {
            $import->shouldReceive('replaceTranslations')->with(Mockery::any(), Mockery::any())->once();
        }

        if ($config['replaceParams'] !== null) {
            $import->shouldReceive('replaceParams')->with(Mockery::any(), Mockery::any())->once();
        }

        if ($config['replaceComments'] !== null) {
            $import->shouldReceive('replaceComments')->with(Mockery::any(), Mockery::any())->once();
        }
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    protected function createImportMock(
        string $importClass,
        array $constructorArgs = [],
        array $options = []
    ): Mockery\MockInterface
    {
        $defaults = [
            'xmlData'                   => [],
            'entity'                    => 'blocks',
            'parseXmlReturn'            => true,
            'extractTranslationsReturn' => [],
            'extractParamsReturn'       => [],
            'extractCommentsReturn'     => [],
            'insertDataReturn'          => [],
        ];

        $config = array_merge($defaults, $options);

        // Ensure we have the required constructor arguments
        if (count($constructorArgs) < 3) {
            throw new InvalidArgumentException(
                'Import mock requires at least 3 constructor arguments: file, database, errorHandler'
            );
        }

        $mock = mock($importClass, $constructorArgs)->makePartial();
        $mock->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('parseXml')->andReturn($config['parseXmlReturn']);
        $mock->shouldReceive('extractTranslations')->andReturn($config['extractTranslationsReturn']);
        $mock->shouldReceive('extractParams')->andReturn($config['extractParamsReturn']);
        $mock->shouldReceive('extractComments')->andReturn($config['extractCommentsReturn']);
        $mock->shouldReceive('insertData')->andReturn($config['insertDataReturn']);

        // Set XML data if provided
        if (! empty($config['xmlData'])) {
            $xmlMock = $this->createXmlMock($config['xmlData'], $config['entity']);
            $reflection = new ReflectionClass($importClass);
            if ($reflection->hasProperty('xml')) {
                $xmlProperty = $reflection->getProperty('xml');
                $xmlProperty->setValue($mock, $xmlMock);
            }
        }

        return $mock;
    }
}
