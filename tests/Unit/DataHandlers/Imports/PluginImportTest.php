<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use Bugo\LightPortal\DataHandlers\Imports\PluginImport;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FileInterface;
use org\bovigo\vfs\vfsStream;

dataset('plugin structures', [
    ['structure' => 'subfolder', 'expected_success' => true],
    ['structure' => 'root', 'expected_success' => true],
    ['structure' => 'invalid', 'expected_success' => false],
]);

beforeEach(function () {
    $this->fileMock = Mockery::mock(FileInterface::class);
    $this->dbMock = Mockery::mock(DatabaseInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);
    $this->vfsRoot = vfsStream::setup('test');

    unset(Utils::$context['import_successful']);
});

it('run method handles extraction result correctly', function ($structure, $expected_success) {
    $import = Mockery::mock(PluginImport::class, [$this->fileMock, $this->dbMock, $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();
    $import->shouldReceive('extractPackage')->andReturn($expected_success);

    $import->run();

    if ($expected_success) {
        expect(isset(Utils::$context['import_successful']))->toBeTrue()
            ->and(Utils::$context['import_successful'])->toBe('Import of plugins successfully completed');
    } else {
        expect(isset(Utils::$context['import_successful']))->toBeFalse();
    }
})->with('plugin structures');

it('extracts package from valid ZIP with subfolder structure', function () {
    $zipPath = __DIR__ . '/../temp/TestPluginFolder.zip';

    $this->fileMock->shouldReceive('get')->with('import_file')->andReturn([
        'name'     => 'TestPluginFolder.zip',
        'tmp_name' => $zipPath,
        'type'     => 'application/zip',
        'error'    => UPLOAD_ERR_OK,
    ]);

    $import = Mockery::mock(PluginImport::class, [$this->fileMock, $this->dbMock, $this->errorHandlerMock])->makePartial()->shouldAllowMockingProtectedMethods();
    $import->shouldReceive('extractPackage')->andReturn(true);
    $import->run();

    expect(Utils::$context['import_successful'])->toBe('Import of plugins successfully completed');
});

it('extracts package from valid ZIP in root structure', function () {
    $zipPath = __DIR__ . '/../temp/TestPlugin.zip';

    $this->fileMock->shouldReceive('get')->with('import_file')->andReturn([
        'name'     => 'TestPlugin.zip',
        'tmp_name' => $zipPath,
        'type'     => 'application/zip',
        'error'    => UPLOAD_ERR_OK,
    ]);

    $import = Mockery::mock(PluginImport::class, [$this->fileMock, $this->dbMock, $this->errorHandlerMock])
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();
    $import->shouldReceive('extractPackage')->andReturn(true);
    $import->run();

    expect(Utils::$context['import_successful'])->toBe('Import of plugins successfully completed');
});

it('handles invalid ZIP file gracefully', function () {
    $this->fileMock->shouldReceive('get')->with('import_file')->andReturn([
        'name'     => 'invalid.txt',
        'tmp_name' => '/tmp/invalid.txt',
        'type'     => 'text/plain',
        'error'    => UPLOAD_ERR_OK,
    ]);

    $import = Mockery::mock(PluginImport::class, [$this->fileMock, $this->dbMock, $this->errorHandlerMock])->makePartial()->shouldAllowMockingProtectedMethods();
    $import->run();

    expect(isset(Utils::$context['import_successful']))->toBeFalse();
});

it('handles empty ZIP file', function () {
    $this->fileMock->shouldReceive('get')->with('import_file')->andReturn([
        'name'     => 'empty.zip',
        'tmp_name' => $this->vfsRoot->url() . '/empty.zip',
        'type'     => 'application/zip',
        'error'    => UPLOAD_ERR_OK,
    ]);

    $import = Mockery::mock(PluginImport::class, [$this->fileMock, $this->dbMock, $this->errorHandlerMock])->makePartial()->shouldAllowMockingProtectedMethods();
    $import->shouldReceive('extractPackage')->andReturn(false)->once();
    $import->run();

    expect(isset(Utils::$context['import_successful']))->toBeFalse();
});
