<?php

namespace Bugo\LightPortal\Front;

/**
 * ChosenPageArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.7
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ChosenPageArticle extends PageArticle
{
	/**
	 * @var array
	 */
	private $selected_pages = [];

	/**
	 * Add properties of the parent class
	 *
	 * Дополняем свойства класса-родителя
	 *
	 * @return void
	 */
	public function init()
	{
		global $modSettings;

		parent::init();

		$this->selected_categories = [];

		$this->selected_pages = !empty($modSettings['lp_frontpage_pages']) ? explode(',', $modSettings['lp_frontpage_pages']) : [];

		$this->wheres[] = 'AND p.page_id IN ({array_int:selected_pages})';

		$this->params['selected_pages'] = $this->selected_pages;
	}

	/**
	 * Get selected topics and portal pages
	 *
	 * Получаем выбранные темы и страницы портала
	 *
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function getData(int $start, int $limit)
	{
		if (empty($this->selected_pages))
			return [];

		return parent::getData($start, $limit);
	}

	/**
	 * Get count of selected topics and portal pages
	 *
	 * Получаем количество выбранных тем и страниц портала
	 *
	 * @return int
	 */
	public function getTotalCount()
	{
		if (empty($this->selected_pages))
			return 0;

		return parent::getTotalCount();
	}
}
