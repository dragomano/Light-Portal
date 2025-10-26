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

namespace LightPortal\Validators;

use Bugo\Compat\Utils;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;

class BlockValidator extends AbstractValidator
{
	protected array $filters = [
		'block_id'      => FILTER_VALIDATE_INT,
		'icon'          => FILTER_DEFAULT,
		'type'          => FILTER_DEFAULT,
		'description'   => FILTER_UNSAFE_RAW,
		'content'       => FILTER_UNSAFE_RAW,
		'placement'     => FILTER_DEFAULT,
		'priority'      => FILTER_VALIDATE_INT,
		'permissions'   => FILTER_VALIDATE_INT,
		'title_class'   => FILTER_DEFAULT,
		'content_class' => FILTER_DEFAULT,
	];

	protected array $customFilters = [
		'hide_header'   => FILTER_VALIDATE_BOOLEAN,
		'link_in_title' => FILTER_VALIDATE_URL,
	];

	public function __construct(PortalSqlInterface $sql, EventDispatcherInterface $dispatcher)
	{
		parent::__construct($sql, $dispatcher);

		$this->filters['areas'] = [
			'filter'  => FILTER_CALLBACK,
			'options' => $this->regexpOrEmpty('/' . LP_AREAS_PATTERN . '/'),
		];
	}

	protected function regexpOrEmpty(string $pattern): callable
	{
		return static function ($value) use ($pattern) {
			if ($value === null || $value === '') {
				return '';
			}

			return preg_match($pattern, $value) ? $value : '';
		};
	}

	protected function extendFilters(): void
	{
		$filters = [];

		$this->dispatcher->dispatch(
			PortalHook::validateBlockParams,
			[
				'baseParams' => &$this->customFilters,
				'params'     => &$filters,
				'type'       => Utils::$context['lp_current_block']['type'],
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
		$this->errors = [];

		$this->checkAreas();

		$this->dispatcher->dispatch(
			PortalHook::findBlockErrors,
			[
				'errors' => &$this->errors,
				'data'   => $this->filteredData,
			]
		);
	}

	protected function checkAreas(): void
	{
		$areasValue = $this->post()->get('areas');
		$validated  = $this->filteredData['areas'] ?? null;

		if (empty($areasValue)) {
			$this->errors[] = 'no_areas';
		} elseif ($validated === false) {
			$this->errors[] = 'no_valid_areas';

			$this->filteredData['areas'] = $areasValue;
		}
	}
}
