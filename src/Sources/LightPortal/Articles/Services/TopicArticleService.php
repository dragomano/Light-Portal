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
use LightPortal\Articles\Queries\TopicArticleQuery;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Utils\Avatar;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class TopicArticleService extends AbstractArticleService
{
	public function __construct(TopicArticleQuery $query, EventDispatcherInterface $dispatcher)
	{
		parent::__construct($query, $dispatcher);
	}

	public function getParams(): array
	{
		return [
			'current_member'    => User::$me->id,
			'is_approved'       => 1,
			'id_poll'           => 0,
			'id_redirect_topic' => 0,
			'attachment_type'   => 0,
			'recycle_board'     => Setting::get('recycle_board', 'int', 0),
			'selected_boards'   => Setting::get('lp_frontpage_boards', 'array', []),
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
			'author_name;desc'  => Lang::$txt['lp_sort_by_author_desc'],
			'author_name'       => Lang::$txt['lp_sort_by_author'],
			'num_views;desc'    => Lang::$txt['lp_sort_by_num_views_desc'],
			'num_views'         => Lang::$txt['lp_sort_by_num_views'],
			'num_replies;desc'  => Lang::$txt['lp_sort_by_num_replies_desc'],
			'num_replies'       => Lang::$txt['lp_sort_by_num_replies'],
		];
	}

	protected function getRules(array $row): array
	{
		$body = $row['body'];

		Lang::censorText($body);

		$body = preg_replace('~\[spoiler.*].*?\[/spoiler]~Usi', '', $body);
		$body = preg_replace('~\[quote.*].*?\[/quote]~Usi', '', $body);
		$body = preg_replace('~\[table.*].*?\[/table]~Usi', '', $body);
		$body = preg_replace('~\[code.*].*?\[/code]~Usi', '', $body);

		$parsedBody = BBCodeParser::load()->parse($body, (bool) $row['smileys_enabled'], (int) $row['id_first_msg']);

		return [
			'id' => fn($row) => $row['id_topic'],

			'section' => fn($row) => [
				'name' => $row['name'],
				'link' => Config::$scripturl . '?board=' . $row['id_board'] . '.0',
			],

			'author' => fn($row) => [
				'id'   => $row['id_member'],
				'link' => Config::$scripturl . '?action=profile;u=' . $row['id_member'],
				'name' => $row['poster_name'],
			],

			'date' => function($row) {
				if (str_contains($this->query->getSorting(), 'updated')) {
					return $row['date'];
				}

				return $row['poster_time'];
			},

			'last_comment' => fn($row) => $row['last_msg_time'],

			'title' => function ($row) {
				$title = $row['subject'];

				Lang::censorText($title);
				Str::cleanBbcode($title);

				return $title;
			},

			'link' => fn($row) => Config::$scripturl . '?topic=' . $row['id_topic'] . '.0',

			'is_new' => function ($row) {
				if (User::$me->is_guest || empty($row['new_from'])) {
					return false;
				}

				return $row['new_from'] <= $row['id_msg_modified'] && $row['last_poster_id'] !== User::$me->id;
			},

			'views' => fn($row) => [
				'num'   => $row['num_views'],
				'title' => Lang::$txt['lp_views'],
				'after' => '',
			],

			'replies' => fn($row) => [
				'num'   => $row['num_replies'],
				'title' => Lang::$txt['lp_replies'],
				'after' => '',
			],

			'css_class' => fn($row) => empty($row['is_sticky']) ? '' : ' sticky',

			'image' => function ($row) use ($parsedBody) {
				if (empty(Config::$modSettings['lp_show_images_in_articles'])) {
					return '';
				}

				$image = Str::getImageFromText($parsedBody);

				if (! empty($row['id_attach']) && empty($image)) {
					return Config::$scripturl . '?topic=' . $row['id_topic'] . ';attach=' . $row['id_attach'] . ';image';
				}

				return $image;
			},

			'can_edit' => fn($row) => User::$me->is_admin
				|| (! User::$me->is_guest && $row['id_member'] === User::$me->id),

			'edit_link' => fn($row) => Config::$scripturl
				. '?action=post;msg=' . $row['id_first_msg'] . ';topic=' . $row['id_topic'] . '.0',

			'teaser' => function() use ($parsedBody) {
				if (empty(Config::$modSettings['lp_show_teaser'])) {
					return '';
				}

				return Str::getTeaser($parsedBody);
			},
		];
	}

	protected function getEventHook(): PortalHook
	{
		return PortalHook::frontTopicsRow;
	}

	protected function finalizeItem(array $item): array
	{
		return Avatar::getWithItems([$item])[0] ?? $item;
	}
}
