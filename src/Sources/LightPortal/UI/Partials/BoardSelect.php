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

namespace LightPortal\UI\Partials;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use LightPortal\Utils\MessageIndex;

if (! defined('SMF'))
	die('No direct access...');

final class BoardSelect extends AbstractSelect
{
	public function getData(): array
	{
		$data = [];
		foreach ($this->params['data'] as $cat) {
			$options = [];
			foreach ($cat['boards'] as $id_board => $board) {
				$options[] = [
					'label' => $board['name'],
					'value' => $id_board,
				];
			}

			$data[] = [
				'label'   => $cat['name'],
				'options' => $options,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return [
			'id'       => 'lp_frontpage_boards',
			'multiple' => true,
			'wide'     => true,
			'hint'     => Lang::$txt['lp_frontpage_boards_select'],
			'data'     => MessageIndex::getBoardList(),
			'value'    => $this->normalizeValue(Config::$modSettings['lp_frontpage_boards'] ?? ''),
		];
	}
}
