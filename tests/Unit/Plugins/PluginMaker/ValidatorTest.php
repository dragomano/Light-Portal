<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Plugins\PluginMaker\Validator;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->validator = new Validator(
        mock(PortalSqlInterface::class),
        mock(EventDispatcherInterface::class)
    );
    $this->accessor = new ReflectionAccessor($this->validator);

    Lang::$txt['lp_post_error_no_name'] ??= 'The <strong>name</strong> field was not filled out. It is required.';
    Lang::$txt['lp_plugin_maker'] ??= [];
    Lang::$txt['lp_plugin_maker'] += [
        'no_unique_name' => 'A plugin with this name already exists!',
    ];

    unset(Utils::$context['post_errors']);
});

describe('handleErrors', function () {
    it('prefers the core error message when it exists', function () {
        $this->accessor->setProperty('errors', ['no_name']);

        $this->accessor->callMethod('handleErrors');

        expect(Utils::$context['post_errors'])->toBe([Lang::$txt['lp_post_error_no_name']]);
    });

    it('falls back to the plugin lang when the core key is missing', function () {
        $this->accessor->setProperty('errors', ['no_unique_name']);

        $this->accessor->callMethod('handleErrors');

        expect(Utils::$context['post_errors'])->toBe([Lang::$txt['lp_plugin_maker']['no_unique_name']]);
    });

    it('uses the raw error key as the last resort', function () {
        $this->accessor->setProperty('errors', ['unknown_error']);

        $this->accessor->callMethod('handleErrors');

        expect(Utils::$context['post_errors'])->toBe(['unknown_error']);
    });

    it('sets nothing when there are no errors', function () {
        $this->accessor->callMethod('handleErrors');

        expect(Utils::$context['post_errors'] ?? null)->toBeNull();
    });
});
