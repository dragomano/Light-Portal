<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\UI\Partials;

use Bugo\Compat\{Lang, Utils};

use function array_combine;
use function array_merge;
use function explode;
use function json_encode;
use function str_replace;
use function str_starts_with;

final class AreaSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params['data'] = [
			'all'    => 'all',
			'portal' => 'portal',
			'home'   => 'home',
			'forum'  => 'forum',
			'pages'  => 'pages',
			'boards' => 'boards',
			'topics' => 'topics',
		];

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

		$params['value'] = explode(',', (string) Utils::$context['lp_block']['areas']);
		$params['data']  = array_merge($params['data'], array_combine($params['value'], $params['value']));

		$data = $values = [];
		foreach ($params['data'] as $value => $text) {
			if (in_array($value, $params['value']))
				$values[] = $value;

			$text = str_replace('!', '', $text);

			if (str_starts_with($value, 'board='))
				$text = Lang::$txt['board'] . str_replace('board=', ' ', $text);

			if (str_starts_with($value, 'topic='))
				$text = Lang::$txt['topic'] . str_replace('topic=', ' ', $text);

			if (str_starts_with($value, 'page='))
				$text = Lang::$txt['page'] . str_replace('page=', ' ', $text);

			$data[] = [
				'label' => Lang::$txt['lp_block_areas_set'][$text] ?? Lang::$txt[$text] ?? $text,
				'value' => $value
			];
		}

		return /** @lang text */ '
		<div id="areas" name="areas"></div>
		<script>
			VirtualSelect.init({
				ele: "#areas",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				maxWidth: "100%",
				multiple: true,
				search: true,
				markSearchResults: true,
				showValueAsTags: true,
				allowNewOption: true,
				showSelectedOptionsFirst: true,
				placeholder: "' . Lang::$txt['lp_block_areas_subtext'] . '",
				noSearchResultsText: "' . Lang::$txt['no_matches'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
				clearButtonText: "' . Lang::$txt['remove'] . '",
				options: ' . json_encode($data) . ',
				selectedValue: ' . json_encode($values) . '
			});
		</script>';
	}
}
