<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Database\PortalSql;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\Status;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\AbstractRepository;
use LightPortal\Repositories\BlockRepository;
use LightPortal\Utils\CacheInterface;
use LightPortal\Utils\SessionInterface;
use Tests\AppMockRegistry;
use Tests\PortalTable;
use Tests\TestAdapterFactory;

arch()
    ->expect(BlockRepository::class)
    ->toExtend(AbstractRepository::class);

beforeEach(function () {
    Lang::$txt['guest_title'] = 'Guest';
    Lang::$txt['lp_custom']['title'] = 'Custom';

    User::$me = new User(1);
    User::$me->language = 'english';

    Config::$language = 'english';

    $GLOBALS['context']['lp_block_placements'] = [
        'left' => 'Left',
        'right' => 'Right',
    ];

    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::BLOCKS->value)->execute();
    $adapter->query(PortalTable::PARAMS->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();

    $this->sql = new PortalSql($adapter);

    $this->dispatcher = mock(EventDispatcherInterface::class);
    $this->dispatcher->shouldReceive('dispatch')->andReturnNull()->byDefault();

    $this->repository = new BlockRepository($this->sql, $this->dispatcher);
});

it('returns grouped blocks for placements', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_blocks (
            block_id, icon, type, placement, priority, permissions, status, areas, title_class, content_class
        ) VALUES (1, '', 'custom', 'left', 1, 0, ?, 'all', '', '')
    ", [Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, description)
        VALUES (1, 'block', 'english', 'Block One', 'Block description')
    ")->execute();

    $result = $this->repository->getAll(0, 10, '');

    expect($result)->toHaveKey('left')
        ->and($result)->toHaveKey('right')
        ->and($result['left'])->toHaveKey(1)
        ->and($result['left'][1]['title'])->toBe('Block One');
});

it('returns list blocks with parameters', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_blocks (
            block_id, icon, type, placement, priority, permissions, status, areas, title_class, content_class
        ) VALUES (1, '', 'custom', 'left', 1, 0, ?, 'all,home', '', '')
    ", [Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES (1, 'block', 'english', 'Block One', 'Content', '')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_params (item_id, type, name, value)
        VALUES (1, 'block', 'css', 'value')
    ")->execute();

    $result = $this->repository->getAll(0, 10, '', 'list');

    expect($result)->toHaveKey(1)
        ->and($result[1]['areas'])->toBe(['all', 'home'])
        ->and($result[1]['parameters']['css'])->toBe('value');
});

it('returns block data with options', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_blocks (
            block_id, icon, type, placement, priority, permissions, status, areas, title_class, content_class
        ) VALUES (1, '', ?, 'left', 1, 0, ?, 'all', '', '')
    ", [ContentType::BBC->value, Status::ACTIVE->value]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title, content, description)
        VALUES (1, 'block', 'english', 'Block One', 'Test content', '')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_params (item_id, type, name, value)
        VALUES (1, 'block', 'theme', 'dark')
    ")->execute();

    $result = $this->repository->getData(1);

    expect($result)->toBeArray()
        ->and($result['id'])->toBe(1)
        ->and($result['content'])->toBe('Test content')
        ->and($result['options']['theme'])->toBe('dark');
});

it('returns block ids of the placement ordered by priority', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_blocks (
            block_id, icon, type, placement, priority, permissions, status, areas, title_class, content_class
        ) VALUES
            (1, '', 'custom', 'left', 3, 0, ?, 'all', '', ''),
            (2, '', 'custom', 'right', 1, 0, ?, 'all', '', ''),
            (3, '', 'custom', 'left', 1, 0, ?, 'all', '', '')
    ", [Status::ACTIVE->value, Status::ACTIVE->value, Status::ACTIVE->value]);

    expect($this->repository->getIdsByPlacement('left'))->toBe([3, 1])
        ->and($this->repository->getIdsByPlacement('right'))->toBe([2])
        ->and($this->repository->getIdsByPlacement(''))->toBe([]);
});

