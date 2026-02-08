<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Articles\Services;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Parsers\BBCodeParser;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Articles\Queries\BoardArticleQuery;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class BoardArticleService extends AbstractArticleService
{
	public function __construct(BoardArticleQuery $query, EventDispatcherInterface $dispatcher)
	{
		parent::__construct($query, $dispatcher);
	}

	public function getParams(): array
	{
		return [
			'current_member'  => User::$me->id,
			'recycle_board'   => Setting::get('recycle_board', 'int', 0),
			'selected_boards' => Setting::get('lp_frontpage_boards', 'array', []),
		];
	}

	public function getSortingOptions(): array
	{
		return [
			'created;desc'      => Lang::$txt['lp_sort_by_created_desc'],
			'created'           => Lang::$txt['lp_sort_by_created'],
			'updated;desc'      => Lang::$txt['lp_sort_by_updated_desc'],
			'updated'           => Lang::$txt['lp_sort_by_updated'],
			'last_comment;desc' => Lang::$txt['lp_sort_by_last_reply_desc'],
			'last_comment'      => Lang::$txt['lp_sort_by_last_reply'],
			'title;desc'        => Lang::$txt['lp_sort_by_title_desc'],
			'title'             => Lang::$txt['lp_sort_by_title'],
			'num_replies;desc'  => Lang::$txt['lp_sort_by_num_replies_desc'],
			'num_replies'       => Lang::$txt['lp_sort_by_num_replies'],
		];
	}

	protected function getRules(array $row): array
	{
		Lang::censorText($row['name']);
		Lang::censorText($row['cat_name']);
		Lang::censorText($row['description']);

		$description = BBCodeParser::load()->parse(
			$row['description'], false, '', Utils::$context['description_allowed_tags']
		);

		return [
			'id' => fn($row) => $row['id_board'],

			'date' => fn($row) => str_contains($this->query->getSorting(), 'updated') && $row['last_updated']
				? $row['last_updated']
				: $row['poster_time'],

			'last_comment' => fn($row) => $row['id_last_msg'],

			'title' => fn($row) => $row['name'],

			'link' => fn($row) => $row['is_redirect']
				? $row['redirect'] . '" rel="nofollow noopener'
				: (Config::$scripturl . '?board=' . $row['id_board'] . '.0'),

			'is_new' => fn($row) => empty($row['is_read']),

			'replies' => fn($row) => [
				'num'   => $row['num_posts'],
				'title' => Lang::$txt['lp_replies'],
				'after' => '',
			],

			'image' => function ($row) use ($description) {
				if (empty(Config::$modSettings['lp_show_images_in_articles'])) {
					return '';
				}

				$image = Str::getImageFromText($description);

				if ($row['attach_id'] && empty($image)) {
					$image = Config::$scripturl . '?action=dlattach;topic=' . $row['id_topic'] . ';attach='
						. $row['attach_id'] . ';image';
				}

				if ($row['is_redirect'] && empty($image)) {
					$image = 'https://image.thum.io/get/' . trim($row['redirect']);
				}

				return $image;
			},

			'can_edit' => fn($row) => User::$me->is_admin || User::$me->allowedTo('manage_boards'),

			'edit_link' => fn($row) => Config::$scripturl
				. '?action=admin;area=manageboards;sa=board;boardid=' . $row['id_board'],

			'category' => fn($row) => $row['cat_name'],

			'is_redirect' => fn($row) => $row['is_redirect'],

			'teaser' => function () use ($description) {
				if (empty(Config::$modSettings['lp_show_teaser'])) {
					return '';
				}

				return Str::getTeaser($description);
			},
		];
	}

	protected function getEventHook(): PortalHook
	{
		return PortalHook::frontBoardsRow;
	}

	protected function finalizeItem(array $item): array
	{
		return $item;
	}
}
