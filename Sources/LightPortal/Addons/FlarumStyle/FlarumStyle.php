<?php

/**
 * FlarumStyle.php
 *
 * @package FlarumStyle (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 31.12.21
 */

namespace Bugo\LightPortal\Addons\FlarumStyle;

use Bugo\LightPortal\Addons\Plugin;

class FlarumStyle extends Plugin
{
	public string $type = 'frontpage';

	public function frontCustomTemplate()
	{
		if (! in_array($this->modSettings['lp_frontpage_mode'], ['all_topics', 'chosen_topics', 'all_pages', 'chosen_pages']))
			return;

		$this->context['is_portal'] = in_array($this->modSettings['lp_frontpage_mode'], ['all_pages', 'chosen_pages']);

		$this->context['lp_all_categories'] = $this->getCategories();

		$this->context['lp_need_lower_case'] = $this->isLowerCaseForDates();

		$this->loadTemplate('show_articles_as_flarum_style');

		$this->prepareFantomBLock();
	}

	private function prepareFantomBLock()
	{
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
			$all_categories = $this->getAllCategories();

			$categories = [
				[
					'name'   => $this->txt['lp_categories'],
					'boards' => []
				]
			];

			foreach ($all_categories as $id => $cat) {
				$categories[0]['boards'][] = [
					'id'          => $id,
					'name'        => $cat['name'],
					'child_level' => 0,
					'selected'    => false
				];
			}

			return $categories;
		}

		$this->require('Subs-MessageIndex');

		$boardListOptions = [
			'ignore_boards'   => true,
			'use_permissions' => true,
			'not_redirection' => true,
			'included_boards' => empty($this->modSettings['lp_frontpage_boards']) ? [] : explode(',', $this->modSettings['lp_frontpage_boards'])
		];

		return getBoardList($boardListOptions);
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
