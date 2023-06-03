<?php

/**
 * SimpleMenu.php
 *
 * @package SimpleMenu (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 18.12.22
 */

namespace Bugo\LightPortal\Addons\SimpleMenu;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class SimpleMenu extends Plugin
{
	public string $icon = 'far fa-list-alt';

	public function blockOptions(array &$options)
	{
		$options['simple_menu']['parameters']['items'] = '';
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'simple_menu')
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

		$parameters['items'] = FILTER_DEFAULT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'simple_menu')
			return;

		$this->setTemplate();

		$this->addInlineJavaScript('
		function handleItems() {
			return {
				items: ' . ($this->context['lp_block']['options']['parameters']['items'] ?: '[]') . ',
				addNewItem() {
					this.items.push({
						name: "",
						link: ""
					})
				},
				removeItem(index) {
					this.items.splice(index, 1)
				}
			}
		}');

		$this->context['posting_fields']['items']['label']['html'] = $this->txt['lp_simple_menu']['items'];
		$this->context['posting_fields']['items']['input']['html'] = simple_menu_items();
		$this->context['posting_fields']['items']['input']['tab']  = 'content';
	}

	public function getData(string $items): array
	{
		if (empty($items))
			return [];

		$html = '
		<ul class="dropmenu">';

		$items = $this->jsonDecode($items, true, false);

		foreach ($items as $item) {
			[$title, $link] = [$item['name'], $item['link']];

			$ext = true;
			if (! str_starts_with($link, 'http')) {
				$active = $link == $this->context['current_action'];
				$link   = $this->scripturl . '?action=' . $link;
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

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'simple_menu' || empty($parameters['items']))
			return;

		$simple_menu_html = $this->cache('simple_menu_addon_b' . $block_id)
			->setLifeTime($cache_time)
			->setFallback(self::class, 'getData', $parameters['items']);

		if (empty($simple_menu_html))
			return;

		echo $simple_menu_html['content'] ?? '';
	}
}