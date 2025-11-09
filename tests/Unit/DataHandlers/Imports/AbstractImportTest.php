<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\DataHandlers\Imports\AbstractImport;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\File;
use LightPortal\Utils\FileInterface;
use LightPortal\Utils\Traits\HasRequest;

arch()
    ->expect(AbstractImport::class)
    ->toBeAbstract()
    ->toUseTrait(HasRequest::class);

it('sets max_file_size in constructor', function () {
    $originalValue = Utils::$context['max_file_size'] ?? null;

    try {
        $sqlMock = mock(PortalSqlInterface::class);
        $fileMock = mock(FileInterface::class);
        $errorHandlerMock = mock(ErrorHandlerInterface::class);

        $expectedBytes = 10 * 1024 * 1024; // 10MB in bytes

        $mock = mock(AbstractImport::class, [$sqlMock, $fileMock, $errorHandlerMock])->makePartial();
        $mock->shouldAllowMockingProtectedMethods();

        Utils::$context['max_file_size'] = $expectedBytes;

        expect(Utils::$context['max_file_size'])->toBe($expectedBytes);
    } finally {
        if ($originalValue !== null) {
            Utils::$context['max_file_size'] = $originalValue;
        } else {
            unset(Utils::$context['max_file_size']);
        }
    }
});

it('returns false when file not found', function () {
    $sqlMock = mock(PortalSqlInterface::class);
    $fileMock = mock(FileInterface::class);
    $errorHandlerMock = mock(ErrorHandlerInterface::class);

    $mock = mock(AbstractImport::class, [$sqlMock, $fileMock, $errorHandlerMock])->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    $fileMock = mock(File::class);
    $fileMock->shouldReceive('get')->with('import_file')->andReturnNull();
    $mock->shouldReceive('files')->andReturn($fileMock);

    $result = $mock->getFile();

    expect($result)->toBeFalse();
});

it('returns false for invalid file type', function () {
    $sqlMock = mock(PortalSqlInterface::class);
    $fileMock = mock(FileInterface::class);
    $errorHandlerMock = mock(ErrorHandlerInterface::class);

    $mock = mock(AbstractImport::class, [$sqlMock, $fileMock, $errorHandlerMock])->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    // Mock file with invalid type
    $file = [
        'type' => 'text/plain',
        'tmp_name' => '/tmp/test.xml'
    ];

    $fileMock = mock(File::class);
    $fileMock->shouldReceive('get')->with('import_file')->andReturn($file);
    $mock->shouldReceive('files')->andReturn($fileMock);

    $result = $mock->getFile();

    expect($result)->toBeFalse();
});

it('returns SimpleXMLElement for valid xml file', function () {
    $sqlMock = mock(PortalSqlInterface::class);
    $fileMock = mock(FileInterface::class);
    $errorHandlerMock = mock(ErrorHandlerInterface::class);

    $mock = mock(AbstractImport::class, [$sqlMock, $fileMock, $errorHandlerMock])->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

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

    $file = [
        'type'     => 'text/xml',
        'tmp_name' => $tempFile,
    ];

    $fileMock = mock(File::class);
    $fileMock->shouldReceive('get')->with('import_file')->andReturn($file);
    $mock->shouldReceive('files')->andReturn($fileMock);

    $result = $mock->getFile();

    expect($result)->toBeInstanceOf(SimpleXMLElement::class);

    unlink($tempFile);
});
