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

namespace Bugo\LightPortal\Areas\Traits;

use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Events\HasEvents;
use Bugo\LightPortal\Lists\IconList;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasCache;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Bugo\LightPortal\Utils\Traits\HasResponse;

use function array_filter;
use function sprintf;
use function str_contains;
use function strtolower;
use function trim;

if (! defined('SMF'))
	die('No direct access...');

trait HasQuery
{
	use HasCache;
	use HasEvents;
	use HasRequest;
	use HasResponse;

	private function prepareIconList(): void
	{
		if ($this->request()->hasNot('icons'))
			return;

		$data = $this->request()->json();

		if (empty($search = trim(strtolower((string) $data['search']))))
			return;

		$icons = $this->getFaIcons();
		$template = Str::html('i', ['class' => '%1$s'])
			->setAttribute('aria-hidden', 'true') . '&nbsp;%1$s';

		$this->events()->dispatch(PortalHook::prepareIconList, ['icons' => &$icons, 'template' => &$template]);

		$icons = array_filter($icons, static fn($item) => str_contains((string) $item, $search));

		$results = [];
		foreach ($icons as $icon) {
			$results[] = [
				'innerHTML' => sprintf($template, $icon),
				'value'     => $icon,
			];
		}

		$this->response()->exit($results);
	}

	private function getFaIcons(): array
	{
		$cacheTTL = 30 * 24 * 60 * 60;

		if (($icons = $this->cache()->get('fa_icon_list', $cacheTTL)) === null) {
			$icons = app(IconList::class)->getList();

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

		$this->response()->exit($topics);
	}
}
