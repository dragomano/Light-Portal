<?php

declare(strict_types=1);

use LightPortal\Enums\License;
use LightPortal\Plugins\CreditsFactory;

it('creates a credit entry via fluent api', function () {
    $credits = CreditsFactory::make()
        ->title('Swiper')
        ->link('https://swiperjs.com')
        ->author('Vladimir Kharlampidi')
        ->license(License::Mit)
        ->toArray();

    expect($credits)->toBe([
        [
            'title'   => 'Swiper',
            'link'    => 'https://swiperjs.com',
            'author'  => 'Vladimir Kharlampidi',
            'license' => [
                'name' => 'MIT',
                'link' => 'https://opensource.org/licenses/MIT',
            ],
        ],
    ]);
});

it('accepts a license as a plain string', function () {
    $credits = CreditsFactory::make()
        ->title('Custom lib')
        ->license('Custom License', 'https://example.com/license')
        ->toArray();

    expect($credits[0]['license'])->toBe([
        'name' => 'Custom License',
        'link' => 'https://example.com/license',
    ]);
});

it('uses license url when no custom link is passed', function () {
    $credits = CreditsFactory::make()
        ->title('Custom lib')
        ->license('Some License')
        ->toArray();

    expect($credits[0]['license'])->toBe([
        'name' => 'Some License',
        'link' => '',
    ]);
});

it('groups entries by title', function () {
    $credits = CreditsFactory::make()
        ->title('First lib')
        ->author('Author 1')
        ->license(License::Mit)
        ->title('Second lib')
        ->author('Author 2')
        ->toArray();

    expect($credits)->toHaveCount(2)
        ->and($credits[0]['title'])->toBe('First lib')
        ->and($credits[1]['title'])->toBe('Second lib')
        ->and($credits[1])->not->toHaveKey('link');
});

it('drops entries without a title', function () {
    $credits = CreditsFactory::make()
        ->author('Orphan author')
        ->license(License::Mit)
        ->toArray();

    expect($credits)->toBeEmpty();
});

it('returns an empty array by default', function () {
    expect(CreditsFactory::make()->toArray())->toBeEmpty();
});
