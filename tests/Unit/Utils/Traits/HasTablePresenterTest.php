<?php

declare(strict_types=1);

use Bugo\Bricks\Tables\Interfaces\TablePresenterInterface;
use LightPortal\Utils\Traits\HasTablePresenter;

beforeEach(function () {
    $this->testClass = new class {
        use HasTablePresenter;
    };
});

it('returns TablePresenterInterface instance', function () {
    $result = $this->testClass->getTablePresenter();

    expect($result)->toBeInstanceOf(TablePresenterInterface::class);
});
