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
 * @version 16.12.21
 */

namespace Bugo\LightPortal\Addons\FlarumStyle;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class FlarumStyle extends Plugin
{
	public string $type = 'frontpage';

	public function frontCustomTemplate()
	{
		global $modSettings, $context;

		if (! in_array($modSettings['lp_frontpage_mode'], ['all_topics', 'chosen_topics', 'all_pages', 'chosen_pages']))
			return;

		$context['is_portal'] = in_array($modSettings['lp_frontpage_mode'], ['all_pages', 'chosen_pages']);

		$context['lp_all_categories'] = $this->getCategories();

		$context['lp_need_lower_case'] = $this->isLowerCaseForDates();

		$this->loadTemplate();

		$this->prepareFantomBLock();

		$context['sub_template'] = 'show_articles_as_flarum_style';
	}

	private function prepareFantomBLock()
	{
		global $context;

		ob_start();

		show_ffs_sidebar();

		$content = ob_get_clean();

		$context['lp_blocks']['left'][] = [
			'id'      => uniqid(),
			'type'    => 'flarum_style',
			'content' => $content
		];
	}

	private function getCategories(): array
	{
		global $context, $txt, $modSettings;

		if ($context['is_portal']) {
			$all_categories = Helper::getAllCategories();

			$categories = array(
				array(
					'name'   => $txt['lp_categories'],
					'boards' => []
				)
			);

			foreach ($all_categories as $id => $cat) {
				$categories[0]['boards'][] = array(
					'id'          => $id,
					'name'        => $cat['name'],
					'child_level' => 0,
					'selected'    => false
				);
			}

			return $categories;
		}

		Helper::require('Subs-MessageIndex');

		$boardListOptions = array(
			'ignore_boards'   => true,
			'use_permissions' => true,
			'not_redirection' => true,
			'included_boards' => empty($modSettings['lp_frontpage_boards']) ? [] : explode(',', $modSettings['lp_frontpage_boards'])
		);

		return getBoardList($boardListOptions);
	}

	/**
	 * Check whether need to display dates in lowercase for the current language
	 *
	 * Проверяем, нужно ли для текущего языка отображать даты в нижнем регистре
	 */
	private function isLowerCaseForDates(): bool
	{
		global $txt;

		return in_array($txt['lang_dictionary'], ['pl', 'es', 'ru', 'uk']);
	}
}
