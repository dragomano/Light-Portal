<?php

/**
 * SimpleMenu.php
 *
 * @package SimpleMenu (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 17.01.24
 */

namespace Bugo\LightPortal\Addons\SimpleMenu;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Utils\{Config, Lang, Utils};

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class SimpleMenu extends Plugin
{
	public string $icon = 'far fa-list-alt';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_menu')
			return;

		$params['items'] = '';
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_menu')
			return;

		$data = $this->request()->only(['item_name', 'item_link']);

		$items = [];
		if ($data && isset($data['item_name']) && isset($data['item_link'])) {
			foreach ($data['item_name'] as $key => $item) {
				if (empty($link = $data['item_link'][$key]))
					continue;

				$items[] = [
					'name' => $item,
					'link' => $link
				];
			}

			$this->request()->put('items', json_encode($items, JSON_UNESCAPED_UNICODE));
		}

		$params['items'] = FILTER_DEFAULT;
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_menu')
			return;

		CustomField::make('items', Lang::$txt['lp_simple_menu']['items'])
			->setTab('content')
			->setValue($this->getFromTemplate('simple_menu_items'));
	}

	public function getData(string $items): array
	{
		if (empty($items))
			return [];

		$html = '
		<ul class="dropmenu">';

		$items = Utils::jsonDecode($items, true);

		foreach ($items as $item) {
			[$title, $link] = [$item['name'], $item['link']];

			$ext = true;
			if (! str_starts_with($link, 'http')) {
				$active = $link == Utils::$context['current_action'];
				$link   = Config::$scripturl . '?action=' . $link;
				$ext    = false;
			}

			$html .= '
			<li style="width: 100%">
				<a' . (empty($active) ? '' : ' class="active"') . ' href="' . $link . '"' . (empty($ext) ? '' : ' target="_blank" rel="noopener"') . '>
					<span>' . $title . '</span>
				</a>
			</li>';
		}

		$html .= '
		</ul>';

		return ['content' => $html];
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'simple_menu' || empty($parameters['items']))
			return;

		$simple_menu_html = $this->cache('simple_menu_addon_b' . $data->block_id)
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData', $parameters['items']);

		if (empty($simple_menu_html))
			return;

		echo $simple_menu_html['content'] ?? '';
	}
}
