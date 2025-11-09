<?php

declare(strict_types=1);

use LightPortal\DataHandlers\DataHandler;
use LightPortal\DataHandlers\Exports\AbstractExport;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FilesystemInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Database\PortalSqlInterface;

arch()->expect(AbstractExport::class)
    ->toBeAbstract()
    ->toExtend(DataHandler::class)
    ->toHaveMethods([
        'main',
        'run',
        'downloadFile',
        'getGeneratorFrom',
        'isEntityEmpty',
        'hasEntityInRequest',
    ]);

it('should call getFile and downloadFile when file exists', function () {
    $entity = 'test';
    $database = mock(PortalSqlInterface::class);
    $filesystem = mock(FilesystemInterface::class);
    $errorHandler = mock(ErrorHandlerInterface::class);

    $mock = mock(AbstractExport::class, [$entity, $database, $filesystem, $errorHandler])->makePartial();
    $mock->shouldAllowMockingProtectedMethods();
    $mock->shouldReceive('getFile')->andReturn('/tmp/test.xml');
    $mock->shouldReceive('downloadFile')->with('/tmp/test.xml')->once();

    $mock->run();
});

it('should do nothing when getFile returns empty', function () {
    $entity = 'test';
    $database = mock(PortalSqlInterface::class);
    $filesystem = mock(FilesystemInterface::class);
    $errorHandler = mock(ErrorHandlerInterface::class);

    $mock = mock(AbstractExport::class, [$entity, $database, $filesystem, $errorHandler])->makePartial();
    $mock->shouldAllowMockingProtectedMethods();
    $mock->shouldReceive('getFile')->andReturn('');
    $mock->shouldReceive('downloadFile')->never();

    $mock->run();
});

it('should handle existing file correctly', function () {
    $entity = 'test';
    $database = mock(PortalSqlInterface::class);
    $filesystem = mock(FilesystemInterface::class);
    $errorHandler = mock(ErrorHandlerInterface::class);

    $mock = mock(AbstractExport::class, [$entity, $database, $filesystem, $errorHandler])->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    // Create a temporary file
    $tempDir = sys_get_temp_dir();
    $filePath = $tempDir . DIRECTORY_SEPARATOR . 'test.xml';
    $fileContent = 'test content';
    file_put_contents($filePath, $fileContent);

    $mock->shouldReceive('fileExists')->with($filePath)->andReturn(true)->once();
    $mock->shouldReceive('obEndClean')->once();
    $mock->shouldReceive('headerRemove')->with('content-encoding')->once();
    $mock->shouldReceive('sendHeader')->with('Content-Description: File Transfer')->once();
    $mock->shouldReceive('sendHeader')->with('Content-Type: application/octet-stream')->once();
    $mock->shouldReceive('basename')->with($filePath)->andReturn(basename($filePath))->once();
    $mock->shouldReceive('sendHeader')->with('Content-Disposition: attachment; filename=test.xml')->once();
    $mock->shouldReceive('sendHeader')->with('Content-Transfer-Encoding: binary')->once();
    $mock->shouldReceive('sendHeader')->with('Expires: 0')->once();
    $mock->shouldReceive('sendHeader')->with('Cache-Control: must-revalidate')->once();
    $mock->shouldReceive('sendHeader')->with('Pragma: public')->once();
    $mock->shouldReceive('fileSize')->with($filePath)->andReturn(strlen($fileContent))->once();
    $mock->shouldReceive('sendHeader')->with('Content-Length: 12')->once();

    // For the file operations
    $fileHandle = fopen($filePath, 'rb');
    $mock->shouldReceive('fopen')->with($filePath, 'rb')->andReturn($fileHandle)->once();
    $mock->shouldReceive('feof')->with($fileHandle)->andReturn(false)->once();
    $mock->shouldReceive('feof')->with($fileHandle)->andReturn(true)->once();
    $mock->shouldReceive('fread')->with($fileHandle, 1024)->andReturn($fileContent)->once();
    $mock->shouldReceive('fclose')->with($fileHandle)->once();
    $mock->shouldReceive('unlink')->with($filePath)->once();
    $mock->shouldReceive('doExit')->once();

    $mock->downloadFile($filePath);

    // Clean up the temporary file
    if (file_exists($filePath)) {
        unlink($filePath);
    }
});

