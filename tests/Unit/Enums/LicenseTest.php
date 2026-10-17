<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\Enums\License;

beforeEach(function () {
    Lang::$txt['lang_locale'] = 'en_US';
});

it('has cases with string values', function () {
    expect(License::cases())->not->toBeEmpty()
        ->and(License::Mit->value)->toBe('MIT')
        ->and(License::Gpl3->value)->toBe('GPL-3.0')
        ->and(License::CcBySa4->value)->toBe('CC BY-SA 4.0');
});

it('returns a https url for every case', function () {
    foreach (License::cases() as $case) {
        expect($case->url())->toStartWith('https://');
    }
});

it('returns correct urls for known licenses', function () {
    expect(License::Apache2->url())->toBe('https://www.apache.org/licenses/LICENSE-2.0')
        ->and(License::Mit->url())->toBe('https://opensource.org/licenses/MIT')
        ->and(License::Gpl3->url())->toBe('https://www.gnu.org/licenses/gpl-3.0.html')
        ->and(License::Agpl3->url())->toBe('https://www.gnu.org/licenses/agpl-3.0.html')
        ->and(License::Isc->url())->toBe('https://opensource.org/licenses/ISC')
        ->and(License::Cc0->url())->toBe('https://creativecommons.org/publicdomain/zero/1.0/')
        ->and(License::CcBy4->url())->toBe('https://creativecommons.org/licenses/by/4.0/')
        ->and(License::CcBySa4->url())->toBe('https://creativecommons.org/licenses/by-sa/4.0/')
        ->and(License::FontAwesomeFree->url())->toBe('https://fontawesome.com/license/free')
        ->and(License::GoodBoy->url())->toBe('https://icons8.com/good-boy-license')
        ->and(License::PictogrammersFree->url())->toBe('https://pictogrammers.com/docs/general/license/')
        ->and(License::Bsd2->url())->toBe('https://opensource.org/licenses/BSD-2-Clause');
});

it('maps both BSD-3 and New BSD to the same url', function () {
    expect(License::Bsd3->url())->toBe('https://opensource.org/licenses/BSD-3-Clause')
        ->and(License::NewBsd->url())->toBe('https://opensource.org/licenses/BSD-3-Clause');
});

it('tries to get a case by name', function () {
    expect(License::tryFromName('Mit'))->toBe(License::Mit)
        ->and(License::tryFromName('Gpl3'))->toBe(License::Gpl3)
        ->and(License::tryFromName('mit'))->toBeNull()
        ->and(License::tryFromName('UnknownLicense'))->toBeNull();
});
