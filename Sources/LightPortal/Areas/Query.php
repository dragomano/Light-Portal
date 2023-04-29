<?php declare(strict_types=1);

/**
 * Query.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.1
 */

namespace Bugo\LightPortal\Areas;

use Bugo\LightPortal\Lists\IconList;

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

		$all_icons = $this->getFaIcons();
		$template = '<i class="%1$s fa-fw" aria-hidden="true"></i>&nbsp;%1$s';

		$this->hook('prepareIconList', [&$all_icons, &$template]);

		$all_icons = array_filter($all_icons, fn($item) => str_contains($item, $search));

		$results = [];
		foreach ($all_icons as $icon) {
			$results[] = [
				'innerHTML' => sprintf($template, $icon),
				'value'     => $icon
			];
		}

		exit(json_encode($results));
	}

	private function getFaIcons(): array
	{
		if (($icons = $this->cache()->get('all_icons', 30 * 24 * 60 * 60)) === null) {
			$icons = (new IconList)->getList();

			$this->cache()->put('all_icons', $icons, 30 * 24 * 60 * 60);
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

		$request = $this->smcFunc['db_query']('', '
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
				'recycle_board'     => empty($this->modSettings['recycle_board']) ? $this->modSettings['recycle_board'] : 0,
				'subject'           => trim($this->smcFunc['strtolower']($search)),
			]
		);

		$topics = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$this->censorText($row['subject']);

			$topics[] = [
				'id'      => $row['id_topic'],
				'subject' => $row['subject'],
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		exit(json_encode($topics));
	}

	private function prepareMemberList(): void
	{
		if ($this->request()->hasNot('members'))
			return;

		$data = $this->request()->json();

		if (empty($search = $data['search']))
			return;

		$search = trim($this->smcFunc['strtolower']($search)) . '*';
		$search = strtr($search, ['%' => '\%', '_' => '\_', '*' => '%', '?' => '_', '&#038;' => '&amp;']);

		$request = $this->smcFunc['db_query']('', '
			SELECT id_member, real_name
			FROM {db_prefix}members
			WHERE {raw:real_name} LIKE {string:search}
				AND is_activated IN (1, 11)
			LIMIT 1000',
			[
				'real_name' => $this->smcFunc['db_case_sensitive'] ? 'LOWER(real_name)' : 'real_name',
				'search'    => $search,
			]
		);

		$members = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$row['real_name'] = strtr($row['real_name'], ['&amp;' => '&#038;', '&lt;' => '&#060;', '&gt;' => '&#062;', '&quot;' => '&#034;']);

			$members[] = [
				'text'  => $row['real_name'],
				'value' => $row['id_member'],
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		exit(json_encode($members));
	}
}
