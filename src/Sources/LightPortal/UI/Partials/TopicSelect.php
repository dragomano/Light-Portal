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

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;

use function explode;
use function func_get_args;
use function json_encode;

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
					fetch("' . Utils::$context['form_action'] . ';topic_by_subject", {
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
		if ($topics === '')
			return [];

		$result = Db::$db->query('', '
			SELECT t.id_topic, m.subject
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			WHERE t.id_topic IN ({array_int:topics})
				AND t.approved = {int:is_approved}
				AND t.id_poll = {int:id_poll}
				AND t.id_redirect_topic = {int:id_redirect_topic}
				AND {query_wanna_see_board}',
			[
				'topics'            => explode(',', $topics),
				'is_approved'       => 1,
				'id_poll'           => 0,
				'id_redirect_topic' => 0,
			]
		);

		$topics = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['subject']);

			$topics[$row['id_topic']] = $row['subject'];
		}

		Db::$db->free_result($result);

		return $topics;
	}
}
