<?php

declare(strict_types=1);

use LightPortal\UI\ViewInterface;
use LightPortal\Utils\Traits\HasView;

beforeEach(function () {
    $this->mockView = mock(ViewInterface::class);
});

it('returns rendered content from view method', function () {
    $this->mockView->shouldReceive('render')
        ->once()
        ->with('test_template', ['param' => 'value'])
        ->andReturn('<div>rendered content</div>');

    $testClass = new class {
        use HasView;

        public function setView(ViewInterface $view): void
        {
            $this->view = $view;
        }
    };

    $testClass->setView($this->mockView);

    $result = $testClass->view('test_template', ['param' => 'value']);

    expect($result)->toBe('<div>rendered content</div>');
});

it('returns rendered content from view method with default template', function () {
    $this->mockView->shouldReceive('render')
        ->once()
        ->with('default', [])
        ->andReturn('<div>default content</div>');

    $testClass = new class {
        use HasView;

        public function setView(ViewInterface $view): void
        {
            $this->view = $view;
        }
    };

    $testClass->setView($this->mockView);

    $result = $testClass->view();

    expect($result)->toBe('<div>default content</div>');
});

it('initializes view instance when not set', function () {
    $testClass = new class {
        use HasView;

        public function callViewInstance(): ViewInterface
        {
            return $this->viewInstance();
        }
    };

    $viewInstance = $testClass->callViewInstance();

    expect($viewInstance)->toBeInstanceOf(ViewInterface::class);
});

it('reuses existing view instance', function () {
    $testClass = new class {
        use HasView;

        public function setView(ViewInterface $view): void
        {
            $this->view = $view;
        }

        public function callViewInstance(): ViewInterface
        {
            return $this->viewInstance();
        }
    };

    $testClass->setView($this->mockView);

    $viewInstance = $testClass->callViewInstance();

    expect($viewInstance)->toBe($this->mockView);
});

it('calls useCustomTemplate method', function () {
    $this->mockView->shouldReceive('render')
        ->once()
        ->with('custom_template', ['key' => 'value'])
        ->andReturn('<p>custom content</p>');

    $testClass = new class {
        use HasView;

        public function setView(ViewInterface $view): void
        {
            $this->view = $view;
        }
    };

    $testClass->setView($this->mockView);
    $testClass->useCustomTemplate('custom_template', ['key' => 'value']);

    expect(true)->toBeTrue();
});

