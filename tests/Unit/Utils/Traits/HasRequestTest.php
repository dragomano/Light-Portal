<?php

declare(strict_types=1);

use LightPortal\Utils\FileInterface;
use LightPortal\Utils\PostInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\Traits\HasRequest;
use Tests\AppMockRegistry;

beforeEach(function () {
    $this->requestMock = mock(RequestInterface::class);
    $this->postMock = mock(PostInterface::class);
    $this->filesMock = mock(FileInterface::class);

    AppMockRegistry::set(RequestInterface::class, $this->requestMock);
    AppMockRegistry::set(PostInterface::class, $this->postMock);
    AppMockRegistry::set(FileInterface::class, $this->filesMock);

    $this->testClass = new class {
        use HasRequest;
    };
});

afterEach(function () {
    AppMockRegistry::clear(RequestInterface::class);
    AppMockRegistry::clear(PostInterface::class);
    AppMockRegistry::clear(FileInterface::class);
});

describe('HasRequest::request()', function () {
    it('returns request interface', function () {
        $result = $this->testClass->request();

        expect($result)->toBeInstanceOf(RequestInterface::class);
    });
});

describe('HasRequest::post()', function () {
    it('returns post interface', function () {
        $result = $this->testClass->post();

        expect($result)->toBeInstanceOf(PostInterface::class);
    });
});

describe('HasRequest::files()', function () {
    it('returns file interface', function () {
        $result = $this->testClass->files();

        expect($result)->toBeInstanceOf(FileInterface::class);
    });
});