it('updates priority and placement', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_blocks (
            block_id, icon, type, placement, priority, permissions, status, areas, title_class, content_class
        ) VALUES
            (1, '', 'custom', 'left', 1, 0, ?, 'all', '', ''),
            (2, '', 'custom', 'left', 2, 0, ?, 'all', '', '')
    ", [Status::ACTIVE->value, Status::ACTIVE->value]);

    // The cached block list must be dropped, otherwise the new placement is invisible on the portal
    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->once();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $this->repository->updatePriority([
        2 => 1,
        1 => 2,
    ], 'right');

    $rows = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT block_id, priority, placement FROM lp_blocks ORDER BY block_id')
        ->execute();

    $rows = iterator_to_array($rows);

    expect($rows[0]['priority'])->toBe(2)
        ->and($rows[0]['placement'])->toBe('right')
        ->and($rows[1]['priority'])->toBe(1)
        ->and($rows[1]['placement'])->toBe('right');
});

it('renumbers the panel after a block is removed', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_blocks (
            block_id, icon, type, placement, priority, permissions, status, areas, title_class, content_class
        ) VALUES
            (1, '', 'custom', 'left', 0, 0, ?, 'all', '', ''),
            (2, '', 'custom', 'left', 1, 0, ?, 'all', '', ''),
            (3, '', 'custom', 'left', 2, 0, ?, 'all', '', '')
    ", [Status::ACTIVE->value, Status::ACTIVE->value, Status::ACTIVE->value]);

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->andReturnNull();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $sessionMock = mock(SessionInterface::class);
    $sessionMock->shouldReceive('withKey')->with('lp')->andReturnSelf();
    $sessionMock->shouldReceive('free')->with('active_blocks')->andReturnNull();
    AppMockRegistry::set(SessionInterface::class, $sessionMock);

    $this->repository->remove([2]);

    $rows = iterator_to_array($this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT block_id, priority FROM lp_blocks ORDER BY block_id')
        ->execute());

    expect(count($rows))->toBe(2)
        ->and($rows[0]['priority'])->toBe(0)
        ->and($rows[1]['priority'])->toBe(1);
});

it('renumbers every panel of the removed blocks', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_blocks (
            block_id, icon, type, placement, priority, permissions, status, areas, title_class, content_class
        ) VALUES
            (1, '', 'custom', 'left', 0, 0, ?, 'all', '', ''),
            (2, '', 'custom', 'left', 1, 0, ?, 'all', '', ''),
            (3, '', 'custom', 'top', 0, 0, ?, 'all', '', ''),
            (4, '', 'custom', 'top', 1, 0, ?, 'all', '', '')
    ", array_fill(0, 4, Status::ACTIVE->value));

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->andReturnNull();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    $sessionMock = mock(SessionInterface::class);
    $sessionMock->shouldReceive('withKey')->with('lp')->andReturnSelf();
    $sessionMock->shouldReceive('free')->with('active_blocks')->andReturnNull();
    AppMockRegistry::set(SessionInterface::class, $sessionMock);

    $this->repository->remove([1, 4]);

    $rows = iterator_to_array($this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT block_id, placement, priority FROM lp_blocks ORDER BY block_id')
        ->execute());

    expect(count($rows))->toBe(2)
        ->and($rows[0]['placement'])->toBe('left')
        ->and($rows[0]['priority'])->toBe(0)
        ->and($rows[1]['placement'])->toBe('top')
        ->and($rows[1]['priority'])->toBe(0);
});

it('appends the blocks hidden from the portal when the placement is given', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_blocks (
            block_id, icon, type, placement, priority, permissions, status, areas, title_class, content_class
        ) VALUES
            (1, '', 'custom', 'right', 0, 0, ?, 'all', '', ''),
            (2, '', 'custom', 'right', 1, 0, ?, 'all', '', ''),
            (3, '', 'custom', 'right', 2, 0, ?, 'all', '', '')
    ", [Status::ACTIVE->value, Status::ACTIVE->value, Status::ACTIVE->value]);

    $cacheMock = mock(CacheInterface::class);
    $cacheMock->shouldReceive('flush')->once();
    AppMockRegistry::set(CacheInterface::class, $cacheMock);

    // The portal renders blocks 1 and 2 only, the hidden block 3 stays right after them
    $this->repository->updatePriority([1, 2], 'right');

    $rows = iterator_to_array($this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT block_id, priority FROM lp_blocks ORDER BY block_id')
        ->execute());

    expect($rows[0]['priority'])->toBe(0)
        ->and($rows[1]['priority'])->toBe(1)
        ->and($rows[2]['priority'])->toBe(2);
});
