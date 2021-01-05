<?php

namespace Bugo\LightPortal\Front;

/**
 * ChosenTopicArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.5
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ChosenTopicArticle extends TopicArticle
{
	/**
	 * @var array
	 */
	private $selected_topics = [];

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

		$this->selected_topics = !empty($modSettings['lp_frontpage_topics']) ? explode(',', $modSettings['lp_frontpage_topics']) : [];

		$this->wheres[] = 'AND t.id_topic IN ({array_int:selected_topics})';

		$this->params['selected_topics'] = $this->selected_topics;
	}

	/**
	 * Get topics from selected boards
	 *
	 * Получаем темы из выбранных разделов
	 *
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function getData(int $start, int $limit)
	{
		if (empty($this->selected_topics))
			return [];

		return parent::getData($start, $limit);
	}

	/**
	 * Get count of active topics from selected boards
	 *
	 * Получаем количество активных тем из выбранных разделов
	 *
	 * @return int
	 */
	public function getTotalCount()
	{
		if (empty($this->selected_topics))
			return 0;

		return parent::getTotalCount();
	}
}
