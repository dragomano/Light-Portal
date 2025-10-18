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

namespace LightPortal\UI\Partials;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;

if (! defined('SMF'))
	die('No direct access...');

final class AreaSelect extends AbstractSelect
{
	private const BASE_AREAS = [
		'all'    => 'all',
		'portal' => 'portal',
		'home'   => 'home',
		'forum'  => 'forum',
		'pages'  => 'pages',
		'boards' => 'boards',
		'topics' => 'topics',
	];

	public function getData(): array
	{
		Lang::$txt['lp_block_areas_set']['pm'] = Lang::$txt['personal_messages'];
		Lang::$txt['lp_block_areas_set']['mlist'] = Lang::$txt['members_title'];
		Lang::$txt['lp_block_areas_set']['recent'] = Lang::$txt['recent_posts'];
		Lang::$txt['lp_block_areas_set']['unread'] = Lang::$txt['view_unread_category'];
		Lang::$txt['lp_block_areas_set']['unreadreplies'] = Lang::$txt['unread_replies'];
		Lang::$txt['lp_block_areas_set']['stats'] = Lang::$txt['forum_stats'];
		Lang::$txt['lp_block_areas_set']['who'] = Lang::$txt['who_title'];
		Lang::$txt['lp_block_areas_set']['agreement'] = Lang::$txt['terms_and_rules'];
		Lang::$txt['lp_block_areas_set']['warehouse'] = Lang::$txt['warehouse_title'] ?? 'warehouse';
		Lang::$txt['lp_block_areas_set']['media'] = Lang::$txt['levgal'] ?? Lang::$txt['mgallery_title'] ?? 'media';
		Lang::$txt['lp_block_areas_set']['gallery'] = Lang::$txt['smfgallery_menu'] ?? 'gallery';

		$value = $this->params['value'] ?? [];
		$allData = array_merge(self::BASE_AREAS, array_combine($value, $value));

		$data = [];
		foreach ($allData as $value => $text) {
			$displayText = str_replace('!', '', $text);

			if (str_starts_with($value, 'board=')) {
				$displayText = Lang::$txt['board'] . str_replace('board=', ' ', $displayText);
			}

			if (str_starts_with($value, 'topic=')) {
				$displayText = Lang::$txt['topic'] . str_replace('topic=', ' ', $displayText);
			}

			if (str_starts_with($value, 'page=')) {
				$displayText = Lang::$txt['page'] . str_replace('page=', ' ', $displayText);
			}

			$data[] = [
				'label' => Lang::$txt['lp_block_areas_set'][$displayText] ?? Lang::$txt[$displayText] ?? $displayText,
				'value' => $value,
			];
		}

		return $data;
	}

	protected function getDefaultParams(): array
	{
		return array_merge(['showSelectedOptionsFirst' => true], [
			'id'       => 'areas',
			'multiple' => true,
			'wide'     => true,
			'allowNew' => true,
			'hint'     => Lang::$txt['lp_block_areas_subtext'],
			'value'    => $this->normalizeValue(Utils::$context['lp_block']['areas'] ?? ''),
		]);
	}

	protected function normalizeValue(mixed $value): array
	{
		return array_map(
			static fn($item) => preg_replace('/=(-)(?=\d)/', '=', $item),
			parent::normalizeValue($value)
		);
	}
}
