<?php

declare(strict_types=1);

use LightPortal\Utils\Filesystem;
use LightPortal\Utils\FilesystemInterface;

function suppressWarnings(callable $callback): mixed
{
    set_error_handler(static fn() => true);

    try {
        return $callback();
    } finally {
        restore_error_handler();
    }
}

describe('Filesystem', function () {
    beforeEach(function () {
        $this->fs = new Filesystem();
        $this->testDir = sys_get_temp_dir() . '/lp_test_fs_' . uniqid();
        $this->fs->mkdir($this->testDir);
        $this->testFile = $this->testDir . '/test.txt';
    });

    afterEach(function () {
        if (is_dir($this->testDir)) {
            $this->fs->delete($this->testDir);
        }
    });

    it('implements FilesystemInterface', function () {
        expect($this->fs)->toBeInstanceOf(FilesystemInterface::class);
    });

    describe('exists()', function () {
        it('returns false for non-existent path', function () {
            expect($this->fs->exists('/non/existent/path'))->toBeFalse();
        });

        it('returns true for existing file', function () {
            $this->fs->write($this->testFile, 'test');
            expect($this->fs->exists($this->testFile))->toBeTrue();
        });
    });

    describe('read()', function () {
        it('throws exception for non-existent file', function () {
            expect(fn() => $this->fs->read('/non/existent/file.txt'))
                ->toThrow(RuntimeException::class, 'File does not exist');
        });

        it('throws exception when file cannot be read', function () {
            expect(fn() => suppressWarnings(fn() => $this->fs->read($this->testDir)))
                ->toThrow(RuntimeException::class, 'Cannot read file');
        });

        it('reads file content correctly', function () {
            $content = 'Hello, World!';
            $this->fs->write($this->testFile, $content);
            expect($this->fs->read($this->testFile))->toBe($content);
        });
    });

    describe('write() throws exception', function () {
        it('throws exception when cannot write to file', function () {
            $invalidPath = '/invalid/path/file.txt';
            expect(fn() => suppressWarnings(fn() => $this->fs->write($invalidPath, 'content')))
                ->toThrow(RuntimeException::class, 'Cannot write to file');
        });

        it('throws exception when writing to a directory path', function () {
            expect(fn() => suppressWarnings(fn() => $this->fs->write($this->testDir, 'content')))
                ->toThrow(RuntimeException::class, 'Cannot write to file');
        });

        it('throws exception when directory does not exist', function () {
            $invalidPath = '/nonexistent/dir/file.txt';
            expect(fn() => suppressWarnings(fn() => $this->fs->write($invalidPath, 'content')))
                ->toThrow(RuntimeException::class, 'directory does not exist');
        });
    });

    describe('write()', function () {
        it('creates file with content', function () {
            $content = 'Test content';
            $result = $this->fs->write($this->testFile, $content);
            expect($result)->toBeTrue()
                ->and($this->fs->read($this->testFile))->toBe($content);
        });
    });

    describe('isDir()', function () {
        it('returns false for file', function () {
            $this->fs->write($this->testFile, 'test');
            expect($this->fs->isDir($this->testFile))->toBeFalse();
        });

        it('returns true for directory', function () {
            $this->fs->mkdir($this->testDir);
            expect($this->fs->isDir($this->testDir))->toBeTrue();
        });
    });

    describe('isFile()', function () {
        it('returns true for file', function () {
            $this->fs->write($this->testFile, 'test');
            expect($this->fs->isFile($this->testFile))->toBeTrue();
        });

        it('returns false for directory', function () {
            $this->fs->mkdir($this->testDir);
            expect($this->fs->isFile($this->testDir))->toBeFalse();
        });
    });

    describe('mkdir()', function () {
        it('creates directory successfully', function () {
            $result = $this->fs->mkdir($this->testDir);
            expect($result)->toBeTrue()
                ->and($this->fs->isDir($this->testDir))->toBeTrue();
        });

        it('returns true if directory already exists', function () {
            $this->fs->mkdir($this->testDir);
            $result = $this->fs->mkdir($this->testDir);
            expect($result)->toBeTrue();
        });

        it('throws exception when path exists but is not a directory', function () {
            $this->fs->write($this->testFile, 'test');
            expect(fn() => $this->fs->mkdir($this->testFile))
                ->toThrow(RuntimeException::class, 'exists but is not a directory');
        });
    });

    describe('delete()', function () {
        it('deletes file successfully', function () {
            $this->fs->write($this->testFile, 'test');
            expect($this->fs->delete($this->testFile))->toBeTrue()
                ->and($this->fs->exists($this->testFile))->toBeFalse();
        });

        it('deletes directory successfully', function () {
            $this->fs->mkdir($this->testDir);
            expect($this->fs->delete($this->testDir))->toBeTrue()
                ->and($this->fs->exists($this->testDir))->toBeFalse();
        });

        it('throws exception when delete fails', function () {
            $missing = $this->testDir . '/missing.txt';
            expect(fn() => suppressWarnings(fn() => $this->fs->delete($missing)))
                ->toThrow(RuntimeException::class, 'Cannot delete path');
        });

        it('deletes nested directories recursively', function () {
            $nestedDir = $this->testDir . '/nested';
            $this->fs->mkdir($nestedDir);
            $this->fs->write($nestedDir . '/file.txt', 'content');

            expect($this->fs->delete($this->testDir))->toBeTrue()
                ->and($this->fs->exists($this->testDir))->toBeFalse();
        });
    });

    describe('listDir()', function () {
        it('throws exception for non-directory', function () {
            $this->fs->write($this->testFile, 'test');
            expect(fn() => $this->fs->listDir($this->testFile))
                ->toThrow(RuntimeException::class, 'not a directory');
        });

        it('lists directory contents', function () {
            $this->fs->mkdir($this->testDir);
            $this->fs->write($this->testDir . '/file1.txt', 'content1');
            $this->fs->write($this->testDir . '/file2.txt', 'content2');

            $contents = $this->fs->listDir($this->testDir);
            expect($contents)->toContain('file1.txt')
                ->and($contents)->toContain('file2.txt');
        });
    });

    describe('getSize()', function () {
        it('throws exception for non-existent file', function () {
            expect(fn() => $this->fs->getSize('/non/existent/file'))
                ->toThrow(RuntimeException::class, 'File does not exist');
        });

        it('throws exception when path is not a file', function () {
            expect(fn() => $this->fs->getSize($this->testDir))
                ->toThrow(RuntimeException::class, 'Path is not a file');
        });

        it('returns file size correctly', function () {
            $content = 'Test content with size';
            $this->fs->write($this->testFile, $content);
            expect($this->fs->getSize($this->testFile))->toBe(mb_strlen($content));
        });
    });

    describe('copy()', function () {
        it('copies file successfully', function () {
            $this->fs->write($this->testFile, 'content');
            $dest = $this->testDir . '/copy.txt';
            expect($this->fs->copy($this->testFile, $dest))->toBeTrue()
                ->and($this->fs->read($dest))->toBe('content');
        });

        it('copies directory recursively', function () {
            $sourceDir = $this->testDir . '/source';
            $nestedDir = $sourceDir . '/nested';
            $this->fs->mkdir($nestedDir);
            $this->fs->write($sourceDir . '/file.txt', 'root');
            $this->fs->write($nestedDir . '/inner.txt', 'inner');

            $destDir = $this->testDir . '/dest';

            expect($this->fs->copy($sourceDir, $destDir))->toBeTrue()
                ->and($this->fs->read($destDir . '/file.txt'))->toBe('root')
                ->and($this->fs->read($destDir . '/nested/inner.txt'))->toBe('inner');
        });

        it('throws exception for non-existent source', function () {
            expect(fn() => $this->fs->copy('/non/existent', '/dest'))
                ->toThrow(RuntimeException::class, 'Source does not exist');
        });

        it('throws exception when copy fails', function () {
            $this->fs->write($this->testFile, 'content');
            $dest = $this->testDir . '/missing/copy.txt';

            expect(fn() => suppressWarnings(fn() => $this->fs->copy($this->testFile, $dest)))
                ->toThrow(RuntimeException::class, 'Cannot copy from');
        });
    });

    describe('move()', function () {
        it('moves file successfully', function () {
            $this->fs->write($this->testFile, 'content');
            $dest = $this->testDir . '/moved.txt';
            expect($this->fs->move($this->testFile, $dest))->toBeTrue()
                ->and($this->fs->exists($dest))->toBeTrue()
                ->and($this->fs->exists($this->testFile))->toBeFalse();
        });

        it('throws exception when source does not exist', function () {
            $dest = $this->testDir . '/missing.txt';
            expect(fn() => $this->fs->move($this->testFile, $dest))
                ->toThrow(RuntimeException::class, 'Source does not exist');
        });

        it('throws exception when move fails', function () {
            $this->fs->write($this->testFile, 'content');
            $dest = $this->testDir . '/missing/moved.txt';

            expect(fn() => suppressWarnings(fn() => $this->fs->move($this->testFile, $dest)))
                ->toThrow(RuntimeException::class, 'Cannot move from');
        });
    });

    describe('getPermissions()', function () {
        it('throws exception for non-existent path', function () {
            expect(fn() => $this->fs->getPermissions('/non/existent'))
                ->toThrow(RuntimeException::class, 'does not exist');
        });

        it('returns file permissions', function () {
            $this->fs->write($this->testFile, 'test');
            $perms = $this->fs->getPermissions($this->testFile);
            expect($perms)->toBeInt()
                ->and($perms)->toBeGreaterThan(0);
        });
    });

    describe('file operations', function () {
        it('opens and reads file correctly', function () {
            $content = "Line 1\nLine 2\nLine 3";
            $this->fs->write($this->testFile, $content);

            $handle = $this->fs->openFile($this->testFile, 'r');
            $data = $this->fs->readFile($handle, 1024);
            $this->fs->closeFile($handle);

            expect($data)->toContain('Line 1')
                ->and($data)->toContain('Line 2')
                ->and($data)->toContain('Line 3');
        });

        it('throws exception when openFile fails', function () {
            expect(fn() => suppressWarnings(fn() => $this->fs->openFile($this->testDir, 'r')))
                ->toThrow(RuntimeException::class, 'Cannot open file');
        });

        it('detects end of file correctly', function () {
            $this->fs->write($this->testFile, 'content');
            $handle = $this->fs->openFile($this->testFile, 'r');

            $this->fs->readFile($handle, 1024);
            $isEof = $this->fs->isEndOfFile($handle);
            $this->fs->closeFile($handle);

            expect($isEof)->toBeTrue();
        });
    });
});
