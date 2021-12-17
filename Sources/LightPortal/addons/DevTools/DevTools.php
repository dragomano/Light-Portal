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
 * @version 15.12.21
 */

namespace Bugo\LightPortal\Addons\DevTools;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\{Helper, FrontPage};

class DevTools extends Plugin
{
	public string $type = 'frontpage';

	public function addSettings(array &$config_vars)
	{
		global $txt;

		$config_vars['dev_tools'][] = array('check', 'show_template_switcher');
		$config_vars['dev_tools'][] = array('check', 'fake_cards', 'subtext' => $txt['lp_dev_tools']['fake_cards_subtext']);
	}

	public function frontCustomTemplate()
	{
		global $modSettings, $smcFunc, $scripturl, $txt, $context;

		if (empty($modSettings['lp_dev_tools_addon_show_template_switcher']) && empty($modSettings['lp_dev_tools_addon_fake_cards']))
			return;

		if (! empty($modSettings['lp_dev_tools_addon_fake_cards'])) {
			$demo_articles = [];

			$products = Helper::cache('dev_tools_addon_demo_products')
				->setLifeTime(21600)
				->setFallback(__CLASS__, 'getProducts');
			$users = Helper::cache('dev_tools_addon_demo_users')
				->setLifeTime(21600)
				->setFallback(__CLASS__, 'getUsers');

			foreach ($products as $id => $article) {
				$date = $smcFunc['random_int']((new \DateTime('-2 years'))->getTimestamp(), time());

				$demo_articles[$article['id']] = array(
					'id'        => $article['id'],
					'section'   => array(
						'name' => $txt['board_name'],
						'link' => $scripturl . '?board=' . $smcFunc['random_int'](0, 100) . '.0'
					),
					'id_msg'    => $msg_id = $smcFunc['random_int'](0, 9999),
					'author'    => array(
						'id'     => $users[$id]['id'],
						'link'   => $scripturl . '?action=profile;u=' . $users[$id]['id'],
						'name'   => $users[$id]['first_name'] . ' ' . $users[$id]['last_name'],
						'avatar' => $users[$id]['avatar']
					),
					'date'      => (new FrontPage)->getCardDate($date),
					'title'     => shorten_subject(Lorem::ipsum(1), 40),
					'link'      => $link = $scripturl . '?topic=' . $article['id'] . '.0',
					'is_new'    => rand(0, 1),
					'views'     => array('num' => $smcFunc['random_int'](0, 9999), 'title' => $txt['lp_views']),
					'replies'   => array('num' => $num_replies = $smcFunc['random_int'](0, 9999), 'title' => $txt['lp_replies']),
					'css_class' => rand(0, 1) ? ' sticky' : '',
					'image'     => 'https://picsum.photos/200/300?random=' . $article['id'],
					'can_edit'  => true,
					'edit_link' => '',
					'teaser'    => Helper::getTeaser(Lorem::ipsum(4)),
					'msg_link'  => $num_replies ? $scripturl . '?msg=' . $msg_id : $link,
					'tags'      => array(
						['name' => 'Tag1', 'href' => $scripturl . '?action=' . LP_ACTION . ';sa=tags;id=' . $smcFunc['random_int'](1, 99)],
						['name' => 'Tag2', 'href' => $scripturl . '?action=' . LP_ACTION . ';sa=tags;id=' . $smcFunc['random_int'](1, 99)],
						['name' => 'Tag3', 'href' => $scripturl . '?action=' . LP_ACTION . ';sa=tags;id=' . $smcFunc['random_int'](1, 99)]
					),
					'datetime'  => date('Y-m-d', $date)
				);
			}

			$context['lp_frontpage_articles'] = $demo_articles;

			$context['linktree'][count($context['linktree']) - 1]['extra_after'] = '';
		}

		if (empty($modSettings['lp_dev_tools_addon_show_template_switcher']))
			return;

		$this->loadTemplate();

		$context['frontpage_layouts'] = FrontPage::getLayouts();

		$context['template_layers'][] = 'layout_switcher';

		$context['current_layout'] = Helper::post('layout', $modSettings['lp_frontpage_layout'] ?? 'articles');

		$context['sub_template'] = 'show_' . $context['current_layout'];
	}

	public function getProducts(): array
	{
		$products = fetch_web_data('https://reqres.in/api/products');

		return json_decode($products, true)['data'] ?? [];
	}

	public function getUsers(): array
	{
		$users = fetch_web_data('https://reqres.in/api/users');

		return json_decode($users, true)['data'] ?? [];
	}

	public function credits(array &$links)
	{
		$links[] = array(
			'title'  => 'Reqres',
			'link'   => 'https://reqres.in',
			'author' => 'Ben Howdle'
		);

		$links[] = array(
			'title' => 'Lorem Picsum',
			'link' => 'https://picsum.photos',
			'author' => 'David Marby & Nijiko Yonskai',
			'license' => array(
				'name' => 'the MIT License',
				'link' => 'https://github.com/DMarby/picsum-photos/blob/main/LICENSE.md'
			)
		);
	}
}
