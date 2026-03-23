<?php

declare(strict_types=1);

use Bugo\Compat\Lang;

beforeEach(function () {
    Lang::$txt = [];

    $this->tmpDir = sys_get_temp_dir() . '/lang_test_' . uniqid();

    mkdir($this->tmpDir);

    Lang::$dirs = [$this->tmpDir];
});

afterEach(function () {
    foreach (glob($this->tmpDir . '/*.php') as $file) {
        unlink($file);
    }

    rmdir($this->tmpDir);

    Lang::$dirs = [];
});

describe('__ function', function () {
    describe('simple key without separators', function () {
        it('returns value from Lang::$txt', function () {
            Lang::$txt['hello'] = 'Hello world';

            expect(__('hello'))->toBe('Hello world');
        });

        it('returns empty string for unknown key', function () {
            expect(__('unknown_key'))->toBe('');
        });

        it('passes args to MessageFormatter', function () {
            Lang::$txt['greeting'] = 'Hello, {name}!';

            expect(__('greeting', ['name' => 'John']))->toBe('Hello, John!');
        });

        it('reads from editortxt when var is specified', function () {
            Lang::$editortxt['bold'] = 'Bold';

            expect(__('bold', [], 'editortxt'))->toBe('Bold');
        });
    });

    describe('key with :: separator', function () {
        it('extracts file and key', function () {
            Lang::$txt['page_title'] = 'My Page';

            // load() will be called but txt is already set
            expect(__('SomeLangFile::page_title'))->toBe('My Page');
        });

        it('does not split rest by dot', function () {
            Lang::$txt['group.nested'] = 'Nested value';

            expect(__('SomeLangFile::group.nested'))->toBe('Nested value');
        });

        it(':: takes priority over .', function () {
            Lang::$txt['key'] = 'Correct';

            expect(__('file.name::key'))->toBe('Correct');
        });
    });

    describe('key with . separator', function () {
        it('extracts file and simple key', function () {
            Lang::$txt['page_title'] = 'My Page';

            expect(__('SomeLangFile.page_title'))->toBe('My Page');
        });

        it('resolves nested key when two dots present', function () {
            Lang::$txt['group']['nested'] = 'Deep value';

            expect(__('SomeLangFile.group.nested'))->toBe('Deep value');
        });

        it('returns empty string for unknown nested key', function () {
            expect(__('SomeLangFile.group.missing'))->toBe('');
        });
    });

    describe('edge cases', function () {
        it('is defined only once', function () {
            expect(function_exists('__'))->toBeTrue();
        });

        it('returns empty string for empty key', function () {
            expect(__(''))->toBe('');
        });
    });
});

describe('resolve_lang_filename', function () {
    it('returns name as-is when lowercase file exists', function () {
        $name = 'index_' . uniqid();
        touch($this->tmpDir . '/' . $name . '.english.php');

        expect(resolve_lang_filename($name))->toBe($name);
    });

    it('returns ucfirst when only capitalized file exists', function () {
        $name = 'admin_' . uniqid();
        touch($this->tmpDir . '/' . ucfirst($name) . '.english.php');

        expect(resolve_lang_filename($name))->toBe(ucfirst($name));
    });

    it('prefers lowercase over ucfirst when both exist', function () {
        $name = 'test_' . uniqid();
        touch($this->tmpDir . '/' . $name . '.english.php');
        touch($this->tmpDir . '/' . ucfirst($name) . '.english.php');

        expect(resolve_lang_filename($name))->toBe($name);
    });

    it('returns name as-is when no file found', function () {
        $name = 'missing_' . uniqid();

        expect(resolve_lang_filename($name))->toBe($name);
    });

    it('works with any language suffix', function () {
        $name = 'calendar_' . uniqid();
        touch($this->tmpDir . '/' . ucfirst($name) . '.russian.php');

        expect(resolve_lang_filename($name))->toBe(ucfirst($name));
    });

    it('searches across multiple dirs', function () {
        $secondDir = sys_get_temp_dir() . '/lang_test2_' . uniqid();
        mkdir($secondDir);
        Lang::$dirs[] = $secondDir;

        $name = 'extra_' . uniqid();
        touch($secondDir . '/' . ucfirst($name) . '.english.php');

        expect(resolve_lang_filename($name))->toBe(ucfirst($name));

        unlink($secondDir . '/' . ucfirst($name) . '.english.php');
        rmdir($secondDir);
    });

    it('uses cached result on repeated calls', function () {
        $name = 'cached_' . uniqid();
        touch($this->tmpDir . '/' . ucfirst($name) . '.english.php');

        $first = resolve_lang_filename($name);

        unlink($this->tmpDir . '/' . ucfirst($name) . '.english.php');

        expect(resolve_lang_filename($name))->toBe($first);
    });
});
