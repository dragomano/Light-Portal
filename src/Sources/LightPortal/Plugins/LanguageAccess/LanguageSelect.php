<?php declare(strict_types=1);

/**
 * @package LanguageAccess (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace LightPortal\Plugins\LanguageAccess;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\UI\Partials\AbstractSelect;

if (! defined('LP_NAME'))
	die('No direct access...');

final class LanguageSelect extends AbstractSelect
{
	public function getData(): array
	{
		$data = [];
		foreach (Utils::$context['lp_languages'] as $lang) {
			$data[] = [
				'label' => $lang['name'],
				'value' => $lang['filename'],
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return [
			'id'       => 'allowed_languages',
			'multiple' => true,
			'hint'     => Lang::$txt['lp_language_access']['allowed_languages_subtext'],
			'value'    => $this->normalizeValue($this->params['allowed_languages']),
		];
	}
}
