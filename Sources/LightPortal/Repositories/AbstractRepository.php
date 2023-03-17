<?php declare(strict_types=1);

/**
 * AbstractRepository.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractRepository
{
	use Helper;

	protected string $entity;

	protected function prepareBbcContent(array &$entity): void
	{
		if ($entity['type'] !== 'bbc')
			return;

		$entity['content'] = $this->smcFunc['htmlspecialchars']($entity['content'], ENT_QUOTES);

		$this->preparseCode($entity['content']);
	}

	protected function saveTitles(int $item, string $method = ''): void
	{
		if (empty($this->context['lp_' . $this->entity]['title']))
			return;

		$titles = [];
		foreach ($this->context['lp_' . $this->entity]['title'] as $lang => $title) {
			$titles[] = [
				'item_id' => $item,
				'type'    => $this->entity,
				'lang'    => $lang,
				'title'   => $title,
			];
		}

		if (empty($titles))
			return;

		$this->smcFunc['db_insert']($method,
			'{db_prefix}lp_titles',
			[
				'item_id' => 'int',
				'type'    => 'string',
				'lang'    => 'string',
				'title'   => 'string',
			],
			$titles,
			['item_id', 'type', 'lang']
		);

		$this->context['lp_num_queries']++;
	}

	protected function saveOptions(int $item, string $method = ''): void
	{
		if (empty($this->context['lp_' . $this->entity]['options']))
			return;

		$params = [];
		foreach ($this->context['lp_' . $this->entity]['options'] as $param_name => $value) {
			$value = is_array($value) ? implode(',', $value) : $value;

			$params[] = [
				'item_id' => $item,
				'type'    => $this->entity,
				'name'    => $param_name,
				'value'   => $value,
			];
		}

		if (empty($params))
			return;

		$this->smcFunc['db_insert']($method,
			'{db_prefix}lp_params',
			[
				'item_id' => 'int',
				'type'    => 'string',
				'name'    => 'string',
				'value'   => 'string',
			],
			$params,
			['item_id', 'type', 'name']
		);

		$this->context['lp_num_queries']++;
	}
}
