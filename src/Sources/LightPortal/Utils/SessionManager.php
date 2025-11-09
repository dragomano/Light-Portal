<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Utils;

use Bugo\Compat\User;
use LightPortal\Enums\Status;
use LightPortal\Repositories\BlockRepositoryInterface;
use LightPortal\Repositories\CategoryRepositoryInterface;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\Utils\Traits\HasSession;

if (! defined('SMF'))
	die('No direct access...');

final readonly class SessionManager
{
	use HasSession;

	public function __construct(
		private BlockRepositoryInterface $blockRepository,
		private PageRepositoryInterface $pageRepository,
		private CategoryRepositoryInterface $categoryRepository,
		private TagRepositoryInterface $tagRepository
	) {}

	public function __invoke(): array
	{
		$result = [];

		foreach ($this->getCountConfig() as $key => $config) {
			$result[$key] = $this->getCount($key);
		}

		return $result;
	}

	private function getCountConfig(): array
	{
		$userKey = User::$me->allowedTo('light_portal_manage_pages_any') ? '' : ('_u' . User::$me->id);

		$activePagesConditions = [
			'status'     => Status::ACTIVE->value,
			'deleted_at' => 0,
		];

		if (! User::$me->allowedTo('light_portal_manage_pages_any')) {
			$activePagesConditions['author_id'] = User::$me->id;
		}

		return [
			'active_blocks' => [
				'repository' => $this->blockRepository,
				'conditions' => ['status' => Status::ACTIVE->value]
			],
			'active_pages' => [
				'cache_key' => 'active_pages' . $userKey,
				'repository' => $this->pageRepository,
				'conditions' => $activePagesConditions
			],
			'my_pages' => [
				'cache_key' => 'my_pages' . $userKey,
				'repository' => $this->pageRepository,
				'conditions' => [
					'author_id'  => User::$me->id,
					'deleted_at' => 0,
				]
			],
			'unapproved_pages' => [
				'repository' => $this->pageRepository,
				'conditions' => [
					'status'     => Status::UNAPPROVED->value,
					'deleted_at' => 0,
				]
			],
			'deleted_pages' => [
				'repository' => $this->pageRepository,
				'conditions' => ['deleted_at != ?' => 0]
			],
			'active_categories' => [
				'repository' => $this->categoryRepository,
				'conditions' => ['status' => Status::ACTIVE->value]
			],
			'active_tags' => [
				'repository' => $this->tagRepository,
				'conditions' => ['status' => Status::ACTIVE->value]
			],
		];
	}

	private function getCount(string $type): int
	{
		$config = $this->getCountConfig()[$type];

		$cacheKey = $config['cache_key'] ?? $type;

		return $this->getCachedCount($cacheKey, $config['repository'], $config['conditions']);
	}

	private function getCachedCount(string $cacheKey, object $repository, array|callable $conditions = []): int
	{
		if ($this->session('lp')->get($cacheKey) === null) {
			$whereConditions = is_callable($conditions) ? $conditions() : $conditions;

			$count = $repository->getTotalCount('', $whereConditions);
			$this->session('lp')->put($cacheKey, $count);
		}

		return $this->session('lp')->get($cacheKey) ?? 0;
	}
}
