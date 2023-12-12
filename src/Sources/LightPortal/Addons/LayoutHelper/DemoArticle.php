<?php declare(strict_types=1);

/**
 * DemoArticle.php
 *
 * @package LayoutHelper (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 12.11.23
 */

namespace Bugo\LightPortal\Addons\LayoutHelper;

use Bugo\LightPortal\Front\AbstractArticle;
use DateTime;
use Exception;

if (! defined('SMF'))
	die('No direct access...');

class DemoArticle extends AbstractArticle
{
	public function init(): void {}

	/**
	 * @throws Exception
	 */
	public function getData(int $start, int $limit): array
	{
		$products = $this->cache('layout_helper_addon_demo_products')
			->setLifeTime(21600)
			->setFallback(self::class, 'getProducts');

		$users = $this->cache('layout_helper_addon_demo_users')
			->setLifeTime(21600)
			->setFallback(self::class, 'getUsers');

		$demo_articles = [];

		foreach ($products as $id => $article) {
			$demo_articles[$article['id']] = [
				'id'        => $article['id'],
				'section'   => [
					'name' => $this->txt['board_name'],
					'link' => $this->scripturl . '?board=' . $this->smcFunc['random_int'](0, 100) . '.0'
				],
				'author'    => [
					'id'     => $users[$id]['id'],
					'link'   => $this->scripturl . '?action=profile;u=' . $users[$id]['id'],
					'name'   => $users[$id]['first_name'] . ' ' . $users[$id]['last_name'],
					'avatar' => '<img class="avatar" src="' . $users[$id]['avatar'] . '" alt="' . $users[$id]['id'] . '">'
				],
				'date'      => $this->smcFunc['random_int']((new DateTime('-2 years'))->getTimestamp(), time()),
				'title'     => $this->getShortenText(Lorem::ipsum(1), 40),
				'link'      => $link = $this->scripturl . '?topic=' . $article['id'] . '.0',
				'is_new'    => random_int(0, 1),
				'views'     => [
					'num'   => $this->smcFunc['random_int'](0, 9999),
					'title' => $this->txt['lp_views']
				],
				'replies'   => [
					'num'   => $num_replies = $this->smcFunc['random_int'](0, 9999),
					'title' => $this->txt['lp_replies']
				],
				'css_class' => random_int(0, 1) ? ' sticky' : '',
				'image'     => 'https://loremflickr.com/470/235?random=' . $article['id'],
				'can_edit'  => $this->user_info['is_admin'],
				'edit_link' => $this->scripturl . '?action=post;msg=' . ($msg_id = $this->smcFunc['random_int'](0, 9999)) . ';topic=' . $article['id'] . '.0',
				'teaser'    => empty($this->modSettings['lp_show_teaser']) ? '' : $this->getTeaser(Lorem::ipsum(4)),
				'msg_link'  => $num_replies ? $this->scripturl . '?msg=' . $msg_id : $link,
				'tags'      => [
					['name' => 'Tag1', 'href' => LP_BASE_URL . ';sa=tags;id=' . $this->smcFunc['random_int'](1, 99)],
					['name' => 'Tag2', 'href' => LP_BASE_URL . ';sa=tags;id=' . $this->smcFunc['random_int'](1, 99)],
					['name' => 'Tag3', 'href' => LP_BASE_URL . ';sa=tags;id=' . $this->smcFunc['random_int'](1, 99)]
				],
			];
		}

		$dates = array_column($demo_articles, 'date');
		array_multisort($dates, SORT_DESC, $demo_articles);

		return $demo_articles;
	}

	public function getTotalCount(): int
	{
		$products = $this->cache('layout_helper_addon_demo_products')
			->setLifeTime(21600)
			->setFallback(self::class, 'getProducts');

		return count($products);
	}

	public function getProducts(): array
	{
		$products = $this->fetchWebData('https://reqres.in/api/products');

		return $this->jsonDecode($products)['data'] ?? [];
	}

	public function getUsers(): array
	{
		$users = $this->fetchWebData('https://reqres.in/api/users');

		return $this->jsonDecode($users)['data'] ?? [];
	}
}
