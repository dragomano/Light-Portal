<?php

declare(strict_types=1);

use LightPortal\Utils\ErrorHandler;
use LightPortal\Utils\ErrorHandlerInterface;

describe('ErrorHandler', function () {
    beforeEach(function () {
        $this->handler = new ErrorHandler();
        $this->handler->clear();
    });

    it('implements ErrorHandlerInterface', function () {
        expect($this->handler)->toBeInstanceOf(ErrorHandlerInterface::class);
    });

    describe('log()', function () {
        it('stores log in memory', function () {
            $this->handler->log('Test message', 'info', ['key' => 'value']);

            $logs = $this->handler->getLogs();
            expect(count($logs))->toBe(1)
                ->and($logs[0]['message'])->toBe('Test message')
                ->and($logs[0]['level'])->toBe('info')
                ->and($logs[0]['context'])->toBe(['key' => 'value']);
        });

        it('stores multiple logs', function () {
            $this->handler->log('Message 1', 'info', []);
            $this->handler->log('Message 2', 'error', []);

            $logs = $this->handler->getLogs();
            expect(count($logs))->toBe(2);
        });
    });

    describe('handle()', function () {
        it('handles exception and logs it', function () {
            $exception = new RuntimeException('Test exception', 500);

            $this->handler->handle($exception);

            $logs = $this->handler->getLogs();
            expect(count($logs))->toBe(1)
                ->and($logs[0]['message'])->toBe('Test exception')
                ->and($logs[0]['level'])->toBe('error')
                ->and($logs[0]['context']['file'])->toContain('ErrorHandlerTest.php')
                ->and($logs[0]['context']['line'])->toBeInt()
                ->and($logs[0]['context']['trace'])->toBeString();
        });
    });

    describe('setLevel() and getLevel()', function () {
        it('returns default level', function () {
            expect($this->handler->getLevel())->toBe('error');
        });

        it('sets and returns custom level', function () {
            $this->handler->setLevel('debug');
            expect($this->handler->getLevel())->toBe('debug');

            $this->handler->setLevel('warning');
            expect($this->handler->getLevel())->toBe('warning');
        });
    });

    describe('clear()', function () {
        it('clears all logs', function () {
            $this->handler->log('Message 1', 'info', []);
            $this->handler->log('Message 2', 'error', []);

            expect(count($this->handler->getLogs()))->toBe(2);

            $this->handler->clear();

            expect(count($this->handler->getLogs()))->toBe(0);
        });
    });

    describe('getLogs()', function () {
        it('returns empty array when no logs', function () {
            expect($this->handler->getLogs())->toBeEmpty();
        });

        it('includes timestamp in logs', function () {
            $before = time();
            $this->handler->log('Test', 'info', []);
            $after = time();

            $logs = $this->handler->getLogs();
            expect($logs[0]['timestamp'])->toBeGreaterThanOrEqual($before)
                ->and($logs[0]['timestamp'])->toBeLessThanOrEqual($after);
        });
    });

    describe('fatal()', function () {
        it('does not throw when called with log=true', function () {
            expect(fn() => $this->handler->fatal('test_message', true))->not->toThrow(Throwable::class);
        });

        it('does not throw when called with log=false', function () {
            expect(fn() => $this->handler->fatal('test_message', false))->not->toThrow(Throwable::class);
        });
    });
});
