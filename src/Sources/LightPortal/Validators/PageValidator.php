<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Validators;

use Bugo\Compat\Db;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\VarType;
use Bugo\LightPortal\Events\EventArgs;
use Bugo\LightPortal\Events\EventManagerFactory;

use function array_merge;

class PageValidator extends AbstractValidator
{
	protected array $filters = [
		'page_id'     => FILTER_VALIDATE_INT,
		'category_id' => FILTER_VALIDATE_INT,
		'author_id'   => FILTER_VALIDATE_INT,
		'slug'        => FILTER_DEFAULT,
		'description' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'content'     => FILTER_UNSAFE_RAW,
		'type'        => FILTER_DEFAULT,
		'entry_type'  => FILTER_DEFAULT,
		'permissions' => FILTER_VALIDATE_INT,
		'status'      => FILTER_VALIDATE_INT,
		'date'        => FILTER_DEFAULT,
		'time'        => FILTER_DEFAULT,
		'tags'        => FILTER_DEFAULT,
		'options'     => [
			'flags'   => FILTER_REQUIRE_ARRAY,
			'options' => [
				'page_icon'            => FILTER_DEFAULT,
				'show_in_menu'         => FILTER_VALIDATE_BOOLEAN,
				'show_title'           => FILTER_VALIDATE_BOOLEAN,
				'show_author_and_date' => FILTER_VALIDATE_BOOLEAN,
				'show_related_pages'   => FILTER_VALIDATE_BOOLEAN,
				'allow_comments'       => FILTER_VALIDATE_BOOLEAN,
			]
		]
	];

	protected function extendFilters(): void
	{
		$params = [];

		app(EventManagerFactory::class)()->dispatch(
			PortalHook::validatePageParams,
			new EventArgs(['params' => &$params, 'type' => Utils::$context['lp_current_page']['type']])
		);

		$this->filters['options']['options'] = array_merge($this->filters['options']['options'], $params);
	}

	protected function extendErrors(): void
	{
		if (empty($this->filteredData['content'])) {
			$this->errors[] = 'no_content';
		}

		if (empty($this->filteredData['slug'])) {
			$this->errors[] = 'no_slug';
		} else {
			if (empty(VarType::ARRAY->filter($this->filteredData['slug'], ['regexp' => '/' . LP_ALIAS_PATTERN . '/']))) {
				$this->errors[] = 'no_valid_slug';
			}

			if (! $this->isUnique($this->filteredData)) {
				$this->errors[] = 'no_unique_slug';
			}
		}

		app(EventManagerFactory::class)()->dispatch(
			PortalHook::findPageErrors,
			new EventArgs(['errors' => &$this->errors, 'data' => $this->filteredData])
		);
	}

	private function isUnique(array $data): bool
	{
		$result = Db::$db->query('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE slug = {string:slug}
				AND page_id != {int:item}',
			[
				'slug' => $data['slug'],
				'item' => $data['page_id'],
			]
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return $count == 0;
	}
}
