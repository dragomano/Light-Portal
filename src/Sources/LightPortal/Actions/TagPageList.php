<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Actions;

use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Articles\Services\TagPageArticleService;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Lists\TagList;
use LightPortal\Utils\Str;

use function LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

class TagPageList extends AbstractPageList
{
	public function __construct(protected CardListInterface $cardList, TagPageArticleService $articleService)
	{
		parent::__construct($cardList, $articleService);
	}

	public function show(): void
	{
		$tag = [
			'id' => Str::typed('int', $this->request()->get('id'))
		];

		$tags = app(TagList::class)();
		if (array_key_exists($tag['id'], $tags) === false) {
			Utils::$context['error_link'] = PortalSubAction::TAGS->url();
			Lang::$txt['back'] = Lang::$txt['lp_all_page_tags'];
			ErrorHandler::fatalLang('lp_tag_not_found', false, status: 404);
		}

		$tag = $tags[$tag['id']];
		Utils::$context['page_title'] = sprintf(Lang::$txt['lp_all_tags_by_key'], $tag['title']);
		Utils::$context['canonical_url'] = PortalSubAction::TAGS->url() . ';id=' . $tag['id'];
		Utils::$context['robot_no_index'] = true;

		$this->breadcrumbs()
			->add(Lang::$txt['lp_all_page_tags'], PortalSubAction::TAGS->url())
			->add($tag['title'], before: $tag['icon']);

		$this->cardList->show($this);

		Utils::obExit();
	}
}
