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

namespace LightPortal\Actions;

use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Articles\Services\CategoryPageArticleService;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Lists\CategoryList;
use LightPortal\Utils\Str;

use function LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

class CategoryPageList extends AbstractPageList
{
	public function __construct(protected CardListInterface $cardList, CategoryPageArticleService $articleService)
	{
		parent::__construct($cardList, $articleService);
	}

	public function show(): void
	{
		$category = [
			'id' => Str::typed('int', $this->request()->get('id'))
		];

		$categories = app(CategoryList::class)();
		if (array_key_exists($category['id'], $categories) === false) {
			Utils::$context['error_link'] = PortalSubAction::CATEGORIES->url();
			Lang::$txt['back'] = Lang::$txt['lp_all_categories'];
			ErrorHandler::fatalLang('lp_category_not_found', false, status: 404);
		}

		if ($category['id'] === 0) {
			Utils::$context['page_title'] = Lang::$txt['lp_all_pages_without_category'];
		} else {
			$category = $categories[$category['id']];
			Utils::$context['page_title'] = sprintf(Lang::$txt['lp_all_pages_with_category'], $category['title']);
		}

		Utils::$context['description'] = $category['description'] ?? '';
		Utils::$context['lp_category_edit_link'] = Config::$scripturl
			. '?action=admin;area=lp_categories;sa=edit;id=' . $category['id'];
		Utils::$context['canonical_url'] = PortalSubAction::CATEGORIES->url() . ';id=' . $category['id'];
		Utils::$context['robot_no_index'] = true;

		$this->breadcrumbs()
			->add(Lang::$txt['lp_all_categories'], PortalSubAction::CATEGORIES->url())
			->add($category['title'] ?? Lang::$txt['lp_no_category'], before: $category['icon']);

		$this->cardList->show($this);

		Utils::obExit();
	}
}
