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
use LightPortal\Articles\Queries\TopicArticleQuery;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Utils\Avatar;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

readonly class TopicArticleService implements ArticleServiceInterface
{
	public function __construct(
		private TopicArticleQuery $query,
		private EventDispatcherInterface $events
	) {}

	public function init(): void
	{
		$params = [
			'current_member'    => User::$me->id,
			'is_approved'       => 1,
			'id_poll'           => 0,
			'id_redirect_topic' => 0,
			'attachment_type'   => 0,
			'recycle_board'     => Setting::get('recycle_board', 'int', 0),
			'selected_boards'   => Setting::get('lp_frontpage_boards', 'array', []),
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
			'author_name;desc'  => Lang::$txt['lp_sort_by_author_desc'],
			'author_name'       => Lang::$txt['lp_sort_by_author'],
			'num_views;desc'    => Lang::$txt['lp_sort_by_num_views_desc'],
			'num_views'         => Lang::$txt['lp_sort_by_num_views'],
			'num_replies;desc'  => Lang::$txt['lp_sort_by_num_replies_desc'],
			'num_replies'       => Lang::$txt['lp_sort_by_num_replies'],
		];
	}

	public function getData(int $start, int $limit, ?string $sortType): iterable
	{
		$this->query->setSorting($sortType);
		$this->query->prepareParams($start, $limit);

		foreach ($this->query->getRawData() as $row) {
			$topic = [
				'id'           => $row['id_topic'],
				'section'      => $this->getSectionData($row),
				'author'       => $this->getAuthorData($row),
				'date'         => $this->getDate($row),
				'last_comment' => $row['last_msg_time'],
				'title'        => $this->getTitle($row),
				'link'         => $this->getLink($row),
				'is_new'       => $this->isNew($row),
				'views'        => $this->getViewsData($row),
				'replies'      => $this->getRepliesData($row),
				'css_class'    => $row['is_sticky'] ? ' sticky' : '',
				'image'        => $this->getImage($row),
				'can_edit'     => $this->canEdit($row),
				'edit_link'    => $this->getEditLink($row),
			];

			$this->prepareTeaser($topic, $row);

			$articles = [$row['id_topic'] => $topic];

			$this->events->dispatch(PortalHook::frontTopicsRow, ['articles' => &$articles, 'row' => $row]);

			$topic = $articles[$row['id_topic']];

			yield $row['id_topic'] => Avatar::getWithItems([$topic])[0] ?? [];
		}
	}

	public function getTotalCount(): int
	{
		return $this->query->getTotalCount();
	}

	private function getSectionData(array $row): array
	{
		return [
			'name' => $row['name'],
			'link' => Config::$scripturl . '?board=' . $row['id_board'] . '.0',
		];
	}

	private function getAuthorData(array $row): array
	{
		$authorId   = (str_contains($this->query->getSorting(), 'last_comment') ? $row['last_poster_id'] : $row['id_member']);
		$authorName = str_contains($this->query->getSorting(), 'last_comment') ? $row['last_poster_name'] : $row['poster_name'];

		return [
			'id'   => $authorId,
			'link' => Config::$scripturl . '?action=profile;u=' . $authorId,
			'name' => $authorName,
		];
	}

	private function getDate(array $row): int
	{
		if (str_contains($this->query->getSorting(), 'last_comment') && $row['last_msg_time']) {
			return $row['last_msg_time'];
		}

		if (str_contains($this->query->getSorting(), 'updated')) {
			return $row['date'];
		}

		return $row['poster_time'];
	}

	private function getTitle(array $row): string
	{
		Str::cleanBbcode($row['subject']);

		Lang::censorText($row['subject']);

		return $row['subject'];
	}

	private function getLink(array $row): string
	{
		return Config::$scripturl . '?topic=' . $row['id_topic'] . '.0';
	}

	private function isNew(array $row): bool
	{
		if (User::$me->is_guest || empty($row['new_from']))
			return false;

		return $row['new_from'] <= $row['id_msg_modified'] && $row['last_poster_id'] !== User::$me->id;
	}

	private function getViewsData(array $row): array
	{
		return [
			'num'   => $row['num_views'],
			'title' => Lang::$txt['lp_views'],
			'after' => '',
		];
	}

	private function getRepliesData(array $row): array
	{
		return [
			'num'   => $row['num_replies'],
			'title' => Lang::$txt['lp_replies'],
			'after' => '',
		];
	}

	private function getImage(array $row): string
	{
		if (empty(Config::$modSettings['lp_show_images_in_articles'])) {
			return '';
		}

		$image = Str::getImageFromText(BBCodeParser::load()->parse($row['body'], false));

		if (! empty($row['id_attach']) && empty($image)) {
			return $this->getLink($row) . ';attach=' . $row['id_attach'] . ';image';
		}

		return $image;
	}

	private function canEdit(array $row): bool
	{
		return User::$me->is_admin || (User::$me->id && $row['id_member'] === User::$me->id);
	}

	private function getEditLink(array $row): string
	{
		return Config::$scripturl . '?action=post;msg=' . $row['id_first_msg'] . ';topic=' . $row['id_topic'] . '.0';
	}

	private function prepareTeaser(array &$topic, array $row): void
	{
		if (empty(Config::$modSettings['lp_show_teaser']))
			return;

		$body = (string) (str_contains($this->query->getSorting(), 'last_comment') ? $row['last_body'] : $row['body']);

		Lang::censorText($body);

		$body = preg_replace('~\[spoiler.*].*?\[/spoiler]~Usi', '', $body);
		$body = preg_replace('~\[quote.*].*?\[/quote]~Usi', '', $body);
		$body = preg_replace('~\[table.*].*?\[/table]~Usi', '', $body);
		$body = preg_replace('~\[code.*].*?\[/code]~Usi', '', $body);
		$body = BBCodeParser::load()->parse($body, (bool) $row['smileys_enabled'], (int) $row['id_first_msg']);

		$topic['teaser'] = Str::getTeaser($body);
	}
}
