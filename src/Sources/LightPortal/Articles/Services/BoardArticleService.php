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

readonly class BoardArticleService implements ArticleServiceInterface
{
	public function __construct(
		private BoardArticleQuery $query,
		private EventDispatcherInterface $events
	) {}

	public function init(): void
	{
		$params = [
			'current_member'  => User::$me->id,
			'recycle_board'   => Setting::get('recycle_board', 'int', 0),
			'selected_boards' => Setting::get('lp_frontpage_boards', 'array', []),
		];

		$this->query->init($params);
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

	public function getData(int $start, int $limit, ?string $sortType): iterable
	{
		$this->query->setSorting($sortType);
		$this->query->prepareParams($start, $limit);

		foreach ($this->query->getRawData() as $row) {
			$row['description'] = BBCodeParser::load()->parse(
				$row['description'], false, '', Utils::$context['description_allowed_tags']
			);

			$board = [
				'id'           => $row['id_board'],
				'date'         => $this->getDate($row),
				'last_comment' => $row['id_last_msg'],
				'title'        => $this->getTitle($row),
				'link'         => $this->getLink($row),
				'is_new'       => empty($row['is_read']),
				'replies'      => $this->getRepliesData($row),
				'image'        => $this->getImage($row),
				'can_edit'     => $this->canEdit(),
				'edit_link'    => $this->getEditLink($row),
				'category'     => $this->getCategory($row),
				'is_redirect'  => $row['is_redirect'],
			];

			$this->prepareTeaser($board, $row);

			$articles = [$row['id_board'] => $board];

			$this->events->dispatch(PortalHook::frontBoardsRow, ['articles' => &$articles, 'row' => $row]);

			$board = $articles[$row['id_board']];

			yield $row['id_board'] => $board;
		}
	}

	public function getTotalCount(): int
	{
		return $this->query->getTotalCount();
	}

	private function getDate(array $row): int
	{
		return str_contains($this->query->getSorting(), 'updated') && $row['last_updated']
			? $row['last_updated']
			: $row['poster_time'];
	}

	private function getTitle(array $row): string
	{
		return BBCodeParser::load()->parse($row['name'], false, '', Utils::$context['description_allowed_tags']);
	}

	private function getLink(array $row): string
	{
		return $row['is_redirect']
			? $row['redirect'] . '" rel="nofollow noopener'
			: (Config::$scripturl . '?board=' . $row['id_board'] . '.0');
	}

	private function getRepliesData(array $row): array
	{
		return [
			'num'   => $row['num_posts'],
			'title' => Lang::$txt['lp_replies'],
			'after' => '',
		];
	}

	private function getImage(array $row): string
	{
		if (empty(Config::$modSettings['lp_show_images_in_articles']))
			return '';

		$image = Str::getImageFromText($row['description']);

		if ($row['attach_id'] && empty($image)) {
			$image = Config::$scripturl . '?action=dlattach;topic=' . $row['id_topic'] . ';attach='
				. $row['attach_id'] . ';image';
		}

		if ($row['is_redirect'] && empty($image)) {
			$image = 'https://mini.s-shot.ru/300x200/JPEG/300/Z100/?' . urlencode(trim($row['redirect']));
		}

		return $image;
	}

	private function canEdit(): bool
	{
		return User::$me->is_admin || User::$me->allowedTo('manage_boards');
	}

	private function getEditLink(array $row): string
	{
		return Config::$scripturl . '?action=admin;area=manageboards;sa=board;boardid=' . $row['id_board'];
	}

	private function getCategory(array $row): string
	{
		return BBCodeParser::load()->parse($row['cat_name'], false, '', Utils::$context['description_allowed_tags']);
	}

	private function prepareTeaser(array &$board, array $row): void
	{
		if (empty(Config::$modSettings['lp_show_teaser']) || empty($row['description']))
			return;

		$board['teaser'] = Str::getTeaser($row['description']);
	}
}
