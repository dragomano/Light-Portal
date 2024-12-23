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

namespace Bugo\LightPortal\Actions;

use Bugo\Compat\PageIndex;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\AlertAction;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\VarType;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Repositories\CommentRepository;
use Bugo\LightPortal\Utils\Avatar;
use Bugo\LightPortal\Utils\CacheTrait;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\Notify;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\Setting;

use function array_map;
use function array_slice;
use function count;
use function date;
use function http_response_code;
use function json_encode;
use function trim;

use const LP_BASE_URL;

if (! defined('SMF'))
	die('No direct access...');

final class Comment implements ActionInterface
{
	use CacheTrait;
	use RequestTrait;

	private CommentRepository $repository;

	public function __construct(private readonly string $pageSlug = '')
	{
		$this->repository = app('comment_repo');
	}

	public function show(): void
	{
		if (empty($this->pageSlug) || $this->request()->isEmpty('api'))
			return;

		header('Content-Type: application/json; charset=utf-8');

		match ($this->request('api')) {
			'add_comment'    => $this->add(),
			'update_comment' => $this->update(),
			'remove_comment' => $this->remove(),
			default          => $this->get(),
		};
	}

	private function get(): never
	{
		$comments = $this->cache('page_' . $this->pageSlug . '_comments')
			->setFallback(fn() => app('comment_repo')->getByPageId(Utils::$context['lp_page']['id']));

		$comments = array_map(function ($comment) {
			$comment['human_date']    = DateTime::relative($comment['created_at']);
			$comment['published_at']  = date('Y-m-d', $comment['created_at']);
			$comment['authorial']     = Utils::$context['lp_page']['author_id'] === $comment['poster']['id'];
			$comment['extra_buttons'] = [];

			app('events')->dispatch(
				PortalHook::commentButtons,
				new Event(new class ($comment, $comment['extra_buttons']) {
					public function __construct(public readonly array $comment, public array &$buttons) {}
				})
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

		$start = (int) $this->request('start');

		http_response_code(200);

		$result = [
			'comments'     => array_slice($commentTree, $start, $limit),
			'parentsCount' => $parentsCount,
			'total'        => count($comments),
			'limit'        => $limit,
		];

		exit(json_encode($result));
	}

	private function add(): never
	{
		$result = [
			'id' => null,
		];

		if (empty(User::$info['id']))
			exit(json_encode($result));

		$data = $this->request()->json();

		if (empty($data['message']))
			exit(json_encode($result));

		$parentId = VarType::INTEGER->filter($data['parent_id']);
		$message  = Utils::htmlspecialchars($data['message']);
		$author   = VarType::INTEGER->filter($data['author']);
		$pageId   = Utils::$context['lp_page']['id'];
		$pageUrl  = Utils::$context['canonical_url'];

		if (empty($pageId) || empty($message))
			exit(json_encode($result));

		$item = $this->repository->save([
			'parent_id'  => $parentId,
			'page_id'    => $pageId,
			'author_id'  => User::$info['id'],
			'message'    => $message,
			'created_at' => $time = time(),
		]);

		if ($item) {
			$this->repository->updateLastCommentId($item, $pageId);

			$result = [
				'id'           => $item,
				'parent_id'    => $parentId,
				'message'      => $message,
				'created_at'   => $time,
				'published_at' => date('Y-m-d', $time),
				'human_date'   => DateTime::relative($time),
				'can_edit'     => true,
				'poster'       => [
					'id'     => User::$info['id'],
					'name'   => User::$info['name'],
					'avatar' => Avatar::get(User::$info['id']),
				],
			];

			$options = [
				'item'      => $item,
				'time'      => $time,
				'author_id' => empty($parentId) ? Utils::$context['lp_page']['author_id'] : $author,
				'title'     => Utils::$context['page_title'],
				'url'       => $pageUrl . '#comment=' . $item,
			];

			empty($parentId)
				? Notify::send('new_comment', AlertAction::PAGE_COMMENT->name(), $options)
				: Notify::send('new_reply', AlertAction::PAGE_COMMENT_REPLY->name(), $options);

			$this->cache()->forget('page_' . $this->pageSlug . '_comments');
		}

		http_response_code(201);

		exit(json_encode($result));
	}

	private function update(): never
	{
		$data = $this->request()->json();

		$result = [
			'success' => false,
		];

		if (empty($data) || Utils::$context['user']['is_guest'])
			exit(json_encode($result));

		$item    = $data['comment_id'];
		$message = trim((string) $data['message']);
		$message = Utils::htmlspecialchars($message);

		if (empty($item) || $message === '')
			exit(json_encode($result));

		$this->repository->update([
			'message' => Utils::shorten($message, 65531),
			'id'      => $item,
			'user'    => Utils::$context['user']['id'],
		]);

		$result = [
			'success' => true,
			'message' => $message,
		];

		$this->cache()->forget('page_' . $this->pageSlug . '_comments');

		exit(json_encode($result));
	}

	private function remove(): never
	{
		$item = (int) $this->request()->json('comment_id');

		if (empty($item)) {
			exit(json_encode(['success' => false]));
		}

		$items = $this->repository->remove($item, $this->pageSlug);

		$this->cache()->forget('page_' . $this->pageSlug . '_comments');

		exit(json_encode(['success' => true, 'items' => $items]));
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
