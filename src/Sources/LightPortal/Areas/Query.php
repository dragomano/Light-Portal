<?php declare(strict_types=1);

/**
 * Query.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas;

use Bugo\LightPortal\Lists\IconList;
use Bugo\Compat\{Config, Database as Db, Lang, Utils};

if (! defined('SMF'))
	die('No direct access...');

trait Query
{
	private function prepareIconList(): void
	{
		if ($this->request()->hasNot('icons'))
			return;

		$data = $this->request()->json();

		if (empty($search = $data['search']))
			return;

		$search = trim(strtolower($search));

		$icons = $this->getFaIcons();
		$template = '<i class="%1$s fa-fw" aria-hidden="true"></i>&nbsp;%1$s';

		$this->hook('prepareIconList', [&$icons, &$template]);

		$icons = array_filter($icons, fn($item) => str_contains($item, $search));

		$results = [];
		foreach ($icons as $icon) {
			$results[] = [
				'innerHTML' => sprintf($template, $icon),
				'value'     => $icon
			];
		}

		exit(json_encode($results));
	}

	private function getFaIcons(): array
	{
		if (($icons = $this->cache()->get('fa_icon_list', 30 * 24 * 60 * 60)) === null) {
			$icons = (new IconList())->getList();

			$this->cache()->put('fa_icon_list', $icons, 30 * 24 * 60 * 60);
		}

		return $icons;
	}

	private function prepareTopicList(): void
	{
		if ($this->request()->hasNot('topic_by_subject'))
			return;

		$data = $this->request()->json();

		if (empty($search = $data['search']))
			return;

		$result = Db::$db->query('', '
			SELECT t.id_topic, m.subject
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			WHERE t.id_poll = {int:id_poll}
				AND t.approved = {int:is_approved}
				AND t.id_redirect_topic = {int:id_redirect_topic}
				AND t.id_board != {int:recycle_board}
				AND INSTR(LOWER(m.subject), {string:subject}) > 0
			ORDER BY m.subject
			LIMIT 100',
			[
				'id_poll'           => 0,
				'is_approved'       => 1,
				'id_redirect_topic' => 0,
				'recycle_board'     => empty(Config::$modSettings['recycle_board']) ? Config::$modSettings['recycle_board'] : 0,
				'subject'           => trim(Utils::$smcFunc['strtolower']($search)),
			]
		);

		$topics = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['subject']);

			$topics[] = [
				'id'      => $row['id_topic'],
				'subject' => $row['subject'],
			];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		exit(json_encode($topics));
	}

	private function prepareMemberList(): void
	{
		if ($this->request()->hasNot('members'))
			return;

		$data = $this->request()->json();

		if (empty($search = $data['search']))
			return;

		$search = trim(Utils::$smcFunc['strtolower']($search)) . '*';
		$search = strtr($search, ['%' => '\%', '_' => '\_', '*' => '%', '?' => '_', '&#038;' => '&amp;']);

		$result = Db::$db->query('', '
			SELECT id_member, real_name
			FROM {db_prefix}members
			WHERE {raw:real_name} LIKE {string:search}
				AND is_activated IN (1, 11)
			LIMIT 1000',
			[
				'real_name' => Utils::$smcFunc['db_case_sensitive'] ? 'LOWER(real_name)' : 'real_name',
				'search'    => $search,
			]
		);

		$members = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$row['real_name'] = strtr($row['real_name'], ['&amp;' => '&#038;', '&lt;' => '&#060;', '&gt;' => '&#062;', '&quot;' => '&#034;']);

			$members[] = [
				'text'  => $row['real_name'],
				'value' => $row['id_member'],
			];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		exit(json_encode($members));
	}
}
