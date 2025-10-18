<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use LightPortal\DataHandlers\Exports\PluginExport;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FilesystemInterface;
use LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;
use Tests\DataHandlerTestTrait;

uses(DataHandlerTestTrait::class);

beforeEach(function () {
    $this->requestMock = Mockery::mock(RequestInterface::class);
    $this->sqlMock = $this->createDatabaseMock();
    $this->fileMock = Mockery::mock(FilesystemInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

    AppMockRegistry::set(RequestInterface::class, $this->requestMock);
});

afterEach(function () {
    AppMockRegistry::clear();
});

describe('getData', function () {
    it('returns empty array when entity is empty', function () {
        $export = Mockery::mock(PluginExport::class, [$this->sqlMock, $this->fileMock, $this->errorHandlerMock])->makePartial();

        $this->requestMock->shouldReceive('isEmpty')->with('plugins')->andReturn(true);
        $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(true);

        $export->shouldReceive('request')->andReturn($this->requestMock);
        $export->shouldAllowMockingProtectedMethods();

        $result = $export->getData();

        expect($result)->toBe([]);
    });

    it('returns selected plugins when provided', function () {
        $export = Mockery::mock(PluginExport::class, [$this->sqlMock, $this->fileMock, $this->errorHandlerMock])->makePartial();

        $this->requestMock->shouldReceive('isEmpty')->with('plugins')->andReturn(false);
        $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(true);
        $this->requestMock->shouldReceive('has')->with('export_all')->andReturn(false);
        $this->requestMock->shouldReceive('get')->with('plugins')->andReturn(['HelloPortal']);

        $export->shouldReceive('request')->andReturn($this->requestMock);
        $export->shouldAllowMockingProtectedMethods();

        $result = $export->getData();

        expect($result)->toBe(['HelloPortal']);
    });

    it('returns all plugins when export_all', function () {
        $export = Mockery::mock(PluginExport::class, [$this->sqlMock, $this->fileMock, $this->errorHandlerMock])->makePartial();

        Utils::$context['lp_plugins'] = ['HelloPortal', 'TopTopics'];

        $this->requestMock->shouldReceive('isEmpty')->with('plugins')->andReturn(true);
        $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(false);
        $this->requestMock->shouldReceive('has')->with('export_all')->andReturn(true);

        $export->shouldReceive('request')->andReturn($this->requestMock);
        $export->shouldAllowMockingProtectedMethods();

        $result = $export->getData();

        expect($result)->toBe(['HelloPortal', 'TopTopics']);
    });
});

describe('getFile', function () {
    it('returns empty string when no dirs', function () {
        $export = Mockery::mock(PluginExport::class, [$this->sqlMock, $this->fileMock, $this->errorHandlerMock])->makePartial();
        $export->shouldAllowMockingProtectedMethods();
        $export->shouldReceive('request')->andReturn($this->requestMock);
        $export->shouldReceive('run')->andReturn();
        $export->shouldReceive('request')->andReturn($this->requestMock)->byDefault();
        $export->shouldReceive('getData')->andReturn([]);

        $result = $export->getFile();

        expect($result)->toBe('');
    });
});

describe('createPackage ', function () {
    it('creates ZIP archive for plugins', function ($dirs, $expectedFile) {
        require_once dirname(__DIR__, 4) . '/src/init.php';

        PortalSetup::copyDirectory(
            dirname(__DIR__, 4) . '/src/Sources/LightPortal/Plugins/HelloPortal',
            sys_get_temp_dir() . '/addons/HelloPortal'
        );

        PortalSetup::copyDirectory(
            dirname(__DIR__, 4) . '/src/Sources/LightPortal/Plugins/TopTopics',
            sys_get_temp_dir() . '/addons/TopTopics'
        );

        $export = Mockery::mock(PluginExport::class, [$this->sqlMock, $this->fileMock, $this->errorHandlerMock])->makePartial();
        $export->shouldAllowMockingProtectedMethods();

        $result = $export->createPackage($dirs);

        expect($result)->toBeString()
            ->and(file_exists($result))->toBeTrue()
            ->and(str_ends_with($result, $expectedFile))->toBeTrue();

        // Cleanup
    })->with([
        [['HelloPortal'], 'HelloPortal.zip'], // Single plugin export scenario
        [['HelloPortal', 'TopTopics'], 'lp_plugins.zip'], // Multiple plugins export scenario
    ]);
});

it('handles ZipArchive creation failure', function () {
    $export = Mockery::mock(PluginExport::class, [$this->sqlMock, $this->fileMock, $this->errorHandlerMock])->makePartial();
    $export->shouldAllowMockingProtectedMethods();

    // For failure test, use temp dir but make TestPlugin a file instead of directory
    $tempDir = sys_get_temp_dir() . '/lp_test_plugins';
    if (! is_dir($tempDir)) {
        mkdir($tempDir);
    }

    $testPluginPath = $tempDir . '/HelloPortal';

    if (! file_exists($testPluginPath)) {
        touch($testPluginPath); // Create as file, not directory
    }

    Utils::$context['lp_addon_dir'] = $tempDir;

    $dirs = ['HelloPortal'];
    $result = $export->createPackage($dirs);

    expect($result)->toBe('');

    // Cleanup
    unlink($testPluginPath);
    rmdir($tempDir);
});
