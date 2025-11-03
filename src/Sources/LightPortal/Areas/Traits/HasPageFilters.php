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

namespace LightPortal\Areas\Traits;

use Bugo\Compat\Utils;
use Laminas\Db\Sql\Predicate\Expression;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Status;

trait HasPageFilters
{
	private array $params = [];

	private ?int $userId = null;

	private bool $isModerate = false;

	private bool $isDeleted = false;

	private ?string $entryType = null;

	private function loadParamsFromRequest(): void
	{
		$pageParams = [];
		$encodedParams = $this->request()->get('params');
		if ($encodedParams) {
			$decodedParams = Utils::$smcFunc['json_decode'](base64_decode($encodedParams), true);
			if (is_array($decodedParams)) {
				$pageParams = $decodedParams;
			}
		}

		$this->userId     = $this->request()->get('u') ? (int) $this->request()->get('u') : ($pageParams['u'] ?? null);
		$this->isModerate = $this->request()->has('moderate') || ($pageParams['moderate'] ?? false);
		$this->isDeleted  = $this->request()->has('deleted') || ($pageParams['deleted'] ?? false);
		$this->entryType  = $this->request()->get('type') ?: ($pageParams['type'] ?? null);
	}

	private function calculateParams(): void
	{
		$searchString = trim((string) $this->request()->get('search'));

		$search = Utils::$smcFunc['strtolower']($searchString);

		$searchParams = [
			'string'   => Utils::htmlspecialchars($searchString),
			'u'        => $this->userId ? (int) $this->userId : null,
			'moderate' => $this->isModerate,
			'deleted'  => $this->isDeleted,
			'type'     => $this->entryType,
		];

		Utils::$context['search_params'] = empty(array_filter($searchParams))
			? '' : base64_encode((string) Utils::$smcFunc['json_encode']($searchParams));

		Utils::$context['search'] = ['string' => $searchParams['string']];

		$whereConditions = [];
		if (! empty($search)) {
			$whereConditions[] = new Expression(
				'LOWER(p.slug) LIKE ? OR LOWER(t.title) LIKE ?',
				['%' . $search . '%', '%' . $search . '%']
			);
		}

		if ($this->userId) {
			$whereConditions['p.author_id'] = (int) $this->userId;
			$whereConditions['p.deleted_at'] = 0;
		} elseif ($this->isModerate) {
			$whereConditions['p.status'] = Status::UNAPPROVED->value;
			$whereConditions['p.deleted_at'] = 0;
		} elseif ($this->isDeleted) {
			$whereConditions[] = new Expression('p.deleted_at <> 0');
		} else {
			$whereConditions['p.status'] = [Status::INACTIVE->value, Status::ACTIVE->value];
			$whereConditions['p.deleted_at'] = 0;
		}

		$whereConditions['p.entry_type'] = $this->entryType ?? EntryType::DEFAULT->name();

		$this->params = ['', $whereConditions];
	}
}
