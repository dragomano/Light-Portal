<?php

namespace Bugo\LightPortal\Addons\FlarumStyle;

use Bugo\LightPortal\Helpers;

/**
 * FlarumStyle
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.6
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class FlarumStyle
{
	/**
	 * @var string
	 */
	public $addon_type = 'frontpage';

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

		if (empty($modSettings['lp_frontpage_mode']) || !in_array($modSettings['lp_frontpage_mode'], ['all_topics', 'chosen_topics']))
			return;

		$context['lp_all_categories'] = $this->getListSelectedBoards();

		require_once(__DIR__ . '/Template.php');

		$this->prepareFantomBLock();

		$context['sub_template'] = 'show_topics_as_flarum_style';
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

		$context['lp_blocks']['left'][time()] = [
			'id'            => time(),
			'type'          => 'flarum_style',
			'content'       => $content,
			'title_class'   => '',
			'title_style'   => '',
			'content_class' => '',
			'content_style' => '',
			'title'         => ''
		];
	}

	/**
	 * Get the list of categories with boards, considering the selected boards in the portal settings
	 *
	 * Получаем список всех категорий с разделами, учитывая отмеченные разделы в настройках портала
	 *
	 * @return array
	 */
	private function getListSelectedBoards()
	{
		global $modSettings;

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
