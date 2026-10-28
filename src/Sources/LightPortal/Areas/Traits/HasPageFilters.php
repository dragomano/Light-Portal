<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Areas\Traits;

use Bugo\Compat\Utils;
use Laminas\Db\Sql\Predicate\Expression;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Status;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

trait HasPageFilters
{
	private const MAX_SEARCH_LENGTH = 100;

	private array $params = [];

	private array $pageParams = [];

	private ?string $searchString = null;

	private ?int $userId = null;

	private bool $isModerate = false;

	private bool $isDeleted = false;

	private ?string $entryType = null;

	private function loadParamsFromRequest(): void
	{
		$encodedParams = $this->request()->get('params');
		if ($encodedParams) {
			$decodedParams = Utils::$smcFunc['json_decode'](base64_decode($encodedParams), true);
			if (is_array($decodedParams)) {
				$this->pageParams = $decodedParams;
			}
		}

		$this->userId     = $this->request()->get('u') ? (int) $this->request()->get('u') : ($this->pageParams['u'] ?? null);
		$this->isModerate = $this->request()->has('moderate') || ($this->pageParams['moderate'] ?? false);
		$this->isDeleted  = $this->request()->has('deleted') || ($this->pageParams['deleted'] ?? false);
		$this->entryType  = $this->request()->get('type') ?: ($this->pageParams['type'] ?? null);
	}

	private function calculateParams(?string $search = null): void
	{
		$this->searchString = $this->limitSearchString($search ?? $this->searchString ?? $this->getRequestedSearch());

		$search = Utils::$smcFunc['strtolower']($this->searchString);

		// The term is stored as is: the templates escape it when they render it
		$searchParams = [
			'string'   => $this->searchString,
			'u'        => $this->userId ? (int) $this->userId : null,
			'moderate' => $this->isModerate,
			'deleted'  => $this->isDeleted,
			'type'     => $this->entryType,
		];

		Utils::$context['search_params'] = empty(array_filter($searchParams))
			? '' : base64_encode((string) Utils::$smcFunc['json_encode']($searchParams));

		Utils::$context['search'] = ['string' => $this->searchString];

		$this->params = ['', $this->buildWhereConditions(
			$this->userId,
			$this->isModerate,
			$this->isDeleted,
			$search
		)];
	}

	/**
	 * The term is carried by the link that is followed, so it survives sorting and paging
	 */
	private function getRequestedSearch(): string
	{
		$requested = $this->request()->get('search');

		return $requested !== null
			? (string) $requested
			: (string) ($this->pageParams['string'] ?? '');
	}

	/**
	 * A term longer than that only makes the LIKE scan heavier
	 */
	private function limitSearchString(string $search): string
	{
		return mb_substr(trim($search), 0, self::MAX_SEARCH_LENGTH);
	}

	private function buildWhereConditions(
		?int $userId,
		bool $isModerate,
		bool $isDeleted,
		string $search = ''
	): array {
		$whereConditions = [];

		if (! empty($search)) {
			// The parentheses keep the OR inside this condition: the view conditions are joined with AND.
			// The * and ? characters are the wildcards here, while % and _ are ordinary characters
			$pattern = Str::likePattern($search);

			$whereConditions[] = new Expression(
				"(LOWER(p.slug) LIKE ? ESCAPE '!' OR LOWER(t.title) LIKE ? ESCAPE '!')",
				[$pattern, $pattern]
			);
		}

		if ($userId) {
			$whereConditions['p.author_id'] = (int) $userId;
			$whereConditions['p.deleted_at'] = 0;
		} elseif ($isModerate) {
			$whereConditions['p.status'] = Status::UNAPPROVED->value;
			$whereConditions['p.deleted_at'] = 0;
		} elseif ($isDeleted) {
			$whereConditions[] = new Expression('p.deleted_at <> 0');
		} else {
			$whereConditions['p.status'] = [Status::INACTIVE->value, Status::ACTIVE->value];
			$whereConditions['p.deleted_at'] = 0;
		}

		$whereConditions['p.entry_type'] = $this->entryType ?? EntryType::DEFAULT->value;

		return $whereConditions;
	}
}
