<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\PortalResultInterface;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\AbstractIndexRepository;
use LightPortal\Repositories\CategoryIndexRepository;
use LightPortal\Database\PortalTransactionInterface;

arch()
    ->expect(CategoryIndexRepository::class)
    ->toExtend(AbstractIndexRepository::class);

beforeEach(function () {
    Lang::$txt['guest_title'] = 'Guest';
    Lang::$txt['lp_no_category'] = 'No category';

    User::$me = new User(1);
    User::$me->language = 'english';
    User::$me->groups = [];
    User::$me->is_admin = false;
    User::$me->is_guest = false;

    Config::$language = 'english';

    $selectMock = mock(PortalSelect::class);
    $selectMock->shouldReceive('from')->andReturnSelf();
    $selectMock->shouldReceive('columns')->andReturnSelf();
    $selectMock->shouldReceive('join')->andReturnSelf();
    $selectMock->shouldReceive('where')->andReturnSelf();
    $selectMock->shouldReceive('group')->andReturnSelf();
    $selectMock->shouldReceive('order')->andReturnSelf();
    $selectMock->shouldReceive('limit')->andReturnSelf();
    $selectMock->shouldReceive('offset')->andReturnSelf();

    $transaction = mock(PortalTransactionInterface::class);

    $this->sql = mock(PortalSqlInterface::class);
    $this->sql->shouldReceive('select')->andReturn($selectMock);
    $this->sql->shouldReceive('getPrefix')->andReturn('');
    $this->sql->shouldReceive('getTransaction')->andReturn($transaction);

    $this->dispatcher = mock(EventDispatcherInterface::class);
    $this->dispatcher->shouldReceive('dispatch')->andReturnNull()->byDefault();

    $this->repository = new CategoryIndexRepository($this->sql, $this->dispatcher);

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

it('returns category list including uncategorized', function () {
    $rows = [
        [
            'category_id' => 1,
            'slug' => 'news',
            'icon' => '',
            'priority' => 1,
            'frequency' => 1,
            'title' => 'News',
            'description' => 'News category',
        ],
        [
            'category_id' => 0,
            'slug' => 'uncategorized',
            'icon' => '',
            'priority' => 0,
            'frequency' => 1,
            'title' => 'No category',
            'description' => '',
        ],
    ];

    $this->sql->shouldReceive('execute')->andReturn(($this->makeResult)($rows));

    $result = $this->repository->getAll(0, 10, 'priority DESC');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey(1)
        ->and($result[1]['slug'])->toBe('news')
        ->and($result[1]['num_pages'])->toBe(1)
        ->and($result[1]['link'])->toContain(';sa=categories;id=1')
        ->and($result[1]['title'])->toBe('News')
        ->and($result)->toHaveKey(0)
        ->and($result[0]['slug'])->toBe('uncategorized')
        ->and($result[0]['title'])->toBe('No category');
});

it('returns total count for categories including uncategorized', function () {
    $this->sql->shouldReceive('execute')->andReturn(($this->makeResult)([
        ['count' => 2],
    ]));

    $count = $this->repository->getTotalCount();

    expect($count)->toBe(2);
});
