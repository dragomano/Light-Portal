<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Laminas\Db\Sql\Where;
use LightPortal\Database\PortalSql;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\AbstractIndexRepository;
use Tests\TestAdapterFactory;

class TestIndexRepository extends AbstractIndexRepository
{
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

    public function commonPageWhere(): Where
    {
        return $this->getCommonPageWhere();
    }
}

beforeEach(function () {
    Lang::$txt['guest_title'] = 'Guest';

    User::$me = new User(1);
    User::$me->language = 'english';
    User::$me->is_admin = true;

    Config::$language = 'english';

    $adapter = TestAdapterFactory::create();
    $this->sql = new PortalSql($adapter);

    $this->dispatcher = mock(EventDispatcherInterface::class);
    $this->dispatcher->shouldReceive('dispatch')->andReturnNull()->byDefault();

    $this->repository = new TestIndexRepository($this->sql, $this->dispatcher);
});

it('builds common page where conditions', function () {
    $where = $this->repository->commonPageWhere();
    $expressionData = $where->getExpressionData();
    $sql = implode(' ', array_map(fn($part) => $part[0], $expressionData));

    expect($sql)->toContain('= %s')
        ->and($sql)->toContain('<= %s')
        ->and($sql)->toContain('IN (%s');

    $params = [];
    foreach ($expressionData as $part) {
        $values = $part[1];
        if (is_array($values)) {
            $params = array_merge($params, $values);
        } else {
            $params[] = $values;
        }
    }
    expect($params)->toContain(Status::ACTIVE->value)
        ->and($params)->toContain(0)
        ->and($params)->toContain(EntryType::DEFAULT->name())
        ->and($params)->toContain(Permission::ALL->value);
});
