<?php

declare(strict_types=1);

use LightPortal\UI\Breadcrumbs\BreadcrumbWrapper;
use LightPortal\Utils\Traits\HasBreadcrumbs;

beforeEach(function () {
    $this->testClass = new class {
        use HasBreadcrumbs;
    };
});

it('returns BreadcrumbWrapper instance', function () {
    $result = $this->testClass->breadcrumbs();

    expect($result)->toBeInstanceOf(BreadcrumbWrapper::class);
});
