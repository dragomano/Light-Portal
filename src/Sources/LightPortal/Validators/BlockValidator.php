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

use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\VarType;
use Bugo\LightPortal\Events\EventArgs;
use Bugo\LightPortal\Events\EventManagerFactory;

use function array_merge;

class BlockValidator extends AbstractValidator
{
	protected array $filters = [
		'block_id'      => FILTER_VALIDATE_INT,
		'icon'          => FILTER_DEFAULT,
		'type'          => FILTER_DEFAULT,
		'note'          => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'content'       => FILTER_UNSAFE_RAW,
		'placement'     => FILTER_DEFAULT,
		'priority'      => FILTER_VALIDATE_INT,
		'permissions'   => FILTER_VALIDATE_INT,
		'areas'         => FILTER_DEFAULT,
		'title_class'   => FILTER_DEFAULT,
		'content_class' => FILTER_DEFAULT,
		'options'       => [
			'flags'   => FILTER_REQUIRE_ARRAY,
			'options' => [
				'hide_header'      => FILTER_VALIDATE_BOOLEAN,
				'no_content_class' => FILTER_VALIDATE_BOOLEAN,
				'link_in_title'    => FILTER_VALIDATE_URL,
			]
		]
	];

	protected function extendFilters(): void
	{
		$params = [];

		app(EventManagerFactory::class)()->dispatch(
			PortalHook::validateBlockParams,
			new EventArgs(['params' => &$params, 'type' => Utils::$context['lp_current_block']['type']])
		);

		$this->filters['options']['options'] = array_merge($this->filters['options']['options'], $params);
	}

	protected function extendErrors(): void
	{
		$this->errors = [];

		if (empty($this->filteredData['areas'])) {
			$this->errors[] = 'no_areas';
		} else {
			if (empty(VarType::ARRAY->filter($this->filteredData['areas'], ['regexp' => '/' . LP_AREAS_PATTERN . '/']))) {
				$this->errors[] = 'no_valid_areas';
			}
		}

		app(EventManagerFactory::class)()->dispatch(
			PortalHook::findBlockErrors,
			new EventArgs(['errors' => &$this->errors, 'data' => $this->filteredData])
		);
	}
}
