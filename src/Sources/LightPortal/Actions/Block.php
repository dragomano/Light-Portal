<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Actions;

use Bugo\Compat\Config;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Action;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Utils\Content;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasRequest;

use function array_filter;
use function array_flip;
use function array_merge;
use function array_slice;
use function explode;
use function in_array;
use function str_contains;

use const LP_ACTION;
use const LP_CACHE_TIME;
use const LP_PAGE_PARAM;

if (! defined('SMF'))
	die('No direct access...');

final class Block implements ActionInterface
{
	use HasRequest;

	public function show(): void
	{
		if (Setting::hideBlocksInACP() || $this->request()->is('devtools') || $this->request()->has('preview'))
			return;

		if (empty(Utils::$context['template_layers']) || empty(Utils::$context['lp_active_blocks']))
			return;

		if (empty(User::$me->allowedTo('light_portal_view')) || empty($blocks = $this->getFilteredByAreas()))
			return;

		foreach ($blocks as $item => $data) {
			if (Permission::canViewItem($data['permissions']) === false)
				continue;

			$data['can_edit'] = Utils::$context['user']['is_admin'];

			$data['content'] = empty($data['content'])
				? Content::prepare(
					$data['type'],
					$data['id'],
					LP_CACHE_TIME,
					Utils::$context['lp_active_blocks'][$data['id']]['parameters'] ?? []
				)
				: Content::parse($data['content'], $data['type']);

			Utils::$context['lp_blocks'][$data['placement']][$item] = $data;

			if (empty($data['parameters']['hide_header'])) {
				$title = Str::getTranslatedTitle($data['titles']);
				$icon  = Icon::parse(Utils::$context['lp_blocks'][$data['placement']][$item]['icon']);

				if (! empty($data['parameters']['link_in_title'])) {
					$title = Str::html('a', $title)->href($data['parameters']['link_in_title']);
				}
			} else {
				$title = $icon = '';
			}

			Utils::$context['lp_blocks'][$data['placement']][$item]['title'] = $icon . $title;
		}

		Theme::loadTemplate('LightPortal/ViewBlocks');

		$counter = 0;
		foreach (Utils::$context['template_layers'] as $layer) {
			$counter++;

			if ($layer === 'body')
				break;
		}

		Utils::$context['template_layers'] = array_merge(
			array_slice(Utils::$context['template_layers'], 0, $counter, true),
			['lp_portal'],
			array_slice(Utils::$context['template_layers'], $counter, null, true)
		);
	}

	private function getFilteredByAreas(): array
	{
		$area = Utils::$context['current_action'] ?: (
			empty(Config::$modSettings['lp_frontpage_mode']) ? Action::FORUM->value : LP_ACTION
		);

		if (Setting::isStandaloneMode()) {
			if (Config::$modSettings['lp_standalone_url'] === $this->request()->url()) {
				$area = LP_ACTION;
			} elseif (empty(Utils::$context['current_action'])) {
				$area = Action::FORUM->value;
			}
		}

		if (isset(Utils::$context['current_board']) || isset(Utils::$context['lp_page'])) {
			$area = '';
		}

		if (! empty(Utils::$context['lp_page']['slug']) && Setting::isFrontpage(Utils::$context['lp_page']['slug'])) {
			$area = LP_ACTION;
		}

		return array_filter(Utils::$context['lp_active_blocks'], function ($block) use ($area) {
			$tempAreas = $block['areas'];
			$block['areas'] = array_flip($block['areas']);

			if (isset($block['areas']['!' . $area]) && $tempAreas[0] === 'all')
				return false;

			if (isset($block['areas']['all']) || isset($block['areas'][$area]))
				return true;

			if (
				$area === LP_ACTION
				&& isset($block['areas'][Action::HOME->value])
				&& empty(Utils::$context['lp_page'])
				&& empty(Utils::$context['current_action'])
			) {
				return true;
			}

			if (isset(Utils::$context['lp_page']['slug'])) {
				if (
					isset($block['areas']['!' . LP_PAGE_PARAM . '=' . Utils::$context['lp_page']['slug']])
					&& $tempAreas[0] === 'pages'
				) {
					return false;
				}

				if (
					isset($block['areas']['pages'])
					|| isset($block['areas'][LP_PAGE_PARAM . '=' . Utils::$context['lp_page']['slug']])
				) {
					return true;
				}
			}

			if (empty(Utils::$context['current_board']))
				return false;

			if (isset($block['areas']['boards']) && empty(Utils::$context['current_topic']))
				return true;

			if (isset($block['areas']['topics']) && ! empty(Utils::$context['current_topic']))
				return true;

			$boards = $topics = [];
			foreach ($tempAreas as $areas) {
				$entity = explode('=', (string) $areas);

				if ($entity[0] === 'board') {
					$boards = $this->getAllowedIds($entity[1]);
				}

				if ($entity[0] === 'topic') {
					$topics = $this->getAllowedIds($entity[1]);
				}
			}

			return in_array(Utils::$context['current_board'], $boards)
				|| (isset(Utils::$context['current_topic']) && in_array(Utils::$context['current_topic'], $topics));
		});
	}

	private function getAllowedIds(string $entity = ''): array
	{
		$ids = [];

		$items = explode('|', $entity);
		foreach ($items as $item) {
			if (str_contains($item, '-')) {
				$range = explode('-', $item);
				for ($i = $range[0]; $i <= $range[1]; $i++) {
					$ids[] = $i;
				}
			} else {
				$ids[] = $item;
			}
		}

		return $ids;
	}
}
