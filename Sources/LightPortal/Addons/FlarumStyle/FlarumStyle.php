<?php

/**
 * FlarumStyle.php
 *
 * @package FlarumStyle (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 07.04.23
 */

namespace Bugo\LightPortal\Addons\FlarumStyle;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class FlarumStyle extends Plugin
{
	public string $type = 'frontpage';

	public function addSettings(array &$config_vars)
	{
		$config_vars['flarum_style'][] = ['check', 'disable_sidebar'];
	}

	public function frontCustomTemplate()
	{
		if (! in_array($this->modSettings['lp_frontpage_mode'], ['all_topics', 'chosen_topics', 'all_pages', 'chosen_pages']))
			return;

		$this->context['lp_need_lower_case'] = $this->isLowerCaseForDates();

		$this->setTemplate('show_articles_as_flarum_style');

		$this->prepareFantomBLock();
	}

	private function prepareFantomBLock()
	{
		if (! empty($this->context['lp_flarum_style_plugin']['disable_sidebar']))
			return;

		$this->context['is_portal'] = in_array($this->modSettings['lp_frontpage_mode'], ['all_pages', 'chosen_pages']);

		$this->context['lp_all_categories'] = $this->getCategories();

		ob_start();

		show_ffs_sidebar();

		$content = ob_get_clean();

		$this->context['lp_blocks']['left'][] = [
			'id'      => uniqid(),
			'type'    => 'flarum_style',
			'content' => $content
		];
	}

	private function getCategories(): array
	{
		if ($this->context['is_portal']) {
			$all_categories = $this->getEntityList('category');

			$categories = [
				[
					'name'   => $this->txt['lp_categories'],
					'boards' => []
				]
			];

			$current_id = $this->request()->has('sa') && $this->request('sa') === 'categories' ? (int) $this->request('id', 0) : false;

			foreach ($all_categories as $id => $cat) {
				$categories[0]['boards'][] = [
					'id'          => $id,
					'name'        => $cat['name'],
					'child_level' => 0,
					'selected'    => $current_id >= 0 && $current_id === $id
				];
			}

			return $categories;
		}

		$options = [
			'included_boards' => empty($this->modSettings['lp_frontpage_boards']) ? [] : explode(',', $this->modSettings['lp_frontpage_boards'])
		];

		return $this->getBoardList($options);
	}

	/**
	 * Check whether need to display dates in lowercase for the current language
	 *
	 * Проверяем, нужно ли для текущего языка отображать даты в нижнем регистре
	 */
	private function isLowerCaseForDates(): bool
	{
		return in_array($this->txt['lang_dictionary'], ['pl', 'es', 'ru', 'uk']);
	}
}
