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

namespace LightPortal\Actions;

use Bugo\Compat\Config;
use Bugo\Compat\Mentions;
use Bugo\Compat\PageIndex;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Enums\AlertAction;
use LightPortal\Enums\NotifyType;
use LightPortal\Enums\PortalHook;
use LightPortal\Enums\VarType;
use LightPortal\Events\HasEvents;
use LightPortal\Repositories\CommentRepositoryInterface;
use LightPortal\Utils\DateTime;
use LightPortal\Utils\Notifier;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasCache;
use LightPortal\Utils\Traits\HasRequest;
use LightPortal\Utils\Traits\HasResponse;

use const LP_BASE_URL;

if (! defined('SMF'))
	die('No direct access...');

final class Comment implements ActionInterface
{
	use HasCache;
	use HasEvents;
	use HasRequest;
	use HasResponse;

	private string $pageSlug;

	public function __construct(
		private readonly CommentRepositoryInterface $repository,
		private readonly Notifier $notifier
	)
	{
		$this->setPageSlug(Utils::$context['lp_page']['slug']);
	}

	public function setPageSlug(string $pageSlug): self
	{
		$this->pageSlug = $pageSlug;

		return $this;
	}

	public function show(): void
	{
		if (empty($this->pageSlug) || $this->request()->isEmpty('api'))
			return;

		match ($this->request()->get('api')) {
			'add_comment'    => $this->add(),
			'update_comment' => $this->update(),
			'remove_comment' => $this->remove(),
			default          => $this->get(),
		};
	}

	private function get(): never
	{
		$comments = $this->langCache('page_' . $this->pageSlug . '_comments')
			->setFallback(fn() => $this->repository->getByPageId(Utils::$context['lp_page']['id']));

		$comments = array_map(function ($comment) {
			$comment['human_date']    = DateTime::relative($comment['created_at']);
			$comment['published_at']  = date('Y-m-d', $comment['created_at']);
			$comment['human_update']  = DateTime::relative($comment['updated_at']);
			$comment['updated_at']    = date('Y-m-d', $comment['updated_at']);
			$comment['authorial']     = Utils::$context['lp_page']['author_id'] === $comment['poster']['id'];
			$comment['extra_buttons'] = [];

			$this->events()->dispatch(
				PortalHook::commentButtons,
				[
					'comment' => $comment,
					'buttons' => &$comment['extra_buttons'],
				]
			);

			return $comment;
		}, $comments);

		$start = (int) $this->request()->get('start');
		$limit = Setting::get('lp_num_comments_per_page', 'int', 10);

		$commentTree  = $this->getTree($comments);
		$parentsCount = count($commentTree);

		Utils::$context['page_index'] = new PageIndex(
			$this->getPageIndexUrl(), $start, $parentsCount, $limit
		);

		$start = Str::typed('int', $this->request()->get('start'));

		http_response_code(200);

		$result = [
			'comments'     => array_slice($commentTree, $start, $limit),
			'parentsCount' => $parentsCount,
			'total'        => count($comments),
			'limit'        => $limit,
		];

		$this->response()->exit($result);
	}

	private function add(): never
	{
		$data = $this->request()->json();

		$result = [
			'id' => null,
		];

		if (empty($data['message']) || User::$me->is_guest) {
			$this->response()->exit($result);
		}

		$parentId = VarType::INTEGER->filter($data['parent_id']);
		$message  = Utils::htmlspecialchars($data['message']);
		$author   = VarType::INTEGER->filter($data['author']);
		$pageId   = Utils::$context['lp_page']['id'];
		$pageUrl  = Utils::$context['canonical_url'];

		if (empty($pageId) || empty($message)) {
			$this->response()->exit($result);
		}

		$verifiedMembers = $this->getMembersToMention($message);

		$message = $this->replaceMemberTagsToMarkdown($message);

		$item = $this->repository->save([
			'parent_id'  => $parentId,
			'page_id'    => $pageId,
			'author_id'  => User::$me->id,
			'message'    => $message,
			'created_at' => $time = time(),
		]);

		if ($item) {
			$this->repository->updateLastCommentId($item, $pageId);

			$options = [
				'item'      => $item,
				'time'      => $time,
				'author_id' => empty($parentId) ? Utils::$context['lp_page']['author_id'] : $author,
				'title'     => Utils::$context['page_title'],
				'url'       => $pageUrl . '#comment=' . $item,
			];

			$this->mentionMembers($verifiedMembers, $options);

			empty($parentId)
				? $this->notifier->notify(NotifyType::NEW_COMMENT->name(), AlertAction::PAGE_COMMENT->name(), $options)
				: $this->notifier->notify(NotifyType::NEW_REPLY->name(), AlertAction::PAGE_COMMENT_REPLY->name(), $options);

			$this->cache()->forget('page_' . $this->pageSlug . '_comments_u' . User::$me->id . '_' . User::$me->language);
		}

		http_response_code(201);

		$this->response()->exit($this->repository->getData($item));
	}

	private function getMembersToMention(string &$message): array
	{
		if (! Setting::canMention())
			return [];

		$members = Mentions::getMentionedMembers($message);
		$message = Mentions::getBody($message, $members);

		return Mentions::verifyMentionedMembers($message, $members);
	}

	private function replaceMemberTagsToMarkdown(string $text): string
	{
		return preg_replace_callback(
			'/\[member=(\d+)](.*?)\[\/member]/',
			function (array $matches) {
				$id   = $matches[1];
				$name = $matches[2];

				return "[@$name](" . Config::$scripturl . '?action=profile;u=' . $id . ")";
			},
			$text
		);
	}

	private function mentionMembers(array $verifiedMembers, array $options): void
	{
		if (! Setting::canMention() || empty($verifiedMembers))
			return;

		foreach ($verifiedMembers as $member) {
			$options['author_id'] = (int) $member['id'];

			$this->notifier->notify(NotifyType::NEW_MENTION->name(), AlertAction::PAGE_COMMENT_MENTION->name(), $options);
		}
	}

	private function update(): never
	{
		$data = $this->request()->json();

		$result = [
			'success' => false,
		];

		if (empty($data) || User::$me->is_guest) {
			$this->response()->exit($result);
		}

		$item    = $data['comment_id'];
		$message = trim((string) $data['message']);
		$message = Utils::htmlspecialchars($message);

		if (empty($item) || $message === '') {
			$this->response()->exit($result);
		}

		$this->repository->update([
			'message' => Utils::shorten($message, 65531),
			'id'      => $item,
			'user'    => Utils::$context['user']['id'],
		]);

		$result = [
			'success' => true,
			'message' => $message,
		];

		$this->cache()->forget('page_' . $this->pageSlug . '_comments_u' . User::$me->id . '_' . User::$me->language);

		$this->response()->exit($result);
	}

	private function remove(): void
	{
		$item = (int) $this->request()->json('comment_id');

		if (empty($item)) {
			$this->response()->exit(['success' => false]);
		}

		$this->repository->remove([$item]);

		$this->cache()->forget('page_' . $this->pageSlug . '_comments_u' . User::$me->id . '_' . User::$me->language);
	}

	private function getTree(array $data): array
	{
		$tree = [];

		foreach ($data as $id => &$node) {
			empty($node['parent_id'])
				? $tree[$id] = &$node
				: isset($data[$node['parent_id']]['id']) && $data[$node['parent_id']]['replies'][$id] = &$node;
		}

		return $tree;
	}

	private function getPageIndexUrl(): string
	{
		return Setting::isFrontpage($this->pageSlug) ? LP_BASE_URL : Utils::$context['canonical_url'];
	}
}
