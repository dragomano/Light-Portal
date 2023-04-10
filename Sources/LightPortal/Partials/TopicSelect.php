<?php declare(strict_types=1);

/**
 * TopicSelect.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Partials;

final class TopicSelect extends AbstractPartial
{
	public function __invoke(array $params = []): string
	{
		$params['id'] ??= 'lp_frontpage_topics';
		$params['value'] ??= $this->modSettings['lp_frontpage_topics'] ?? '';
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
				ele: "#' . $params['id'] . '",' . ($this->context['right_to_left'] ? '
				textDirection: "rtl",' : '') . '
				dropboxWrapper: "body",
				multiple: true,
				search: true,
				markSearchResults: true,
				placeholder: "' . ($params['hint'] ?? $this->txt['lp_frontpage_topics_select']) . '",
				noSearchResultsText: "' . $this->txt['no_matches'] . '",
				searchPlaceholderText: "' . $this->txt['search'] . '",
				allOptionsSelectedText: "' . $this->txt['all'] . '",
				noOptionsText: "' . $this->txt['lp_frontpage_topics_no_items'] . '",
				moreText: "' . $this->txt['post_options'] . '",
				showValueAsTags: true,
				maxWidth: "100%",
				options: ' . json_encode($data) . ',
				selectedValue: [' . $params['value'] . '],
				onServerSearch: async function (search, virtualSelect) {
					fetch("' . $this->context['canonical_url'] . ';topic_by_subject", {
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
							data.push({label: json[i].subject, value: json[i].id})
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

		$request = $this->smcFunc['db_query']('', '
			SELECT t.id_topic, m.subject
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			WHERE t.id_topic IN ({array_int:topics})',
			[
				'topics' => explode(',', $topics),
			]
		);

		$topics = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$this->censorText($row['subject']);

			$topics[$row['id_topic']] = $row['subject'];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $topics;
	}
}
