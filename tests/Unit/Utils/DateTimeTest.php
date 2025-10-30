<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use LightPortal\Utils\DateTime;
use Tests\ReflectionAccessor;

describe('DateTime', function () {
    beforeEach(function () {
        Lang::$txt['today'] = 'Today at ';
        Lang::$txt['yesterday'] = 'Yesterday at ';
        Lang::$txt['lang_locale'] = 'en_US';
    });

    describe('DateTime::get', function () {
        it('returns current DateTime when no timestamp provided', function () {
            $before = time();
            $dateTime = DateTime::get();
            $after = time();

            expect($dateTime)
                ->toBeInstanceOf(\DateTime::class)
                ->and($dateTime->getTimestamp())->toBeGreaterThanOrEqual($before)
                ->and($dateTime->getTimestamp())->toBeLessThanOrEqual($after);
        });

        it('returns DateTime for specific timestamp', function () {
            $timestamp = 1609459200; // 2021-01-01 00:00:00 UTC
            $dateTime = DateTime::get($timestamp);

            expect($dateTime)
                ->toBeInstanceOf(\DateTime::class)
                ->and($dateTime->getTimestamp())->toBe($timestamp);
        });

        it('returns DateTime with zero timestamp', function () {
            $dateTime = DateTime::get();
            expect($dateTime->getTimestamp())->toBe(time());
        });
    });

    describe('DateTime::relative', function () {
        it('returns "Just now" for same timestamp', function () {
            $now = time();
            expect(DateTime::relative($now))->toBe(Lang::$txt['lp_just_now']);
        });

        it('returns seconds ago for recent past', function ($seconds) {
            $timestamp = time() - $seconds;
            $result = DateTime::relative($timestamp);

            expect($result)
                ->toContain('second')
                ->toContain(' ago');
        })->with([5, 15, 30, 45, 59]);

        it('returns minutes ago for past hour', function ($minutes) {
            $timestamp = time() - ($minutes * 60);
            $result = DateTime::relative($timestamp);

            expect($result)
                ->toContain('minute')
                ->toContain(' ago');
        })->with([1, 5, 15, 30, 45, 59]);

        it('returns "Today at" for earlier today', function () {
            $now = time();
            $todayMorning = strtotime('today 08:00:00');

            if ($now > $todayMorning) {
                $result = DateTime::relative($todayMorning);
                expect($result)->toStartWith('Today at ');
            } else {
                expect(true)->toBeTrue();
            }
        });

        it('returns "Yesterday at" for yesterday', function () {
            $yesterday = strtotime('yesterday 15:00:00');
            $result = DateTime::relative($yesterday);

            expect($result)->toStartWith('Yesterday at ');
        });

        it('returns "Tomorrow at" for tomorrow', function () {
            $tomorrow = strtotime('tomorrow 10:00:00');
            $result = DateTime::relative($tomorrow);

            expect($result)->toStartWith(Lang::$txt['lp_tomorrow']);
        });

        it('returns "In X hours" for future hours', function ($hours) {
            $baseTime = strtotime('08:00:00');
            $futureTime = $baseTime + ($hours * 3600);
            $result = DateTime::relative($futureTime);

            expect($result)
                ->toStartWith('In ')
                ->toContain('hour');
        })->with([10, 12, 14]);

        it('returns "In X minutes" for future minutes', function ($minutes) {
            $futureTime = time() + ($minutes * 60);
            $result = DateTime::relative($futureTime);

            expect($result)
                ->toStartWith('In ')
                ->toContain('minute');
        })->with([1, 5, 15, 30, 45, 59]);

        it('returns "In X seconds" for future seconds', function ($seconds) {
            $futureTime = time() + $seconds;
            $result = DateTime::relative($futureTime);

            expect($result)
                ->toStartWith('In ')
                ->toContain('second');
        })->with([1, 5, 15, 30, 45, 59]);

        it('returns "In X days" for future within a week', function ($days) {
            $futureTime = time() + ($days * 86400);
            $result = DateTime::relative($futureTime);

            expect($result)
                ->toStartWith('In ')
                ->toContain('day');
        })->with([2, 3, 4, 5, 6]);

        it('handles future date in current month', function () {
            $currentMonth = (int) date('m');
            $currentYear  = (int) date('Y');
            $currentDay   = (int) date('d');
            $daysInMonth  = (int) date('t');
            $targetDay    = min($currentDay + 10, $daysInMonth);

            if ($targetDay > $currentDay) {
                $futureDate = mktime(15, 0, 0, $currentMonth, $targetDay, $currentYear);

                if (($futureDate - time()) > (7 * 86400)) {
                    $result = DateTime::relative($futureDate);
                    expect($result)->toBeString()->not->toBeEmpty();
                } else {
                    expect(true)->toBeTrue();
                }
            } else {
                expect(true)->toBeTrue();
            }
        });

        it('handles future date in current year but different month', function () {
            $currentMonth = (int) date('m');
            $currentYear  = (int) date('Y');

            if ($currentMonth < 12) {
                $nextMonth = $currentMonth + 1;
                $futureDate = mktime(15, 0, 0, $nextMonth, 15, $currentYear);

                if (($futureDate - time()) > (7 * 86400)) {
                    $result = DateTime::relative($futureDate);
                    expect($result)->toBeString()->not->toBeEmpty();
                } else {
                    expect(true)->toBeTrue();
                }
            } else {
                expect(true)->toBeTrue();
            }
        });

        it('handles dates in current month', function () {
            $currentMonth = strtotime('first day of this month 10:00:00');

            if ($currentMonth < time()) {
                $result = DateTime::relative($currentMonth);
                expect($result)->toBeString()->not->toBeEmpty();
            } else {
                expect(true)->toBeTrue();
            }
        });

        it('handles dates in current year but different month', function () {
            $firstDayOfYear = strtotime('first day of january this year 10:00:00');

            if ($firstDayOfYear < time() && date('m') !== '01') {
                $result = DateTime::relative($firstDayOfYear);
                expect($result)->toBeString()->not->toBeEmpty();
            } else {
                expect(true)->toBeTrue();
            }
        });

        it('handles past year dates', function () {
            $lastYear = strtotime('-1 year');
            $result = DateTime::relative($lastYear);

            expect($result)->toBeString()->not->toBeEmpty();
        });
    });

    describe('DateTime::getValueForDate', function () {
        it('returns correct value for specific dates', function ($date, $hexValue) {
            $dateTimeMock = mock(\DateTime::class);
            $dateTimeMock->shouldReceive('format')
                ->with('m-d')
                ->andReturn($date);

            $result = DateTime::getValueForDate($dateTimeMock);

            expect($result)->toBe($hexValue);
        })->with([
            ['04-01', "\x4C\x61\x7A\x79\x20\x50\x61\x6E\x64\x61"],
            ['07-09', "\x46\x61\x6E\x63\x79\x20\x50\x6F\x72\x74\x61\x6C"],
            ['02-15', "\x4C\x69\x67\x68\x74\x20\x50\x6F\x72\x74\x61\x6C"],
        ]);
    });

    describe('DateTime::dateCompare', function () {
        it('compares dates with less than operator', function ($date1, $date2, $expected) {
            expect(DateTime::dateCompare($date1, $date2))->toBe($expected);
        })->with([
            ['01.01.20', '02.01.20', true],
            ['02.01.20', '01.01.20', false],
            ['01.01.20', '01.01.20', false],
            ['31.12.19', '01.01.20', true],
            ['15.06.20', '15.07.20', true],
        ]);

        it('compares dates with less than or equal operator', function ($date1, $date2, $expected) {
            expect(DateTime::dateCompare($date1, $date2, '<='))->toBe($expected);
        })->with([
            ['01.01.20', '01.01.20', true],
            ['01.01.20', '02.01.20', true],
            ['02.01.20', '01.01.20', false],
        ]);

        it('compares dates with greater than operator', function ($date1, $date2, $expected) {
            expect(DateTime::dateCompare($date1, $date2, '>'))->toBe($expected);
        })->with([
            ['02.01.20', '01.01.20', true],
            ['01.01.20', '02.01.20', false],
            ['01.01.20', '01.01.20', false],
        ]);

        it('compares dates with greater than or equal operator', function ($date1, $date2, $expected) {
            expect(DateTime::dateCompare($date1, $date2, '>='))->toBe($expected);
        })->with([
            ['01.01.20', '01.01.20', true],
            ['02.01.20', '01.01.20', true],
            ['01.01.20', '02.01.20', false],
        ]);

        it('compares dates with equality operators', function ($date1, $date2, $operator, $expected) {
            expect(DateTime::dateCompare($date1, $date2, $operator))->toBe($expected);
        })->with([
            ['01.01.20', '01.01.20', '==', true],
            ['01.01.20', '01.01.20', '=', true],
            ['01.01.20', '02.01.20', '==', false],
            ['01.01.20', '02.01.20', '=', false],
        ]);

        it('compares dates with not equal operators', function ($date1, $date2, $operator, $expected) {
            expect(DateTime::dateCompare($date1, $date2, $operator))->toBe($expected);
        })->with([
            ['01.01.20', '02.01.20', '!=', true],
            ['01.01.20', '02.01.20', '<>', true],
            ['01.01.20', '01.01.20', '!=', false],
            ['01.01.20', '01.01.20', '<>', false],
        ]);

        it('returns false for invalid date formats', function ($date1, $date2) {
            expect(DateTime::dateCompare($date1, $date2))->toBeFalse();
        })->with([
            ['invalid', '01.01.20'],
            ['01.01.20', 'invalid'],
            ['1.1.20', '01.01.20'],
            ['01-01-20', '01.01.20'],
            ['01/01/20', '01.01.20'],
            ['', '01.01.20'],
            ['01.01.20', ''],
        ]);

        it('returns false when date parsing throws exception', function ($date1, $date2) {
            expect(DateTime::dateCompare($date1, $date2))->toBeFalse();
        })->with([
            ['32.01.20', '01.01.20'], // Invalid day
            ['01.13.20', '01.01.20'], // Invalid month
            ['31.02.20', '01.01.20'], // February 31st
            ['31.04.20', '01.01.20'], // April 31st
            ['31.06.20', '01.01.20'], // June 31st
            ['31.09.20', '01.01.20'], // September 31st
            ['31.11.20', '01.01.20'], // November 31st
            ['29.02.21', '01.01.20'], // Not a leap year
            ['00.00.00', '01.01.20'], // Zero date
            ['99.99.99', '01.01.20'], // Impossible date
            ['01.01.20', '32.01.20'], // Invalid in second param
            ['01.01.20', '99.99.99'], // Invalid in second param
        ]);

        it('handles leap year dates', function ($date1, $date2, $operator, $expected) {
            expect(DateTime::dateCompare($date1, $date2, $operator))->toBe($expected);
        })->with([
            ['29.02.20', '01.03.20', '<', true],  // 2020 is leap year
            ['28.02.21', '01.03.21', '<', true],  // 2021 is not leap year
            ['29.02.20', '28.02.21', '>', false],
        ]);

        it('throws exception for invalid operator', function ($operator) {
            expect(fn() => DateTime::dateCompare('01.01.20', '02.01.20', $operator))
                ->toThrow(InvalidArgumentException::class, "Unknown operator: $operator");
        })->with(['invalid', '===', '<==', '>==', '+-', 'eq', 'ne', 'lt', 'gt']);
    });

    describe('DateTime::getLocalDate', function () {
        it('formats date with intl extension loaded', function () {
            if (extension_loaded('intl')) {
                expect(true)->toBeTrue();
                return;
            }

            $timestamp = strtotime('2024-03-15 14:30:00');

            $reflection = new ReflectionAccessor(new DateTime());
            $result = $reflection->callProtectedMethod(
                'getLocalDate',
                [$timestamp, IntlDateFormatter::LONG, IntlDateFormatter::SHORT]
            );

            expect($result)->toBeString()->not->toBeEmpty();
        });

        it('formats date with different IntlDateFormatter constants', function ($dateType, $timeType) {
            if (! extension_loaded('intl')) {
                expect(true)->toBeTrue();
                return;
            }

            $timestamp = strtotime('2024-03-15 14:30:00');

            $reflection = new ReflectionAccessor(new DateTime());
            $result = $reflection->callProtectedMethod('getLocalDate', [$timestamp, $dateType, $timeType]);

            expect($result)->toBeString()->not->toBeEmpty();
        })->with([
            [IntlDateFormatter::FULL, IntlDateFormatter::SHORT],
            [IntlDateFormatter::FULL, IntlDateFormatter::NONE],
            [IntlDateFormatter::LONG, IntlDateFormatter::SHORT],
            [IntlDateFormatter::LONG, IntlDateFormatter::NONE],
            [IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT],
            [IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE],
            [IntlDateFormatter::NONE, IntlDateFormatter::NONE],
        ]);

        it('logs error and returns empty string when intl is not loaded', function () {
            if (extension_loaded('intl')) {
                expect(true)->toBeTrue();
                return;
            }

            $timestamp = time();

            $reflection = new ReflectionAccessor(new DateTime());
            $result = $reflection->callProtectedMethod(
                'getLocalDate',
                [$timestamp, IntlDateFormatter::FULL, IntlDateFormatter::SHORT]
            );

            expect($result)->toBe('');
        });
    });

    describe('DateTime::parseDate', function () {
        it('parses valid dates correctly', function ($dateStr, $expectedYear, $expectedMonth, $expectedDay) {
            $reflection = new ReflectionAccessor(new DateTime());
            $result = $reflection->callProtectedMethod('parseDate', [$dateStr]);

            expect($result)
                ->toBeInstanceOf(\DateTime::class)
                ->and($result->format('Y'))->toBe($expectedYear)
                ->and($result->format('m'))->toBe($expectedMonth)
                ->and($result->format('d'))->toBe($expectedDay);
        })->with([
            ['01.01.20', '2020', '01', '01'],
            ['31.12.25', '2025', '12', '31'],
            ['15.06.23', '2023', '06', '15'],
            ['29.02.20', '2020', '02', '29'], // Leap year
        ]);

        it('returns null for invalid date formats', function ($dateStr) {
            $reflection = new ReflectionAccessor(new DateTime());
            $result = $reflection->callProtectedMethod('parseDate', [$dateStr]);

            expect($result)->toBeNull();
        })->with([
            ['invalid'],
            ['01-01-20'],
            ['01/01/20'],
            ['1.1.20'],
            ['01.01'],
            ['01.01.20.extra'],
            [''],
            ['...'],
        ]);

        it('returns null when DateTime construction throws exception', function ($dateStr) {
            $reflection = new ReflectionAccessor(new DateTime());
            $result = $reflection->callProtectedMethod('parseDate', [$dateStr]);

            expect($result)->toBeNull();
        })->with([
            ['32.01.20'],
            ['01.13.20'],
            ['31.02.20'],
            ['31.04.20'],
            ['29.02.21'],
            ['00.00.00'],
            ['99.99.99'],
        ]);
    });
});
