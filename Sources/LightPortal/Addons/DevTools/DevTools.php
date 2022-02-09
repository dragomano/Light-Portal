<?php

/**
 * DevTools.php
 *
 * @package DevTools (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 10.01.22
 */

namespace Bugo\LightPortal\Addons\DevTools;

use Bugo\LightPortal\Addons\Plugin;
use DateTime;
use Exception;

if (! defined('LP_NAME'))
	die('No direct access...');

class DevTools extends Plugin
{
	public string $type = 'frontpage';

	public function addSettings(array &$config_vars)
	{
		$config_vars['dev_tools'][] = ['check', 'show_template_switcher'];
		$config_vars['dev_tools'][] = ['check', 'fake_cards', 'subtext' => $this->txt['lp_dev_tools']['fake_cards_subtext']];
	}

	/**
	 * @hook
	 * @throws Exception
	 */
	public function frontCustomTemplate()
	{
		if (empty($this->modSettings['lp_dev_tools_addon_show_template_switcher']) && empty($this->modSettings['lp_dev_tools_addon_fake_cards']))
			return;

		if (! empty($this->modSettings['lp_dev_tools_addon_fake_cards'])) {
			$demo_articles = [];

			$products = $this->cache('dev_tools_addon_demo_products')
				->setLifeTime(21600)
				->setFallback(__CLASS__, 'getProducts');
			$users = $this->cache('dev_tools_addon_demo_users')
				->setLifeTime(21600)
				->setFallback(__CLASS__, 'getUsers');

			foreach ($products as $id => $article) {
				$date = $this->smcFunc['random_int']((new DateTime('-2 years'))->getTimestamp(), time());

				$demo_articles[$article['id']] = [
					'id'        => $article['id'],
					'section'   => [
						'name' => $this->txt['board_name'],
						'link' => $this->scripturl . '?board=' . $this->smcFunc['random_int'](0, 100) . '.0'
					],
					'id_msg'    => $msg_id = $this->smcFunc['random_int'](0, 9999),
					'author'    => [
						'id'     => $users[$id]['id'],
						'link'   => $this->scripturl . '?action=profile;u=' . $users[$id]['id'],
						'name'   => $users[$id]['first_name'] . ' ' . $users[$id]['last_name'],
						'avatar' => $users[$id]['avatar']
					],
					'date'      => $this->getFriendlyTime($date),
					'title'     => shorten_subject(Lorem::ipsum(1), 40),
					'link'      => $link = $this->scripturl . '?topic=' . $article['id'] . '.0',
					'is_new'    => rand(0, 1),
					'views'     => ['num' => $this->smcFunc['random_int'](0, 9999), 'title' => $this->txt['lp_views']],
					'replies'   => ['num' => $num_replies = $this->smcFunc['random_int'](0, 9999), 'title' => $this->txt['lp_replies']],
					'css_class' => rand(0, 1) ? ' sticky' : '',
					'image'     => 'https://picsum.photos/200/300?random=' . $article['id'],
					'can_edit'  => true,
					'edit_link' => '',
					'teaser'    => $this->getTeaser(Lorem::ipsum(4)),
					'msg_link'  => $num_replies ? $this->scripturl . '?msg=' . $msg_id : $link,
					'tags'      => [
						['name' => 'Tag1', 'href' => LP_BASE_URL . ';sa=tags;id=' . $this->smcFunc['random_int'](1, 99)],
						['name' => 'Tag2', 'href' => LP_BASE_URL . ';sa=tags;id=' . $this->smcFunc['random_int'](1, 99)],
						['name' => 'Tag3', 'href' => LP_BASE_URL . ';sa=tags;id=' . $this->smcFunc['random_int'](1, 99)]
					],
					'datetime'  => date('Y-m-d', $date)
				];
			}

			$this->context['lp_frontpage_articles'] = $demo_articles;

			$this->context['linktree'][count($this->context['linktree']) - 1]['extra_after'] = '';
		}

		if (empty($this->modSettings['lp_dev_tools_addon_show_template_switcher']))
			return;

		$this->context['frontpage_layouts'] = $this->getFrontPageLayouts();

		$this->context['current_layout'] = $this->post('layout', $this->modSettings['lp_frontpage_layout'] ?? 'articles');

		$this->loadTemplate('show_' . $this->context['current_layout'])->withLayer('layout_switcher');
	}

	public function getProducts(): array
	{
		$products = fetch_web_data('https://reqres.in/api/products');

		return smf_json_decode($products, true)['data'] ?? [];
	}

	public function getUsers(): array
	{
		$users = fetch_web_data('https://reqres.in/api/users');

		return smf_json_decode($users, true)['data'] ?? [];
	}

	public function credits(array &$links)
	{
		$links[] = [
			'title'  => 'Reqres',
			'link'   => 'https://reqres.in',
			'author' => 'Ben Howdle'
		];

		$links[] = [
			'title' => 'Lorem Picsum',
			'link' => 'https://picsum.photos',
			'author' => 'David Marby & Nijiko Yonskai',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/DMarby/picsum-photos/blob/main/LICENSE.md'
			]
		];
	}
}
