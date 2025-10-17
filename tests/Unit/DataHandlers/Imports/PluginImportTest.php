<?php

declare(strict_types=1);

namespace Tests\Unit\DataHandlers\Imports;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\DataHandlers\Imports\PluginImport;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\File;
use FilesystemIterator;
use Mockery;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Tests\ReflectionAccessor;
use ZipArchive;

dataset('plugin scenarios', [
    ['BootstrapIcons.zip', true],
    ['UserInfo.zip', true],
    ['unknown_package.zip', false],
    ['SomePlugin.zip', false],
]);

beforeAll(function () {
    $addonDir = LP_ADDON_DIR;
    if (is_dir($addonDir)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($addonDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $file->isDir() ? @rmdir($file->getRealPath()) : @unlink($file->getRealPath());
        }
    }

    $destination = sys_get_temp_dir() . '/plugins';
    $basePath = dirname(__DIR__, 4);
    $targets = [
        'BootstrapIcons' => [$basePath . '/src/Sources/LightPortal/Plugins/BootstrapIcons'],
        'UserInfo' => [
            $basePath . '/src/Sources/LightPortal/Plugins/UserInfo/UserInfo.php',
            $basePath . '/src/package-info.xml',
        ],
        'unknown_package' => [$basePath . '/src/package-info.xml'],
        'SomePlugin' => [$basePath . '/README.md'],
    ];

    createArchives($targets, $destination);
});

function createArchives(array $targets, string $destination): void
{
    if (! is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    foreach ($targets as $archiveName => $paths) {
        $zipFile = $destination . '/' . $archiveName . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new RuntimeException("Couldn't create archive: $zipFile");
        }

        foreach ($paths as $path) {
            $realPath = realpath($path);
            if ($realPath === false) {
                continue;
            }

            if (is_dir($realPath)) {
                addDirectoryToZip($zip, $realPath, basename($realPath));
            } elseif (is_file($realPath)) {
                $zip->addFile($realPath, basename($realPath));
            }
        }

        $zip->close();
    }
}

function addDirectoryToZip(ZipArchive $zip, string $directory, string $baseName): void
{
    $directory = rtrim($directory, '/');
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($files as $file) {
        if ($file->isDir()) {
            $dirPath = $baseName . '/' . substr($file->getPathname(), strlen($directory) + 1);
            $zip->addEmptyDir($dirPath);
        } else {
            $filePath = $baseName . '/' . substr($file->getPathname(), strlen($directory) + 1);
            $zip->addFile($file->getPathname(), $filePath);
        }
    }
}

beforeEach(function () {
    $this->sqlMock = Mockery::mock(PortalSqlInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class)->shouldIgnoreMissing();

    unset(Utils::$context['import_successful']);
});

it('returns current entity', function () {
    $import = new PluginImport($this->sqlMock, new File(), $this->errorHandlerMock);

    expect($import->getEntity())->toBe('plugins');
});

it('sets UI', function () {
    unset( Utils::$context['lp_file_type']);

    $import = new ReflectionAccessor(new PluginImport($this->sqlMock, new File(), $this->errorHandlerMock));
    $import->callProtectedMethod('setupUi');

    expect(Utils::$context['lp_file_type'])->toBe('application/zip');
});

it('imports plugins correctly for all scenarios', function ($file, $expected_success) {
    $fixturePath = sys_get_temp_dir() . '/plugins/' . $file;

    $_FILES['import_file'] = [
        'name'     => $file,
        'tmp_name' => $fixturePath,
        'type'     => 'application/zip',
        'error'    => UPLOAD_ERR_OK,
        'size'     => filesize($fixturePath),
    ];

    $import = new ReflectionAccessor(new PluginImport($this->sqlMock, new File(), $this->errorHandlerMock));
    $import->callProtectedMethod('run');

    if ($expected_success) {
        expect(Utils::$context['import_successful'])
            ->toBe('Import of plugins successfully completed');
    } else {
        expect(isset(Utils::$context['import_successful']))->toBeFalse();
    }
})->with('plugin scenarios');
