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

namespace Bugo\LightPortal\Areas\Traits;

use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\EventManagerFactory;
use Bugo\LightPortal\Lists\IconList;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Utils\CacheTrait;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;

use function array_filter;
use function json_encode;
use function sprintf;
use function str_contains;
use function strtolower;
use function strtr;
use function trim;

if (! defined('SMF'))
	die('No direct access...');

trait QueryTrait
{
	use CacheTrait;
	use RequestTrait;

	private function prepareIconList(): void
	{
		if ($this->request()->hasNot('icons'))
			return;

		$data = $this->request()->json();

		if (empty($search = trim(strtolower((string) $data['search']))))
			return;

		$icons = $this->getFaIcons();
		$template = Str::html('i', ['class' => '%1$s fa-fw'])
			->setAttribute('aria-hidden', 'true') . '&nbsp;%1$s';

		(new EventManagerFactory())()->dispatch(
			PortalHook::prepareIconList,
			new Event(new class ($icons, $template) {
				public function __construct(public array &$icons, public string &$template) {}
			})
		);

		$icons = array_filter($icons, static fn($item) => str_contains((string) $item, $search));

		$results = [];
		foreach ($icons as $icon) {
			$results[] = [
				'innerHTML' => sprintf($template, $icon),
				'value'     => $icon,
			];
		}

		exit(json_encode($results));
	}

	private function getFaIcons(): array
	{
		$cacheTTL = 30 * 24 * 60 * 60;

		if (($icons = $this->cache()->get('fa_icon_list', $cacheTTL)) === null) {
			$icons = (new IconList())->getList();

			$this->cache()->put('fa_icon_list', $icons, $cacheTTL);
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
				'recycle_board'     => Setting::get('recycle_board', 'int', 0),
				'subject'           => trim((string) Utils::$smcFunc['strtolower']($search)),
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

		exit(json_encode($topics));
	}

	private function prepareMemberList(): void
	{
		if ($this->request()->hasNot('members'))
			return;

		$data = $this->request()->json();

		if (empty($search = $data['search']))
			return;

		$search = trim((string) Utils::$smcFunc['strtolower']($search)) . '*';
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
			$row['real_name'] = strtr(
				$row['real_name'], ['&amp;' => '&#038;', '&lt;' => '&#060;', '&gt;' => '&#062;', '&quot;' => '&#034;']
			);

			$members[] = [
				'text'  => $row['real_name'],
				'value' => $row['id_member'],
			];
		}

		Db::$db->free_result($result);

		exit(json_encode($members));
	}
}
