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

namespace LightPortal\Enums;

use Bugo\Compat\Lang;
use LightPortal\Articles\BoardArticle;
use LightPortal\Articles\ChosenPageArticle;
use LightPortal\Articles\ChosenTopicArticle;
use LightPortal\Articles\PageArticle;
use LightPortal\Articles\TopicArticle;
use LightPortal\Enums\Traits\HasValues;

enum FrontPageMode: string
{
	use HasValues;

	case DEFAULT = '';
	case CHOSEN_PAGE = 'chosen_page';
	case ALL_PAGES = 'all_pages';
	case CHOSEN_PAGES = 'chosen_pages';
	case ALL_TOPICS = 'all_topics';
	case CHOSEN_TOPICS = 'chosen_topics';
	case CHOSEN_BOARDS = 'chosen_boards';

	public function getArticleClass(): ?string
	{
		return match($this) {
			self::ALL_PAGES => PageArticle::class,
			self::ALL_TOPICS => TopicArticle::class,
			self::CHOSEN_BOARDS => BoardArticle::class,
			self::CHOSEN_PAGES => ChosenPageArticle::class,
			self::CHOSEN_TOPICS => ChosenTopicArticle::class,
			default => null,
		};
	}

	public static function getSelectOptions(): array
	{
		return array_combine(self::values(), Lang::$txt['lp_frontpage_mode_set']);
	}
}
