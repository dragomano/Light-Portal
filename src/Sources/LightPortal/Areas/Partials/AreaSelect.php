<?php declare(strict_types=1);

/**
 * AreaSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Areas\Partials;

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

		$this->txt['lp_block_areas_set']['pm'] = $this->txt['personal_messages'];
		$this->txt['lp_block_areas_set']['mlist'] = $this->txt['members_title'];
		$this->txt['lp_block_areas_set']['recent'] = $this->txt['recent_posts'];
		$this->txt['lp_block_areas_set']['unread'] = $this->txt['view_unread_category'];
		$this->txt['lp_block_areas_set']['unreadreplies'] = $this->txt['unread_replies'];
		$this->txt['lp_block_areas_set']['stats'] = $this->txt['forum_stats'];
		$this->txt['lp_block_areas_set']['who'] = $this->txt['who_title'];
		$this->txt['lp_block_areas_set']['agreement'] = $this->txt['terms_and_rules'];
		$this->txt['lp_block_areas_set']['warehouse'] = $this->txt['warehouse_title'] ?? 'warehouse';
		$this->txt['lp_block_areas_set']['media'] = $this->txt['levgal'] ?? $this->txt['mgallery_title'] ?? 'media';
		$this->txt['lp_block_areas_set']['gallery'] = $this->txt['smfgallery_menu'] ?? 'gallery';

		$params['value'] = explode(',', $this->context['lp_block']['areas']);
		$params['data'] = array_merge($params['data'], array_combine($params['value'], $params['value']));

		$data = $values = [];
		foreach ($params['data'] as $value => $text) {
			if (in_array($value, $params['value']))
				$values[] = $value;

			$text = str_replace('!', '', $text);

			if (str_starts_with($value, 'board='))
				$text = $this->txt['board'] . str_replace('board=', ' ', $text);

			if (str_starts_with($value, 'topic='))
				$text = $this->txt['topic'] . str_replace('topic=', ' ', $text);

			if (str_starts_with($value, 'page='))
				$text = $this->txt['page'] . str_replace('page=', ' ', $text);

			$data[] = [
				'label' => $this->txt['lp_block_areas_set'][$text] ?? $this->txt[$text] ?? $text,
				'value' => $value
			];
		}

		return /** @lang text */ '
		<div id="areas" name="areas"></div>
		<script>
			VirtualSelect.init({
				ele: "#areas",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				maxWidth: "100%",
				multiple: true,
				search: true,
				markSearchResults: true,
				showValueAsTags: true,
				allowNewOption: true,
				showSelectedOptionsFirst: true,
				placeholder: "' . $this->txt['lp_block_areas_subtext'] . '",
				noSearchResultsText: "' . $this->txt['no_matches'] . '",
				searchPlaceholderText: "' . $this->txt['search'] . '",
				clearButtonText: "' . $this->txt['remove'] . '",
				options: ' . json_encode($data) . ',
				selectedValue: ' . json_encode($values) . '
			});
		</script>';
	}
}
