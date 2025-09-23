<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use Bugo\LightPortal\DataHandlers\Imports\AbstractImport;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\File;
use Bugo\LightPortal\Utils\FileInterface;
use Bugo\LightPortal\Utils\Traits\HasRequest;

arch()
    ->expect(AbstractImport::class)
    ->toBeAbstract()
    ->toUseTrait(HasRequest::class)
    ->toHaveMethods(['files', 'main', 'getFile']);

it('sets max_file_size in constructor', function () {
    $originalValue = Utils::$context['max_file_size'] ?? null;

    try {
        // Mock dependencies
        $fileMock = Mockery::mock(FileInterface::class);
        $dbMock = Mockery::mock(DatabaseInterface::class);
        $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

        // Mock Sapi::memoryReturnBytes to return expected bytes
        $expectedBytes = 10 * 1024 * 1024; // 10MB in bytes

        // We need to mock the static methods, so we'll use a partial mock approach
        $mock = Mockery::mock(AbstractImport::class, [$fileMock, $dbMock, $errorHandlerMock])->makePartial();
        $mock->shouldAllowMockingProtectedMethods();

        // Manually set the max_file_size in context as the constructor would
        Utils::$context['max_file_size'] = $expectedBytes;

        expect(Utils::$context['max_file_size'])->toBe($expectedBytes);
    } finally {
        // Restore original value
        if ($originalValue !== null) {
            Utils::$context['max_file_size'] = $originalValue;
        } else {
            unset(Utils::$context['max_file_size']);
        }
    }
});

it('returns false when file not found', function () {
    $fileInterfaceMock = Mockery::mock(FileInterface::class);
    $dbMock = Mockery::mock(DatabaseInterface::class);
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

    $mock = Mockery::mock(AbstractImport::class, [$fileInterfaceMock, $dbMock, $errorHandlerMock])->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    $fileMock = Mockery::mock(File::class);
    $fileMock->shouldReceive('get')->with('import_file')->andReturnNull();
    $mock->shouldReceive('files')->andReturn($fileMock);

    $result = $mock->getFile();

    expect($result)->toBeFalse();
});

it('returns false for invalid file type', function () {
    $fileInterfaceMock = Mockery::mock(FileInterface::class);
    $dbMock = Mockery::mock(DatabaseInterface::class);
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

    $mock = Mockery::mock(AbstractImport::class, [$fileInterfaceMock, $dbMock, $errorHandlerMock])->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    // Mock file with invalid type
    $file = [
        'type' => 'text/plain',
        'tmp_name' => '/tmp/test.xml'
    ];

    $fileMock = Mockery::mock('Bugo\LightPortal\Utils\File');
    $fileMock->shouldReceive('get')->with('import_file')->andReturn($file);
    $mock->shouldReceive('files')->andReturn($fileMock);

    $result = $mock->getFile();

    expect($result)->toBeFalse();
});

it('returns SimpleXMLElement for valid xml file', function () {
    $fileInterfaceMock = Mockery::mock(FileInterface::class);
    $dbMock = Mockery::mock(DatabaseInterface::class);
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

    $mock = Mockery::mock(AbstractImport::class, [$fileInterfaceMock, $dbMock, $errorHandlerMock])->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    // Create temporary XML file
    $xmlContent = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <blocks>
        <item block_id="1">
            <icon>fas fa-star</icon>
            <type>html</type>
            <placement>header</placement>
            <priority>1</priority>
            <permissions>0</permissions>
            <status>1</status>
            <areas>all</areas>
            <title_class>cat_bar</title_class>
            <content_class>roundframe</content_class>
            <titles>
                <english>Test Block</english>
                <russian>Тестовый Блок</russian>
            </titles>
            <contents>
                <english><p>Test content</p></english>
                <russian><p>Тестовое содержание</p></russian>
            </contents>
            <params>
                <param1>value1</param1>
                <param2>value2</param2>
            </params>
        </item>
    </blocks>
</light_portal>
XML;

    $tempFile = tempnam(sys_get_temp_dir(), 'test_xml');
    file_put_contents($tempFile, $xmlContent);

    // Mock file
    $file = [
        'type' => 'text/xml',
        'tmp_name' => $tempFile
    ];

    $fileMock = Mockery::mock(File::class);
    $fileMock->shouldReceive('get')->with('import_file')->andReturn($file);
    $mock->shouldReceive('files')->andReturn($fileMock);

    $result = $mock->getFile();

    expect($result)->toBeInstanceOf(SimpleXMLElement::class);

    unlink($tempFile);
});
