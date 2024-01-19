<?php declare(strict_types=1);

/**
 * ChosenTopicArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Front;

use Bugo\LightPortal\Utils\Config;

if (! defined('SMF'))
	die('No direct access...');

final class ChosenTopicArticle extends TopicArticle
{
	private array $selected_topics = [];

	public function init(): void
	{
		parent::init();

		$this->selected_boards = [];

		$this->selected_topics = empty(Config::$modSettings['lp_frontpage_topics']) ? [] : explode(',', Config::$modSettings['lp_frontpage_topics']);

		$this->wheres[] = 'AND t.id_topic IN ({array_int:selected_topics})';

		$this->params['selected_topics'] = $this->selected_topics;
	}

	public function getData(int $start, int $limit): array
	{
		if (empty($this->selected_topics))
			return [];

		return parent::getData($start, $limit);
	}

	public function getTotalCount(): int
	{
		if (empty($this->selected_topics))
			return 0;

		return parent::getTotalCount();
	}
}
