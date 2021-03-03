<?php

namespace Bugo\LightPortal\Addons\DevTools;

use Bugo\LightPortal\{Helpers, FrontPage};

/**
 * DevTools
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.7
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class DevTools
{
	/**
	 * @var string
	 */
	public $addon_type = 'frontpage';

	/**
	 * @param array $config_vars
	 * @return void
	 */
	public function addSettings(&$config_vars)
	{
		global $txt;

		$config_vars[] = array('check', 'lp_dev_tools_addon_show_template_switcher');
		$config_vars[] = array('check', 'lp_dev_tools_addon_fake_cards', 'subtext' => $txt['lp_dev_tools_addon_fake_cards_subtext']);
	}

	/**
	 * @return void
	 */
	public function frontCustomTemplate()
	{
		global $modSettings, $scripturl, $txt, $context;

		if (empty($modSettings['lp_dev_tools_addon_show_template_switcher']) && empty($modSettings['lp_dev_tools_addon_fake_cards']))
			return;

		if (!empty($modSettings['lp_dev_tools_addon_fake_cards'])) {
			$demo_articles = [];

			$products = Helpers::cache('dev_tools_addon_demo_products', 'getProducts', __CLASS__, 21600);
			$users    = Helpers::cache('dev_tools_addon_demo_users', 'getUsers', __CLASS__, 21600);

			foreach ($products as $id => $article) {
				$date = random_int((new \DateTime('-2 years'))->getTimestamp(), time());

				$demo_articles[$article['id']] = array(
					'id'        => $article['id'],
					'section'   => array(
						'name' => $txt['board_name'],
						'link' => $scripturl . '?board=' . random_int(0, 100) . '.0'
					),
					'id_msg'    => $msg_id = random_int(0, 9999),
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
					'views'     => array('num' => random_int(0, 9999), 'title' => $txt['lp_views']),
					'replies'   => array('num' => $num_replies = random_int(0, 9999), 'title' => $txt['lp_replies']),
					'css_class' => rand(0, 1) ? ' sticky' : '',
					'image'     => 'https://picsum.photos/200/300?random=' . $article['id'],
					'can_edit'  => true,
					'edit_link' => '',
					'teaser'    => Lorem::ipsum(4),
					'msg_link'  => $num_replies ? $scripturl . '?msg=' . $msg_id : $link,
					'keywords'  => ['Tag1', 'Tag2', 'Tag3'],
					'datetime'  => date('Y-m-d', $date)
				);
			}

			$context['lp_frontpage_articles'] = $demo_articles;

			$context['linktree'][count($context['linktree']) - 1]['extra_after'] = '';
		}

		if (empty($modSettings['lp_dev_tools_addon_show_template_switcher']))
			return;

		require_once(__DIR__ . '/Template.php');

		$context['frontpage_layouts'] = (new FrontPage)->getLayouts();

		$context['template_layers'][] = 'layout_switcher';

		$context['current_layout'] = Helpers::post('layout', 'articles');

		$context['sub_template'] = 'show_' . $context['current_layout'];
	}

	/**
	 * @return array
	 */
	public function getProducts()
	{
		$products = fetch_web_data('https://reqres.in/api/products');

		return json_decode($products, true)['data'] ?? [];
	}

	/**
	 * @return array
	 */
	public function getUsers()
	{
		$users = fetch_web_data('https://reqres.in/api/users');

		return json_decode($users, true)['data'] ?? [];
	}

	/**
	 * @param array $links
	 * @return void
	 */
	public function credits(&$links)
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
