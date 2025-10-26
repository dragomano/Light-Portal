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

namespace LightPortal\Areas\Traits;

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Enums\PortalHook;
use LightPortal\Lists\IconList;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasCache;
use LightPortal\Utils\Traits\HasPortalSql;
use LightPortal\Utils\Traits\HasRequest;
use LightPortal\Utils\Traits\HasResponse;
use Laminas\Db\Sql\Predicate\Expression;

use function LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

trait HasQuery
{
	use HasCache;
	use HasPortalSql;
	use HasRequest;
	use HasResponse;

	protected function prepareIconList(): void
	{
		if ($this->request()->hasNot('icons'))
			return;

		$data = $this->request()->json();

		if (empty($search = trim(strtolower((string) $data['search']))))
			return;

		$icons = $this->getFaIcons();
		$template = Str::html('i', ['class' => '%1$s'])
			->setAttribute('aria-hidden', 'true') . '&nbsp;%1$s';

		$this->dispatcher->dispatch(PortalHook::prepareIconList, ['icons' => &$icons, 'template' => &$template]);

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

	protected function getFaIcons(): array
	{
		$cacheTTL = 30 * 24 * 60 * 60;

		if (($icons = $this->cache()->get('fa_icon_list', $cacheTTL)) === null) {
			$icons = app(IconList::class)->getList();

			$this->cache()->put('fa_icon_list', $icons, $cacheTTL);
		}

		return $icons;
	}

	protected function prepareTopicList(): void
	{
		if ($this->request()->hasNot('topic_by_subject'))
			return;

		$data = $this->request()->json();

		if (empty($search = $data['search']))
			return;

		$select = $this->getPortalSql()->select()
			->from(['t' => 'topics'])
			->columns(['id_topic'])
			->join(['m' => 'messages'], 'm.id_msg = t.id_first_msg', ['subject'])
			->where([
				't.id_poll = ?'           => 0,
				't.approved = ?'          => 1,
				't.id_redirect_topic = ?' => 0,
				't.id_board != ?'         => Setting::get('recycle_board', 'int', 0),
			])
			->where(new Expression(
				'LOWER(m.subject) LIKE ?',
				['%' . trim(Utils::$smcFunc['strtolower']($search)) . '%']
			))
			->order('m.subject')
			->limit(100);

		$result = $this->getPortalSql()->execute($select);

		$topics = [];
		foreach ($result as $row) {
			Lang::censorText($row['subject']);

			$topics[] = [
				'id'      => $row['id_topic'],
				'subject' => $row['subject'],
			];
		}

		$this->response()->exit($topics);
	}
}
