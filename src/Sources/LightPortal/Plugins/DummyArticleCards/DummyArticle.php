<?php declare(strict_types=1);

/**
 * @package DummyArticleCards (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 24.09.25
 */

namespace Bugo\LightPortal\Plugins\DummyArticleCards;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Articles\AbstractArticle;
use Bugo\LightPortal\Enums\PortalSubAction;
use Bugo\LightPortal\Utils\Traits\HasCache;
use DateTime;
use Exception;

if (! defined('LP_NAME'))
	die('No direct access...');

class DummyArticle extends AbstractArticle
{
	use HasCache;

	public function init(): void {}

	public function getSortingOptions(): array
	{
		return [];
	}

	/**
	 * @throws Exception
	 */
	public function getData(int $start, int $limit, string $sortType = null): iterable
	{
		$products = $this->cache('active_layout_addon_demo_products')
			->setLifeTime(21600)
			->setFallback(fn() => $this->getProducts());

		$users = $this->cache('active_layout_addon_demo_users')
			->setLifeTime(21600)
			->setFallback(fn() => $this->getUsers());

		$demoArticles = [];

		foreach ($products as $id => $article) {
			$section = $article['brand'];
			$title   = $article['title'];
			$image   = $article['thumbnail'];
			$teaser  = empty(Config::$modSettings['lp_show_teaser']) ? '' : $article['description'];
			$tag     = $article['category'];

			$demoArticles[$article['id']] = [
				'id'        => $article['id'],
				'section'   => [
					'name' => $section,
					'link' => Config::$scripturl . '?board=' . random_int(0, 100) . '.0'
				],
				'author'    => [
					'id'     => $users[$id]['id'],
					'link'   => Config::$scripturl . '?action=profile;u=' . $users[$id]['id'],
					'name'   => $users[$id]['firstName'] . ' ' . $users[$id]['lastName'],
					'avatar' => '<img class="avatar" src="' . $users[$id]['image'] . '" alt="' . $users[$id]['username'] . '">'
				],
				'date'      => random_int((new DateTime('-2 years'))->getTimestamp(), time()),
				'title'     => $title,
				'link'      => Config::$scripturl . '?topic=' . $article['id'] . '.0',
				'is_new'    => random_int(0, 1),
				'views'     => [
					'num'   => random_int(0, 9999),
					'title' => Lang::$txt['lp_views']
				],
				'replies'   => [
					'num'   => random_int(0, 9999),
					'title' => Lang::$txt['lp_replies']
				],
				'css_class' => random_int(0, 1) ? ' sticky' : '',
				'image'     => $image,
				'can_edit'  => User::$me->is_admin,
				'edit_link' => Config::$scripturl . '?action=post;msg=' . (random_int(0, 9999)) . ';topic=' . $article['id'] . '.0',
				'teaser'    => $teaser,
				'rating'    => $article['rating'],
				'tags'      => [
					['title' => $tag, 'href' => PortalSubAction::TAGS->url() . ';id=' . random_int(1, 99)]
				],
			];
		}

		$dates = array_column($demoArticles, 'date');
		array_multisort($dates, SORT_DESC, $demoArticles);

		foreach ($demoArticles as $id => $article) {
			yield $id => $article;
		}
	}

	public function getTotalCount(): int
	{
		$products = $this->cache('active_layout_addon_demo_products')
			->setLifeTime(21600)
			->setFallback(fn() => $this->getProducts());

		return count($products);
	}

	public function getProducts(): array
	{
		$data = file_get_contents(__DIR__ . '/products.json');

		return Utils::jsonDecode($data, true)['products'] ?? [];
	}

	public function getUsers(): array
	{
		$data = file_get_contents(__DIR__ . '/users.json');

		return Utils::jsonDecode($data, true)['users'] ?? [];
	}
}
