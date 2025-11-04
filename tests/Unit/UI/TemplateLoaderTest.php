<?php

declare(strict_types=1);

use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\UI\TemplateLoader;
use LightPortal\UI\ViewInterface;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->reflection = new ReflectionAccessor(new TemplateLoader);
    $this->reflection->setProtectedProperty('view', null);
    $this->reflection->setProtectedProperty('content', '');
});

it('renders template from file when template exists', function () {
    $result = TemplateLoader::fromFile('debug');

    expect($result)->toBeString()
        ->and(strlen($result))->toBeGreaterThan(0);
});

it('returns false when template name is empty', function () {
    $result = TemplateLoader::fromFile();

    expect($result)->toBeFalse();
});

it('returns last content from getLastContent', function () {
    $this->reflection->setProtectedProperty('content', '<div>Test content</div>');

    $result = TemplateLoader::getLastContent();

    expect($result)->toBe('<div>Test content</div>');
});

it('returns empty string when no content has been set', function () {
    $result = TemplateLoader::getLastContent();

    expect($result)->toBe('');
});

it('checks if template exists via reflection', function () {
    $result = $this->reflection->callProtectedMethod('templateExists', ['debug']);

    expect($result)->toBeTrue();

    $result = $this->reflection->callProtectedMethod('templateExists', ['nonexistent_template']);

    expect($result)->toBeFalse();
});

it('generates correct template path via reflection', function () {
    $result = $this->reflection->callProtectedMethod('getTemplatePath', ['test_template']);

    $expectedPath = Theme::$current->settings['default_theme_dir'] . '/LightPortal/test_template.blade.php';

    expect($result)->toBe($expectedPath);
});

it('returns correct template base path via reflection', function () {
    $result = $this->reflection->callProtectedMethod('getTemplateBasePath');

    $expectedPath = Theme::$current->settings['default_theme_dir'] . '/LightPortal';

    expect($result)->toBe($expectedPath);
});

it('initializes view correctly via reflection', function () {
    $this->reflection->setProtectedProperty('view', null);
    $this->reflection->callProtectedMethod('initView');

    $view = $this->reflection->getProtectedProperty('view');

    expect($view)->toBeInstanceOf(ViewInterface::class);
});

it('does not reinitialize view if already set', function () {
    $mockView = mock(ViewInterface::class);
    $this->reflection->setProtectedProperty('view', $mockView);
    $this->reflection->callProtectedMethod('initView');

    $view = $this->reflection->getProtectedProperty('view');

    expect($view)->toBe($mockView);
});

it('sets sub_template context when useSubTemplate is true', function () {
    TemplateLoader::fromFile('debug');

    expect(Utils::$context['sub_template'])->toBe('lp_blade_wrapper');
});

it('does not set sub_template context when useSubTemplate is false', function () {
    TemplateLoader::fromFile('debug', [], false);

    expect(Utils::$context)->not->toHaveKey('sub_template');
});

it('stores content when useSubTemplate is true', function () {
    TemplateLoader::fromFile('debug');

    $lastContent = TemplateLoader::getLastContent();

    expect($lastContent)->toBeString()
        ->and(strlen($lastContent))->toBeGreaterThan(0);
});
