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

namespace Bugo\LightPortal\Validators;

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Utils\Language;
use Laminas\Db\Sql\Expression;

class PageValidator extends AbstractValidator
{
	protected array $filters = [
		'page_id'     => FILTER_VALIDATE_INT,
		'category_id' => FILTER_VALIDATE_INT,
		'author_id'   => FILTER_VALIDATE_INT,
		'slug'        => [
			'filter'  => FILTER_VALIDATE_REGEXP,
			'options' => ['regexp' => '/' . LP_ALIAS_PATTERN . '/'],
		],
		'description' => FILTER_UNSAFE_RAW,
		'content'     => FILTER_UNSAFE_RAW,
		'type'        => FILTER_DEFAULT,
		'entry_type'  => FILTER_DEFAULT,
		'permissions' => FILTER_VALIDATE_INT,
		'status'      => FILTER_VALIDATE_INT,
		'date'        => FILTER_DEFAULT,
		'time'        => FILTER_DEFAULT,
		'tags'        => FILTER_DEFAULT,
	];

	protected array $customFilters = [
		'page_icon'            => FILTER_DEFAULT,
		'show_in_menu'         => FILTER_VALIDATE_BOOLEAN,
		'show_title'           => FILTER_VALIDATE_BOOLEAN,
		'show_author_and_date' => FILTER_VALIDATE_BOOLEAN,
		'show_related_pages'   => FILTER_VALIDATE_BOOLEAN,
		'allow_comments'       => FILTER_VALIDATE_BOOLEAN,
	];

	protected function extendFilters(): void
	{
		$filters = [];

		$this->events()->dispatch(
			PortalHook::validatePageParams,
			[
				'params' => &$filters,
				'type'   => Utils::$context['lp_current_page']['type'],
			]
		);

		$this->customFilters = array_merge($this->customFilters, $filters);
	}

	protected function modifyData(): void
	{
		$this->filteredData['options'] = filter_var_array(
			$this->post()->only(array_keys($this->customFilters)), $this->customFilters
		);
	}

	protected function extendErrors(): void
	{
		if (Language::isDefault() && empty($this->filteredData['content'])) {
			$this->errors[] = 'no_content';
		}

		$this->checkSlug();

		$this->events()->dispatch(
			PortalHook::findPageErrors,
			[
				'errors' => &$this->errors,
				'data'   => $this->filteredData,
			]
		);
	}

	protected function isUnique(): bool
	{
		$select = $this->sql->select('lp_pages')
			->columns(['count' => new Expression('COUNT(page_id)')])
			->where([
				'slug = ?' => $this->filteredData['slug'],
				'page_id != ?' => $this->filteredData['page_id'],
			]);

		$result = $this->sql->execute($select)->current();

		return $result['count'] == 0;
	}
}
