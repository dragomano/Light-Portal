<?php

declare(strict_types=1);

use Bugo\Compat\Tasks\BackgroundTask;
use Bugo\Compat\Config;
use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\PortalResultInterface;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Enums\AlertAction;
use LightPortal\Tasks\Notifier;
use Tests\AppMockRegistry;
use Tests\ReflectionAccessor;

arch()
    ->expect(Notifier::class)
    ->toExtend(BackgroundTask::class);

describe('Notifier task', function () {
    beforeEach(function () {
        $this->sql = mock(PortalSqlInterface::class);
        AppMockRegistry::set(PortalSqlInterface::class, $this->sql);

        $this->task = new Notifier([
            'content_type' => 'new_page',
            'content_author_id' => 1,
            'sender_id' => 0,
            'sender_name' => 'Admin',
            'time' => time(),
            'content_id' => 1,
            'content_action' => 'create',
            'extra' => '{"content_link":"https://example.com"}',
        ]);

        $this->accessor = new ReflectionAccessor($this->task);

        $this->makeResult = function (array $rows): PortalResultInterface {
            $iterator = new ArrayIterator($rows);
            $result = mock(PortalResultInterface::class);

            $result->shouldReceive('current')->andReturnUsing(function () use ($iterator) {
                return $iterator->current();
            });
            $result->shouldReceive('valid')->andReturnUsing(function () use ($iterator) {
                return $iterator->valid();
            });
            $result->shouldReceive('next')->andReturnUsing(function () use ($iterator) {
                $iterator->next();
            });
            $result->shouldReceive('key')->andReturnUsing(function () use ($iterator) {
                return $iterator->key();
            });
            $result->shouldReceive('rewind')->andReturnUsing(function () use ($iterator) {
                $iterator->rewind();
            });

            return $result;
        };
    });

    afterEach(function () {
        AppMockRegistry::clear();
    });

    it('collects notify recipients by preference bits', function () {
        $prefs = [
            1 => [
                AlertAction::PAGE_COMMENT->name() => Notifier::RECEIVE_NOTIFY_ALERT | Notifier::RECEIVE_NOTIFY_EMAIL,
            ],
            2 => [
                AlertAction::PAGE_UNAPPROVED->name() => Notifier::RECEIVE_NOTIFY_ALERT,
            ],
        ];

        $notifies = $this->accessor->callMethod('getNotifies', [$prefs]);

        expect($notifies['alert'])->toContain(1, 2)
            ->and($notifies['email'])->toContain(1);
    });

    it('does nothing when no alerts to add', function () {
        $this->sql->shouldNotReceive('insert');

        $this->accessor->callMethod('addAlerts', [['alert' => []]]);

        expect(true)->toBeTrue();
    });

    it('groups member emails by language', function () {
        Config::$language = 'en';

        $select = new PortalSelect();

        $this->sql->shouldReceive('select')->andReturn($select);
        $this->sql->shouldReceive('execute')->with($select)->andReturn(($this->makeResult)([
            ['id_member' => 1, 'lngfile' => '', 'email_address' => 'a@example.com'],
            ['id_member' => 2, 'lngfile' => 'ru', 'email_address' => 'b@example.com'],
        ]));

        $emails = $this->accessor->callMethod('getMemberEmails', [[
            'email' => [1, 2],
        ]]);

        expect($emails['en'][1])->toBe('a@example.com')
            ->and($emails['ru'][2])->toBe('b@example.com');
    });
});
