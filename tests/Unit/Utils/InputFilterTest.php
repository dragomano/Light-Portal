<?php

declare(strict_types=1);

use LightPortal\Utils\InputFilter;
use LightPortal\Utils\PostInterface;
use LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;

describe('InputFilter', function () {
    beforeEach(function () {
        $this->requestMock = mock(RequestInterface::class);
        $this->postMock = mock(PostInterface::class);

        AppMockRegistry::set(RequestInterface::class, $this->requestMock);
        AppMockRegistry::set(PostInterface::class, $this->postMock);
    });

    describe('filter()', function () {
        it('returns empty array when request has no matching keys', function () {
            $this->requestMock->shouldReceive('has')->with('id')->andReturn(false);
            $this->requestMock->shouldReceive('has')->with('name')->andReturn(false);

            $filter = new InputFilter();

            $result = $filter->filter([
                ['int', 'id'],
                ['string', 'name'],
            ]);

            expect($result)->toBeEmpty();
        });

        it('filters integer values correctly', function () {
            $this->requestMock->shouldReceive('has')->with('id')->andReturn(true);
            $this->requestMock->shouldReceive('get')->with('id')->andReturn('42');

            $filter = new InputFilter();

            $result = $filter->filter([
                ['int', 'id'],
            ]);

            expect($result['id'])->toBeInt();
        });

        it('filters float values correctly', function () {
            $this->requestMock->shouldReceive('has')->with('price')->andReturn(true);
            $this->requestMock->shouldReceive('get')->with('price')->andReturn('19.99');

            $filter = new InputFilter();

            $result = $filter->filter([
                ['float', 'price'],
            ]);

            expect($result['price'])->toBeFloat();
        });

        it('filters check (boolean) values correctly', function () {
            $this->requestMock->shouldReceive('has')->with('enabled')->andReturn(true);
            $this->requestMock->shouldReceive('get')->with('enabled')->andReturn('1');

            $filter = new InputFilter();

            $result = $filter->filter([
                ['check', 'enabled'],
            ]);

            expect($result['enabled'])->toBeBool();
        });

        it('filters url values correctly', function () {
            $this->requestMock->shouldReceive('has')->with('link')->andReturn(true);
            $this->requestMock->shouldReceive('get')->with('link')->andReturn('https://example.com');

            $filter = new InputFilter();

            $result = $filter->filter([
                ['url', 'link'],
            ]);

            expect($result['link'])->toBeString();
        });

        it('ignores unknown types', function () {
            $this->requestMock->shouldReceive('has')->with('field')->andReturn(true);
            $this->requestMock->shouldReceive('get')->with('field')->andReturn('value');

            $filter = new InputFilter();

            $result = $filter->filter([
                ['unknown', 'field'],
            ]);

            expect($result)->toHaveKey('field');
        });
    });
});
