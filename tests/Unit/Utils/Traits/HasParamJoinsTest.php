<?php

declare(strict_types=1);

use Laminas\Db\Sql\Select;
use LightPortal\Utils\Traits\HasParamJoins;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->testClass = new class {
        use HasParamJoins;
    };

    $this->reflection = new ReflectionAccessor($this->testClass);
});

describe('HasParamJoins::normalizeParamConfig()', function () {
    it('handles integer key with string value', function () {
        $result = $this->reflection->callMethod('normalizeParamConfig', [1, 'param_value', 1]);

        expect($result)->toBeArray()
            ->and($result[0])->toBe('param_value')
            ->and($result[1])->toBe('pp')
            ->and($result[2])->toBeEmpty();
    });

    it('handles integer key with index greater than 1', function () {
        $result = $this->reflection->callMethod('normalizeParamConfig', [2, 'another_param', 2]);

        expect($result)->toBeArray()
            ->and($result[0])->toBe('another_param')
            ->and($result[1])->toBe('pp2')
            ->and($result[2])->toBeEmpty();
    });

    it('handles string key with associative config', function () {
        $config = ['alias' => 'custom_alias', 'columns' => ['id', 'value']];
        $result = $this->reflection->callMethod('normalizeParamConfig', ['custom_param', $config, 1]);

        expect($result)->toBeArray()
            ->and($result[0])->toBe('custom_param')
            ->and($result[1])->toBe('custom_alias')
            ->and($result[2])->toBe(['id', 'value']);
    });

    it('handles string key with partial config', function () {
        $config = ['columns' => ['name']];
        $result = $this->reflection->callMethod('normalizeParamConfig', ['partial_param', $config, 1]);

        expect($result)->toBeArray()
            ->and($result[0])->toBe('partial_param')
            ->and($result[1])->toBe('pp')
            ->and($result[2])->toBe(['name']);
    });

    it('handles string key without config', function () {
        $result = $this->reflection->callMethod('normalizeParamConfig', ['no_config_param', [], 1]);

        expect($result)->toBeArray()
            ->and($result[0])->toBe('no_config_param')
            ->and($result[1])->toBe('pp')
            ->and($result[2])->toBeEmpty();
    });
});
