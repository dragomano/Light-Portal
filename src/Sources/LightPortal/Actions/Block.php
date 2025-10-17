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

use const LP_ACTION;
use const LP_CACHE_TIME;
use const LP_PAGE_PARAM;

if (! defined('SMF'))
	die('No direct access...');

class Block implements ActionInterface
{
	use HasRequest;

	public function show(): void
	{
		if ($this->shouldSkipRendering()) {
			return;
		}

		$blocks = $this->getVisibleBlocks();
		if (empty($blocks)) {
			return;
		}

		$this->prepareBlocks($blocks);
		$this->injectPortalLayer();
	}

	protected function shouldSkipRendering(): bool
	{
		$req = $this->request();

		return Setting::hideBlocksInACP()
			|| $req->is('devtools')
			|| $req->has('preview')
			|| empty(Utils::$context['template_layers'])
			|| empty(Utils::$context['lp_active_blocks'])
			|| empty(User::$me->allowedTo('light_portal_view'));
	}

	protected function getVisibleBlocks(): array
	{
		$blocks = $this->getFilteredByAreas();
		if (empty($blocks)) {
			return [];
		}

		return array_filter($blocks, static fn($b) => Permission::canViewItem($b['permissions']) !== false);
	}

	protected function prepareBlocks(array $blocks): void
	{
		foreach ($blocks as $key => $block) {
			$block['can_edit'] = Utils::$context['user']['is_admin'];
			$block['content']  = $this->resolveContent($block);
			$block['title']    = $this->buildTitle($block);

			Utils::$context['lp_blocks'][$block['placement']][$key] = $block;
		}
	}

	protected function resolveContent(array $block): string
	{
		if (! empty($block['content'])) {
			return Content::parse($block['content'], $block['type']);
		}

		$params = Utils::$context['lp_active_blocks'][$block['id']]['parameters'] ?? [];

		return Content::prepare($block['type'], $block['id'], LP_CACHE_TIME, $params);
	}

	protected function buildTitle(array $block): string
	{
		if (! empty($block['parameters']['hide_header'])) {
			return '';
		}

		$title = $block['title'] ?? '';
		$icon  = Icon::parse($block['icon'] ?? '');

		if (! empty($block['parameters']['link_in_title'])) {
			$title = Str::html('a', $title)->href($block['parameters']['link_in_title']);
		}

		return $icon . $title;
	}

	protected function injectPortalLayer(): void
	{
		$layers = Utils::$context['template_layers'];
		$pos    = array_search('body', $layers, true);

		if ($pos === false) {
			return;
		}

		Theme::loadTemplate('LightPortal/ViewBlocks');

		Utils::$context['template_layers'] = array_merge(
			array_slice($layers, 0, $pos + 1, true),
			['lp_portal'],
			array_slice($layers, $pos + 1, null, true)
		);
	}

	protected function getFilteredByAreas(): array
	{
		$area = $this->resolveCurrentArea();

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

			$entities = $this->collectAllowedEntities($tempAreas);

			return in_array(Utils::$context['current_board'], $entities['boards'])
				|| (isset(Utils::$context['current_topic'])
					&& in_array(Utils::$context['current_topic'], $entities['topics']));
		});
	}

	protected function resolveCurrentArea(): string
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

		return $area;
	}

	protected function collectAllowedEntities(array $areas): array
	{
		$boards = $topics = [];

		foreach ($areas as $area) {
			$entity = explode('=', (string) $area, 2);

			if ($entity[0] === 'board') {
				$boards = array_merge($boards, $this->getAllowedIds($entity[1]));
			}

			if ($entity[0] === 'topic') {
				$topics = array_merge($topics, $this->getAllowedIds($entity[1]));
			}
		}

		return [
			'boards' => array_unique($boards),
			'topics' => array_unique($topics),
		];
	}

	protected function getAllowedIds(string $entity = ''): array
	{
		$ids = [];

		if (empty($entity)) {
			return [];
		}

		$items = explode('|', $entity);
		foreach ($items as $item) {
			$item = trim($item);
			if (empty($item)) {
				continue;
			}

			if (str_contains($item, '-')) {
				[$start, $end] = explode('-', $item);
				$start = (int) $start;
				$end   = (int) $end;
				$step  = $start <= $end ? 1 : -1;

				for ($i = $start; $i != $end + $step; $i += $step) {
					$ids[] = $i;
				}
			} else {
				$ids[] = (int) $item;
			}
		}

		return $ids;
	}
}
