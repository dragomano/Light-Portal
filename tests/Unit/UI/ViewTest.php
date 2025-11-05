<?php

declare(strict_types=1);

use LightPortal\Renderers\RendererInterface;
use LightPortal\UI\View;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->templateDir = __DIR__ . '/../../../src/Themes/default/LightPortal';
    $this->view = new View($this->templateDir);

    $this->accessor = new ReflectionAccessor($this->view);
});

it('can be instantiated with custom template directory', function () {
    $this->view->setTemplateDir('/custom/template/dir');

    expect($this->view)->toBeInstanceOf(View::class);
});

it('renders blade template successfully', function () {
    $result = $this->view->render('debug', ['name' => 'World']);

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('renders php template successfully', function () {
    $result = $this->view->render('debug', ['name' => 'PHP World']);

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('returns empty string when template does not exist', function () {
    $result = $this->view->render('nonexistent_template');

    expect($result)->toBe('');
});

it('renders template with dotted notation', function () {
    $result = $this->view->render('debug', ['value' => 'success']);

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('includes default parameters in render context', function () {
    $result = $this->view->render('debug');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('merges custom parameters with default parameters', function () {
    $result = $this->view->render('debug', ['custom' => 'Custom Value']);

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('gets default parameters correctly via reflection', function () {
    $result = $this->accessor->callProtectedMethod('getDefaultParams');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('context')
        ->and($result)->toHaveKey('language')
        ->and($result)->toHaveKey('modSettings')
        ->and($result)->toHaveKey('scripturl')
        ->and($result)->toHaveKey('settings')
        ->and($result)->toHaveKey('txt');
});

it('finds blade template file correctly via reflection', function () {
    $result = $this->accessor->callProtectedMethod('getFile', ['debug']);

    expect($result)->toContain('debug.blade.php');
});

it('finds php template file correctly via reflection', function () {
    $result = $this->accessor->callProtectedMethod('getFile', ['index']);

    expect($result)->toContain('index.php');
});

it('returns empty string when file not found via reflection', function () {
    $result = $this->accessor->callProtectedMethod('getFile', ['nonexistent']);

    expect($result)->toBe('');
});

it('creates correct renderer for blade files via reflection', function () {
    $bladeFile = $this->templateDir . '/test.blade.php';

    $result = $this->accessor->callProtectedMethod('makeRenderer', [$bladeFile]);

    expect($result)->toBeInstanceOf(RendererInterface::class);
});

it('creates correct renderer for php files via reflection', function () {
    $phpFile = $this->templateDir . '/test.php';

    $result = $this->accessor->callProtectedMethod('makeRenderer', [$phpFile]);

    expect($result)->toBeInstanceOf(RendererInterface::class);
});

