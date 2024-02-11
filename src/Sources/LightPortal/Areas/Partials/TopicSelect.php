<?php declare(strict_types=1);

/**
 * TopicSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas\Partials;

use Bugo\Compat\{Config, Database as Db, Lang, Utils};

final class TopicSelect extends AbstractPartial
{
	public function __invoke(): string
	{
		$params = func_get_args();
		$params = $params[0] ?? [];

		$params['id'] ??= 'lp_frontpage_topics';
		$params['value'] ??= Config::$modSettings['lp_frontpage_topics'] ?? '';
		$params['data'] ??= $this->getSelectedTopics($params['value']);

		$data = [];
		foreach ($params['data'] as $id => $topic) {
			$data[] = [
				'label' => $topic,
				'value' => $id
			];
		}

		return /** @lang text */ '
		<div id="' . $params['id'] . '" name="' . $params['id'] . '"></div>
		<script>
			VirtualSelect.init({
				ele: "#' . $params['id'] . '",' . (Utils::$context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				multiple: true,
				search: true,
				markSearchResults: true,
				placeholder: "' . ($params['hint'] ?? Lang::$txt['lp_frontpage_topics_select']) . '",
				noSearchResultsText: "' . Lang::$txt['no_matches'] . '",
				searchPlaceholderText: "' . Lang::$txt['search'] . '",
				allOptionsSelectedText: "' . Lang::$txt['all'] . '",
				noOptionsText: "' . Lang::$txt['lp_frontpage_topics_no_items'] . '",
				moreText: "' . Lang::$txt['post_options'] . '",
				showValueAsTags: true,
				maxWidth: "100%",
				options: ' . json_encode($data) . ',
				selectedValue: [' . $params['value'] . '],
				onServerSearch: async function (search, virtualSelect) {
					fetch("' . Utils::$context['canonical_url'] . ';topic_by_subject", {
						method: "POST",
						headers: {
							"Content-Type": "application/json; charset=utf-8"
						},
						body: JSON.stringify({
							search
						})
					})
					.then(response => response.json())
					.then(function (json) {
						let data = [];
						for (let i = 0; i < json.length; i++) {
							data.push({ label: json[i].subject, value: json[i].id })
						}

						virtualSelect.setServerOptions(data)
					})
					.catch(function (error) {
						virtualSelect.setServerOptions(false)
					})
				}
			});
		</script>';
	}

	private function getSelectedTopics(string $topics): array
	{
		if (empty($topics))
			return [];

		$result = Db::$db->query('', '
			SELECT t.id_topic, m.subject
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			WHERE t.id_topic IN ({array_int:topics})',
			[
				'topics' => explode(',', $topics),
			]
		);

		$topics = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['subject']);

			$topics[$row['id_topic']] = $row['subject'];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $topics;
	}
}
