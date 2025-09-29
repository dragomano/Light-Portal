<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Articles;

use Bugo\LightPortal\Utils\Setting;

if (! defined('SMF'))
	die('No direct access...');

final class ChosenTopicArticle extends TopicArticle
{
	private array $selectedTopics = [];

	public function init(): void
	{
		parent::init();

		$this->selectedBoards = [];

		$this->selectedTopics = Setting::get('lp_frontpage_topics', 'array', []);

		$this->wheres[] = 'AND t.id_topic IN ({array_int:selected_topics})';

		$this->params['selected_topics'] = $this->selectedTopics;
	}

	public function getData(int $start, int $limit, string $sortType = null): iterable
	{
		if (empty($this->selectedTopics))
			return [];

		return parent::getData($start, $limit, $sortType);
	}

	public function getTotalCount(): int
	{
		if (empty($this->selectedTopics))
			return 0;

		return parent::getTotalCount();
	}
}
