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
		'areas'         => [
			'filter'  => FILTER_VALIDATE_REGEXP,
			'options' => ['regexp' => '/' . LP_AREAS_PATTERN . '/'],
		],
		'title_class'   => FILTER_DEFAULT,
		'content_class' => FILTER_DEFAULT,
	];

	protected array $customFilters = [
		'hide_header'   => FILTER_VALIDATE_BOOLEAN,
		'link_in_title' => FILTER_VALIDATE_URL,
	];

	protected function extendFilters(): void
	{
		$filters = [];

		$this->events()->dispatch(
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

		$this->events()->dispatch(
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
		$validatedAreas = $this->filteredData['areas'] ?? null;

		$isEmptyAreas = empty($areasValue);
		$isInvalidAreas = ! $isEmptyAreas && $validatedAreas === false;

		if ($isEmptyAreas) {
			$this->errors[] = 'no_areas';
		}

		if ($isInvalidAreas) {
			$this->errors[] = 'no_valid_areas';
			$this->filteredData['areas'] = $areasValue;
		}
	}
}
