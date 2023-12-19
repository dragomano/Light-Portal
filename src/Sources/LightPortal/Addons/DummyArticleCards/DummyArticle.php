<?php declare(strict_types=1);

/**
 * DummyArticle.php
 *
 * @package DummyArticleCards (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 15.12.23
 */

namespace Bugo\LightPortal\Addons\DummyArticleCards;

use Bugo\LightPortal\Front\AbstractArticle;
use DateTime;
use Exception;

if (! defined('SMF'))
	die('No direct access...');

class DummyArticle extends AbstractArticle
{
	private string $limit;

	public function __construct()
	{
		$this->limit = $this->modSettings['lp_num_items_per_page'] ?? '6';
	}

	public function init(): void {}

	/**
	 * @throws Exception
	 */
	public function getData(int $start, int $limit): array
	{
		$products = $this->cache('active_layout_addon_demo_products')
			->setLifeTime(21600)
			->setFallback(self::class, 'getProducts');

		$users = $this->cache('active_layout_addon_demo_users')
			->setLifeTime(21600)
			->setFallback(self::class, 'getUsers');

		$demo_articles = [];

		foreach ($products as $id => $article) {
			if (empty($this->context['lp_dummy_article_cards_plugin']['use_lorem_ipsum'])) {
				$section = $article['brand'];
				$title   = $article['title'];
				$image   = $article['thumbnail'];
				$teaser  = empty($this->modSettings['lp_show_teaser']) ? '' : $article['description'];
				$tag     = $article['category'];
			} else {
				$section = $this->getShortenText(Lorem::ipsum(1), 20);
				$title   = $this->getShortenText(Lorem::ipsum(1), 40);
				$image   = 'https://loremflickr.com/470/235?random=' . $article['id'];
				$teaser  = empty($this->modSettings['lp_show_teaser']) ? '' : $this->getTeaser(Lorem::ipsum(4));
				$tag     = $this->getShortenText(Lorem::ipsum(1), 10);
			}

			$demo_articles[$article['id']] = [
				'id'        => $article['id'],
				'section'   => [
					'name' => $section,
					'link' => $this->scripturl . '?board=' . random_int(0, 100) . '.0'
				],
				'author'    => [
					'id'     => $users[$id]['id'],
					'link'   => $this->scripturl . '?action=profile;u=' . $users[$id]['id'],
					'name'   => $users[$id]['firstName'] . ' ' . $users[$id]['lastName'],
					'avatar' => '<img class="avatar" src="' . $users[$id]['image'] . '" alt="' . $users[$id]['username'] . '">'
				],
				'date'      => random_int((new DateTime('-2 years'))->getTimestamp(), time()),
				'title'     => $title,
				'link'      => $link = $this->scripturl . '?topic=' . $article['id'] . '.0',
				'is_new'    => random_int(0, 1),
				'views'     => [
					'num'   => random_int(0, 9999),
					'title' => $this->txt['lp_views']
				],
				'replies'   => [
					'num'   => $num_replies = random_int(0, 9999),
					'title' => $this->txt['lp_replies']
				],
				'css_class' => random_int(0, 1) ? ' sticky' : '',
				'image'     => $image,
				'can_edit'  => $this->user_info['is_admin'],
				'edit_link' => $this->scripturl . '?action=post;msg=' . ($msg_id = random_int(0, 9999)) . ';topic=' . $article['id'] . '.0',
				'teaser'    => $teaser,
				'msg_link'  => $num_replies ? $this->scripturl . '?msg=' . $msg_id : $link,
				'rating'    => $article['rating'],
				'tags'      => [
					['name' => $tag, 'href' => LP_BASE_URL . ';sa=tags;id=' . random_int(1, 99)]
				],
			];
		}

		$dates = array_column($demo_articles, 'date');
		array_multisort($dates, SORT_DESC, $demo_articles);

		return $demo_articles;
	}

	public function getTotalCount(): int
	{
		$products = $this->cache('active_layout_addon_demo_products')
			->setLifeTime(21600)
			->setFallback(self::class, 'getProducts');

		return count($products);
	}

	public function getProducts(): array
	{
		$data = file_get_contents('https://dummyjson.com/products?limit=' . $this->limit);

		return $this->jsonDecode($data)['products'] ?? [];
	}

	public function getUsers(): array
	{
		$data = file_get_contents('https://dummyjson.com/users?limit=' . $this->limit);

		return $this->jsonDecode($data)['users'] ?? [];
	}
}