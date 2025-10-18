<?php

declare(strict_types=1);

use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\PsrLoggerAdapter;
use Psr\Log\LoggerInterface;

it('implements PSR-3 LoggerInterface', function () {
    expect(PsrLoggerAdapter::class)
        ->toImplement(LoggerInterface::class);
});

it('has required methods', function () {
    expect(PsrLoggerAdapter::class)
        ->toHaveMethods([
            'emergency', 'alert', 'critical', 'error', 'warning',
            'notice', 'info', 'debug', 'log'
        ]);
});

it('maps PSR-3 levels to SMF types correctly', function () {
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

    $logger = new PsrLoggerAdapter($errorHandlerMock);

    // Test critical levels
    $errorHandlerMock->shouldReceive('log')
        ->with('Test message', 'critical', [])
        ->once();

    $logger->log('emergency', 'Test message');

    $errorHandlerMock->shouldReceive('log')
        ->with('Test message', 'critical', [])
        ->once();

    $logger->log('alert', 'Test message');

    $errorHandlerMock->shouldReceive('log')
        ->with('Test message', 'critical', [])
        ->once();

    $logger->log('critical', 'Test message');

    // Test general levels
    $errorHandlerMock->shouldReceive('log')
        ->with('Test message', 'general', [])
        ->once();

    $logger->log('error', 'Test message');

    $errorHandlerMock->shouldReceive('log')
        ->with('Test message', 'general', [])
        ->once();

    $logger->log('warning', 'Test message');

    $errorHandlerMock->shouldReceive('log')
        ->with('Test message', 'general', [])
        ->once();

    $logger->log('notice', 'Test message');

    $errorHandlerMock->shouldReceive('log')
        ->with('Test message', 'general', [])
        ->once();

    $logger->log('info', 'Test message');

    // Test debug level
    $errorHandlerMock->shouldReceive('log')
        ->with('Test message', 'debug', [])
        ->once();

    $logger->log('debug', 'Test message');

    // Test unknown level defaults to general
    $errorHandlerMock->shouldReceive('log')
        ->with('Test message', 'general', [])
        ->once();

    $logger->log('unknown', 'Test message');
});

it('delegates emergency calls to log method correctly', function () {
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);
    $errorHandlerMock->shouldReceive('log')
        ->with('Emergency message', 'critical', ['user' => 'test'])
        ->once();

    $logger = new PsrLoggerAdapter($errorHandlerMock);

    $logger->emergency('Emergency message', ['user' => 'test']);
});

it('delegates alert calls to log method correctly', function () {
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);
    $errorHandlerMock->shouldReceive('log')
        ->with('Alert message', 'critical', [])
        ->once();

    $logger = new PsrLoggerAdapter($errorHandlerMock);

    $logger->alert('Alert message');
});

it('delegates critical calls to log method correctly', function () {
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);
    $errorHandlerMock->shouldReceive('log')
        ->with('Critical message', 'critical', [])
        ->once();

    $logger = new PsrLoggerAdapter($errorHandlerMock);

    $logger->critical('Critical message');
});

it('delegates error calls to log method correctly', function () {
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);
    $errorHandlerMock->shouldReceive('log')
        ->with('Error message', 'general', [])
        ->once();

    $logger = new PsrLoggerAdapter($errorHandlerMock);

    $logger->error('Error message');
});

it('delegates warning calls to log method correctly', function () {
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);
    $errorHandlerMock->shouldReceive('log')
        ->with('Warning message', 'general', [])
        ->once();

    $logger = new PsrLoggerAdapter($errorHandlerMock);

    $logger->warning('Warning message');
});

it('delegates notice calls to log method correctly', function () {
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);
    $errorHandlerMock->shouldReceive('log')
        ->with('Notice message', 'general', [])
        ->once();

    $logger = new PsrLoggerAdapter($errorHandlerMock);

    $logger->notice('Notice message');
});

it('delegates info calls to log method correctly', function () {
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);
    $errorHandlerMock->shouldReceive('log')
        ->with('Info message', 'general', [])
        ->once();

    $logger = new PsrLoggerAdapter($errorHandlerMock);

    $logger->info('Info message');
});

it('delegates debug calls to log method correctly', function () {
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);
    $errorHandlerMock->shouldReceive('log')
        ->with('Debug message', 'debug', [])
        ->once();

    $logger = new PsrLoggerAdapter($errorHandlerMock);

    $logger->debug('Debug message');
});

it('handles Stringable message objects', function () {
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);
    $errorHandlerMock->shouldReceive('log')
        ->with('Stringable message', 'general', [])
        ->once();

    $logger = new PsrLoggerAdapter($errorHandlerMock);

    $message = new class implements Stringable {
        public function __toString(): string
        {
            return 'Stringable message';
        }
    };

    $logger->info($message);
});

it('passes context array to error handler', function () {
    $errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);
    $context = ['key' => 'value', 'number' => 42];
    $errorHandlerMock->shouldReceive('log')
        ->with('Test', 'general', $context)
        ->once();

    $logger = new PsrLoggerAdapter($errorHandlerMock);

    $logger->info('Test', $context);
});
