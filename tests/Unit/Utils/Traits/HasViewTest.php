<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use LightPortal\UI\View;
use LightPortal\Utils\Traits\HasView;
use Tests\ReflectionAccessor;

beforeEach(function () {
    mock('overload:LightPortal\UI\View')
        ->shouldReceive('render')
        ->andReturn('<div>rendered</div>');

    $this->testClass = new class {
        use HasView;

        public function callUseLayerAbove(string $template = 'default', array $params = []): void
        {
            $this->useLayerAbove($template, $params);
        }

        public function callUseCustomTemplate(string $template = 'default', array $params = []): void
        {
            $this->useCustomTemplate($template, $params);
        }

        public function callView(string $template = 'default', array $params = []): string
        {
            return $this->view($template, $params);
        }

        public function callViewInstance(): View
        {
            return $this->viewInstance();
        }
    };

    $this->reflection = new ReflectionAccessor($this->testClass);

    Utils::$context = [];
});

it('sets layer above content correctly', function () {
    $this->testClass->callUseLayerAbove('default', ['param' => 'value']);

    expect(Utils::$context['template_layers'])->toContain('custom')
        ->and(Utils::$context['lp_layer_above_content'])->toBeString();
});

it('sets custom template correctly', function () {
    $this->testClass->callUseCustomTemplate('default', ['param' => 'value']);

    expect(Utils::$context['sub_template'])->toBe('custom')
        ->and(Utils::$context['lp_custom_content'])->toBeString();
});

it('renders view correctly', function () {
    $result = $this->testClass->callView('default', ['param' => 'value']);

    expect($result)->toBeString();
});

it('returns view instance correctly', function () {
    $viewInstance = $this->testClass->callViewInstance();

    expect($viewInstance)->toBeInstanceOf(View::class);
});

it('caches view instance', function () {
    $viewInstance1 = $this->testClass->callViewInstance();
    $viewInstance2 = $this->testClass->callViewInstance();

    expect($viewInstance1)->toBe($viewInstance2);
});
