<?php

/**
 * FlarumStyle
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\FlarumStyle;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class FlarumStyle extends Plugin
{
	/**
	 * @var string
	 */
	public $type = 'frontpage';

	/**
	 * Load custom template for frontpage topics
	 *
	 * Загружаем пользовательский шаблон для статей-тем
	 *
	 * @return void
	 */
	public function frontCustomTemplate()
	{
		global $modSettings, $context;

		if (!in_array($modSettings['lp_frontpage_mode'], ['all_topics', 'chosen_topics', 'all_pages', 'chosen_pages']))
			return;

		$context['is_portal'] = in_array($modSettings['lp_frontpage_mode'], ['all_pages', 'chosen_pages']);

		$context['lp_all_categories'] = $this->getCategories();

		$this->loadTemplate();

		$this->prepareFantomBLock();

		$context['sub_template'] = 'show_articles_as_flarum_style';
	}

	/**
	 * Make a fantom block
	 *
	 * Создаем фантомный блок
	 *
	 * @return void
	 */
	private function prepareFantomBLock()
	{
		global $context;

		ob_start();

		show_ffs_sidebar();

		$content = ob_get_clean();

		$context['lp_blocks']['left'][] = [
			'type'    => 'flarum_style',
			'content' => $content
		];
	}

	/**
	 * Get the list of categories with boards, considering the selected boards in the portal settings
	 *
	 * Получаем список всех категорий с разделами, учитывая отмеченные разделы в настройках портала
	 *
	 * @return array
	 */
	private function getCategories(): array
	{
		global $context, $txt, $modSettings;

		if ($context['is_portal']) {
			$all_categories = Helpers::getAllCategories();

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

		Helpers::require('Subs-MessageIndex');

		$boardListOptions = array(
			'ignore_boards'   => true,
			'use_permissions' => true,
			'not_redirection' => true,
			'included_boards' => !empty($modSettings['lp_frontpage_boards']) ? explode(',', $modSettings['lp_frontpage_boards']) : []
		);

		return getBoardList($boardListOptions);
	}
}
