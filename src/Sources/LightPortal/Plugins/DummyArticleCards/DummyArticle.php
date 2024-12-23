<?php declare(strict_types=1);

/**
 * @package DummyArticleCards (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\DummyArticleCards;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Articles\AbstractArticle;
use Bugo\LightPortal\Utils\CacheTrait;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use DateTime;
use Exception;

if (! defined('LP_NAME'))
	die('No direct access...');

class DummyArticle extends AbstractArticle
{
	use CacheTrait;

	private readonly string $limit;

	public function __construct()
	{
		$this->limit = Setting::get('lp_num_items_per_page', 'int', 6);
	}

	public function init(): void {}

	/**
	 * @throws Exception
	 */
	public function getData(int $start, int $limit): array
	{
		$products = $this->cache('active_layout_addon_demo_products')
			->setLifeTime(21600)
			->setFallback(fn() => $this->getProducts());

		$users = $this->cache('active_layout_addon_demo_users')
			->setLifeTime(21600)
			->setFallback(fn() => $this->getUsers());

		$demoArticles = [];

		$keywords = empty(Utils::$context['lp_dummy_article_cards_plugin']['keywords'])
			? '' : (Utils::$context['lp_dummy_article_cards_plugin']['keywords'] . '/all');

		foreach ($products as $id => $article) {
			if (empty(Utils::$context['lp_dummy_article_cards_plugin']['use_lorem_ipsum'])) {
				$section = $article['brand'];
				$title   = $article['title'];
				$image   = $article['thumbnail'];
				$teaser  = empty(Config::$modSettings['lp_show_teaser']) ? '' : $article['description'];
				$tag     = $article['category'];
			} else {
				$section = Utils::shorten(Lorem::ipsum(1), 20);
				$title   = Utils::shorten(Lorem::ipsum(1), 40);
				$image   = 'https://loremflickr.com/470/235/' . $keywords . '?random=' . $article['id'];
				$teaser  = empty(Config::$modSettings['lp_show_teaser']) ? '' : Str::getTeaser(Lorem::ipsum(4));
				$tag     = Utils::shorten(Lorem::ipsum(1), 10);
			}

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
				'link'      => $link = Config::$scripturl . '?topic=' . $article['id'] . '.0',
				'is_new'    => random_int(0, 1),
				'views'     => [
					'num'   => random_int(0, 9999),
					'title' => Lang::$txt['lp_views']
				],
				'replies'   => [
					'num'   => $numReplies = random_int(0, 9999),
					'title' => Lang::$txt['lp_replies']
				],
				'css_class' => random_int(0, 1) ? ' sticky' : '',
				'image'     => $image,
				'can_edit'  => User::$info['is_admin'],
				'edit_link' => Config::$scripturl . '?action=post;msg=' . ($msgId = random_int(0, 9999)) . ';topic=' . $article['id'] . '.0',
				'teaser'    => $teaser,
				'msg_link'  => $numReplies ? Config::$scripturl . '?msg=' . $msgId : $link,
				'rating'    => $article['rating'],
				'tags'      => [
					['title' => $tag, 'href' => LP_BASE_URL . ';sa=tags;id=' . random_int(1, 99)]
				],
			];
		}

		$dates = array_column($demoArticles, 'date');
		array_multisort($dates, SORT_DESC, $demoArticles);

		return $demoArticles;
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
		$data = file_get_contents('https://dummyjson.com/products?limit=' . $this->limit);

		return Utils::jsonDecode($data, true)['products'] ?? [];
	}

	public function getUsers(): array
	{
		$data = file_get_contents('https://dummyjson.com/users?limit=' . $this->limit);

		return Utils::jsonDecode($data, true)['users'] ?? [];
	}
}
