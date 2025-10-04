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

namespace Bugo\LightPortal\UI\Partials;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\TitleClass;

if (! defined('SMF'))
	die('No direct access...');

final class TitleClassSelect extends AbstractSelect
{
	protected string $template = 'preview_select';

	public function getData(): array
	{
		$data = [];
		foreach ($this->params['data'] as $key => $template) {
			$data[] = [
				'label' => sprintf($template, empty($key) ? Lang::$txt['no'] : $key),
				'value' => $key,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return [
			'id'       => 'title_class',
			'multiple' => false,
			'wide'     => false,
			'hint'     => Lang::$txt['no'],
			'data'     => TitleClass::values() ?? [],
			'value'    => Utils::$context['lp_block']['title_class'] ?? '',
		];
	}
}
