<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Database\PortalSql;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\AbstractRepository;
use Tests\PortalTable;
use Tests\TestAdapterFactory;

class TestAbstractRepository extends AbstractRepository
{
    protected string $entity = 'category';

    public function __construct(PortalSql $sql, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($sql, $dispatcher);
    }

    public function getAll(
        int $start,
        int $limit,
        string $sort,
        string $filter = '',
        array $whereConditions = []
    ): array {
        return [];
    }

    public function getTotalCount(string $filter = '', array $whereConditions = []): int
    {
        return 0;
    }

    public function publicSaveTranslations(array $data, bool $replace = false): void
    {
        $this->saveTranslations($data, $replace);
    }

    public function publicSaveOptions(array $data, bool $replace = false): void
    {
        $this->saveOptions($data, $replace);
    }

    public function publicDeleteRelatedData(array $items): void
    {
        $this->deleteRelatedData($items);
    }

    public function publicExecuteInTransaction(callable $callback): int
    {
        return $this->executeInTransaction($callback);
    }
}

beforeEach(function () {
    Lang::$txt['guest_title'] = 'Guest';

    User::$me = new User(1);
    User::$me->language = 'english';

    Config::$language = 'english';

    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::CATEGORIES->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();
    $adapter->query(PortalTable::PARAMS->value)->execute();

    $this->sql = new PortalSql($adapter);

    $this->dispatcher = mock(EventDispatcherInterface::class);
    $this->dispatcher->shouldReceive('dispatch')->andReturnNull()->byDefault();

    $this->repository = new TestAbstractRepository($this->sql, $this->dispatcher);
});

it('saves translations', function () {
    $this->repository->publicSaveTranslations([
        'id' => 1,
        'title' => 'Title',
        'content' => 'Content',
        'description' => 'Desc',
    ]);

    $row = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT * FROM lp_translations WHERE item_id = ?', [1])
        ->current();

    expect($row['title'])->toBe('Title')
        ->and($row['content'])->toBe('Content')
        ->and($row['description'])->toBe('Desc')
        ->and($row['type'])->toBe('category');
});

it('saves options', function () {
    $this->repository->publicSaveOptions([
        'id' => 2,
        'options' => [
            'theme' => 'dark',
            'flags' => ['a', 'b'],
        ],
    ]);

    $rows = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT * FROM lp_params WHERE item_id = ? ORDER BY name', [2])
        ->toArray();

    expect($rows)->toHaveCount(2)
        ->and($rows[0]['name'])->toBe('flags')
        ->and($rows[0]['value'])->toBe('a,b')
        ->and($rows[1]['name'])->toBe('theme')
        ->and($rows[1]['value'])->toBe('dark');
});

it('deletes related data', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, title)
        VALUES (1, 'category', 'english', 'Title')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_params (item_id, type, name, value)
        VALUES (1, 'category', 'theme', 'dark')
    ")->execute();

    $this->repository->publicDeleteRelatedData([1]);

    $translations = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_translations')
        ->execute()
        ->current()['count'];
    $params = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_params')
        ->execute()
        ->current()['count'];

    expect($translations)->toBe(0)
        ->and($params)->toBe(0);
});

it('toggles status for categories', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_categories (category_id, parent_id, slug, icon, priority, status)
        VALUES
            (1, 0, 'one', '', 1, 1),
            (2, 0, 'two', '', 2, 0)
    ")->execute();

    $this->repository->toggleStatus([1, 2]);

    $rows = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT category_id, status FROM lp_categories ORDER BY category_id')
        ->execute();

    $rows = iterator_to_array($rows);

    expect($rows[0]['status'])->toBe(0)
        ->and($rows[1]['status'])->toBe(1);
});

it('executes callbacks within a transaction', function () {
    $result = $this->repository->publicExecuteInTransaction(fn() => 7);

    expect($result)->toBe(7);
});