it('should do nothing when file does not exist', function () {
    $entity = 'test';
    $database = mock(PortalSqlInterface::class);
    $filesystem = mock(FilesystemInterface::class);
    $errorHandler = mock(ErrorHandlerInterface::class);

    $mock = mock(AbstractExport::class, [$entity, $database, $filesystem, $errorHandler])->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    $mock->shouldReceive('fileExists')->with('/nonexistent/file.xml')->andReturn(false);

    // Ensure no file operations or headers are set when file doesn't exist
    $mock->shouldNotReceive('ob_end_clean');
    $mock->shouldNotReceive('header');
    $mock->shouldNotReceive('header_remove');
    $mock->shouldNotReceive('fopen');
    $mock->shouldNotReceive('unlink');
    $mock->shouldNotReceive('exit');

    $mock->downloadFile('/nonexistent/file.xml');

    // Test completes without exceptions
});

it('should return closure that yields items', function () {
    $entity = 'test';
    $database = mock(PortalSqlInterface::class);
    $filesystem = mock(FilesystemInterface::class);
    $errorHandler = mock(ErrorHandlerInterface::class);

    $mock = mock(AbstractExport::class, [$entity, $database, $filesystem, $errorHandler])->makePartial();
    $mock->shouldAllowMockingProtectedMethods();

    $items = ['a', 'b', 'c'];
    $generator = $mock->getGeneratorFrom($items);

    expect($generator)->toBeInstanceOf(Closure::class);

    $result = [];
    foreach ($generator() as $item) {
        $result[] = $item;
    }

    expect($result)->toBe($items);
});

it('should return true when no entity and no export_all', function () {
    $database = mock(PortalSqlInterface::class);
    $filesystem = mock(FilesystemInterface::class);
    $errorHandler = mock(ErrorHandlerInterface::class);

    $mock = mock(AbstractExport::class, ['test_entity', $database, $filesystem, $errorHandler])->makePartial();

    $request = mock(RequestInterface::class);
    $request->shouldReceive('isEmpty')->with('test_entity')->andReturn(true);
    $request->shouldReceive('hasNot')->with('export_all')->andReturn(true);

    $mock->shouldReceive('request')->andReturn($request);
    $mock->shouldAllowMockingProtectedMethods();

    expect($mock->isEntityEmpty())->toBeTrue();
});

it('should return false when has entity', function () {
    $database = mock(PortalSqlInterface::class);
    $filesystem = mock(FilesystemInterface::class);
    $errorHandler = mock(ErrorHandlerInterface::class);

    $mock = mock(AbstractExport::class, ['test_entity', $database, $filesystem, $errorHandler])->makePartial();

    $request = mock(RequestInterface::class);
    $request->shouldReceive('isEmpty')->with('test_entity')->andReturn(false);

    $mock->shouldReceive('request')->andReturn($request);
    $mock->shouldAllowMockingProtectedMethods();

    expect($mock->isEntityEmpty())->toBeFalse();
});

it('should return true when has entity and no export_all', function () {
    $database = mock(PortalSqlInterface::class);
    $filesystem = mock(FilesystemInterface::class);
    $errorHandler = mock(ErrorHandlerInterface::class);

    $mock = mock(AbstractExport::class, ['test_entity', $database, $filesystem, $errorHandler])->makePartial();

    $request = mock(RequestInterface::class);
    $request->shouldReceive('get')->with('test_entity')->andReturn([1, 2]);
    $request->shouldReceive('hasNot')->with('export_all')->andReturn(true);

    $mock->shouldReceive('request')->andReturn($request);
    $mock->shouldAllowMockingProtectedMethods();

    expect($mock->hasEntityInRequest())->toBeTrue();
});

it('should return false when no entity', function () {
    $database = mock(PortalSqlInterface::class);
    $filesystem = mock(FilesystemInterface::class);
    $errorHandler = mock(ErrorHandlerInterface::class);

    $mock = mock(AbstractExport::class, ['test_entity', $database, $filesystem, $errorHandler])->makePartial();

    $request = mock(RequestInterface::class);
    $request->shouldReceive('get')->with('test_entity')->andReturn(null);

    $mock->shouldReceive('request')->andReturn($request);
    $mock->shouldAllowMockingProtectedMethods();

    expect($mock->hasEntityInRequest())->toBeFalse();
});
