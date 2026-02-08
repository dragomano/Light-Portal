<?php

declare(strict_types=1);

use LightPortal\Utils\GlobalArray;
use LightPortal\Utils\Request;
use LightPortal\Utils\RequestInterface;
use Tests\ReflectionAccessor;

arch()
    ->expect(Request::class)
    ->toExtend(GlobalArray::class)
    ->toImplement(RequestInterface::class);

describe('Request', function () {
    beforeEach(function () {
        $GLOBALS['_REQUEST'] = [];
        unset($GLOBALS['_SERVER']['REQUEST_URL']);

        $this->request  = new Request();
        $this->accessor = new ReflectionAccessor($this->request);
    });

    afterEach(function () {
        $GLOBALS['_REQUEST'] = [];
        unset($GLOBALS['_SERVER']['REQUEST_URL']);
    });

    describe('__construct()', function () {
        it('initializes storage from $_REQUEST', function () {
            $GLOBALS['_REQUEST'] = ['test' => 'value'];

            expect($this->request->get('test'))->toBe('value');
        });

        it('works with empty $_REQUEST', function () {
            $GLOBALS['_REQUEST'] = [];

            expect($this->request->all())->toBeEmpty();
        });
    });

    describe('is()', function () {
        it('returns true when type matches action', function () {
            $GLOBALS['_REQUEST'] = ['action' => 'test_action'];

            expect($this->request->is('test_action'))->toBeTrue();
        });

        it('returns false when type does not match action', function () {
            $GLOBALS['_REQUEST'] = ['action' => 'other_action'];

            expect($this->request->is('test_action'))->toBeFalse();
        });

        it('returns false when type does not exist', function () {
            $GLOBALS['_REQUEST'] = [];

            expect($this->request->is('test_action'))->toBeFalse();
        });

        it('works with custom type', function () {
            $GLOBALS['_REQUEST'] = ['sa' => 'custom_action'];

            expect($this->request->is('custom_action', 'sa'))->toBeTrue();
        });
    });

    describe('isNot()', function () {
        it('returns true when is() returns false', function () {
            $GLOBALS['_REQUEST'] = ['action' => 'other_action'];

            expect($this->request->isNot('test_action'))->toBeTrue();
        });

        it('returns false when is() returns true', function () {
            $GLOBALS['_REQUEST'] = ['action' => 'test_action'];

            expect($this->request->isNot('test_action'))->toBeFalse();
        });
    });

    describe('sa()', function () {
        it('returns true when sa matches action', function () {
            $GLOBALS['_REQUEST'] = ['sa' => 'my_action'];

            expect($this->request->sa('my_action'))->toBeTrue();
        });

        it('returns false when sa does not match', function () {
            $GLOBALS['_REQUEST'] = ['sa' => 'other_action'];

            expect($this->request->sa('my_action'))->toBeFalse();
        });

        it('returns false when sa is not set', function () {
            $GLOBALS['_REQUEST'] = [];

            expect($this->request->sa('my_action'))->toBeFalse();
        });
    });

    describe('json()', function () {
        it('returns empty array when JSON is invalid', function () {
            $mockInput = function ($filename) {
                if ($filename === 'php://input') {
                    return 'invalid json{';
                }

                return false;
            };

            $result = $this->accessor->callMethod('json', [null, null, $mockInput]);
            expect($result)->toBe([]);
        });

        it('returns default when key not found', function () {
            $mockInput = function ($filename) {
                if ($filename === 'php://input') {
                    return json_encode(['key1' => 'value1']);
                }

                return false;
            };

            $result = $this->accessor->callMethod('json', ['nonexistent', 'default', $mockInput]);
            expect($result)->toBe('default');
        });

        it('returns default when JSON is empty', function () {
            $mockInput = function ($filename) {
                if ($filename === 'php://input') {
                    return '';
                }

                return false;
            };

            $result = $this->accessor->callMethod('json', ['key', 'default', $mockInput]);
            expect($result)->toBe('default');
        });
    });

    describe('url()', function () {
        it('returns REQUEST_URL from server', function () {
            $GLOBALS['_SERVER']['REQUEST_URL'] = '/test/path';

            expect($this->request->url())->toBe('/test/path');
        });

        it('returns empty string when REQUEST_URL not set', function () {
            unset($GLOBALS['_SERVER']['REQUEST_URL']);

            expect($this->request->url())->toBe('');
        });

        it('handles full URL', function () {
            $GLOBALS['_SERVER']['REQUEST_URL'] = 'https://example.com/page?id=1';

            expect($this->request->url())->toBe('https://example.com/page?id=1');
        });
    });
});
