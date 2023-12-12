<?php declare(strict_types=1);

/**
 * Comment.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Entities;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Repositories\CommentRepository;
use IntlException;

if (! defined('SMF'))
	die('No direct access...');

final class Comment
{
	use Helper;

	private CommentRepository $repository;

	public function __construct(private string $alias = '')
	{
		$this->repository = new CommentRepository();
	}

	/**
	 * @throws IntlException
	 */
	public function prepare(): void
	{
		if (empty($this->alias) || $this->request()->isEmpty('api'))
			return;

		header('Content-Type: application/json; charset=utf-8');

		match ($this->request('api')) {
			'add_comment'    => $this->add(),
			'update_comment' => $this->update(),
			'remove_comment' => $this->remove(),
			default          => $this->get(),
		};
	}

	/**
	 * @throws IntlException
	 */
	private function get(): void
	{
		$comments = $this->cache('page_' . $this->alias . '_comments')
			->setFallback(CommentRepository::class, 'getByPageId', $this->context['lp_page']['id']);

		$comments = array_map(function ($comment) {
			$comment['human_date']    = $this->getFriendlyTime($comment['created_at']);
			$comment['published_at']  = date('Y-m-d', $comment['created_at']);
			$comment['authorial']     = $this->context['lp_page']['author_id'] === $comment['poster']['id'];
			$comment['extra_buttons'] = [];

			$this->hook('commentButtons', [$comment, &$comment['extra_buttons']]);

			return $comment;
		}, $comments);

		$limit = (int) ($this->modSettings['lp_num_comments_per_page'] ?? 10);

		$commentTree = $this->getTree($comments);

		$parentsCount = sizeof($commentTree);

		$this->context['page_index'] = $this->constructPageIndex(
			$this->getPageIndexUrl(),
			$this->request()->get('start'),
			$parentsCount,
			$limit
		);

		$start = $this->request('start');

		http_response_code(200);

		$result = [
			'comments'     => array_slice($commentTree, $start, $limit),
			'parentsCount' => $parentsCount,
			'total'        => sizeof($comments),
			'limit'        => $limit
		];

		exit(json_encode($result));
	}

	/**
	 * @throws IntlException
	 */
	private function add(): void
	{
		$result = [
			'id' => null
		];

		if (empty($this->user_info['id']))
			exit(json_encode($result));

		$data = $this->request()->json();

		if (empty($data['message']))
			exit(json_encode($result));

		$parent_id = $this->validate($data['parent_id'], 'int');
		$message   = $this->smcFunc['htmlspecialchars']($data['message']);
		$author    = $this->validate($data['author'], 'int');
		$page_id   = $this->context['lp_page']['id'];
		$page_url  = $this->context['canonical_url'];

		if (empty($page_id) || empty($message))
			exit(json_encode($result));

		$item = $this->repository->save([
			'parent_id'  => $parent_id,
			'page_id'    => $page_id,
			'author_id'  => $this->user_info['id'],
			'message'    => $message,
			'created_at' => $time = time()
		]);

		if ($item) {
			$this->repository->updateLastCommentId($item, $page_id);

			$result = [
				'id'           => $item,
				'parent_id'    => $parent_id,
				'message'      => $message,
				'created_at'   => $time,
				'published_at' => date('Y-m-d', $time),
				'human_date'   => $this->getFriendlyTime($time),
				'can_edit'     => true,
				'poster'       => [
					'id'     => $this->user_info['id'],
					'name'   => $this->user_info['name'],
					'avatar' => $this->getUserAvatar($this->user_info['id']),
				],
			];

			$notifyOptions = [
				'item'      => $item,
				'time'      => $time,
				'author_id' => empty($parent_id) ? $this->context['lp_page']['author_id'] : $author,
				'title'     => $this->context['page_title'],
				'url'       => $page_url . '#comment=' . $item,
			];

			empty($parent_id)
				? $this->makeNotify('new_comment', 'page_comment', $notifyOptions)
				: $this->makeNotify('new_reply', 'page_comment_reply', $notifyOptions);

			$this->cache()->forget('page_' . $this->alias . '_comments');
		}

		http_response_code(201);

		exit(json_encode($result));
	}

	private function update(): void
	{
		$data = $this->request()->json();

		$result = [
			'success' => false
		];

		if (empty($data) || $this->context['user']['is_guest'])
			exit(json_encode($result));

		$item    = $data['comment_id'];
		$message = $this->smcFunc['htmlspecialchars']($data['message']);

		if (empty($item) || empty($message) || empty(trim($message)))
			exit(json_encode($result));

		$this->repository->update([
			'message' => $this->getShortenText($message, 65531),
			'id'      => $item,
			'user'    => $this->context['user']['id']
		]);

		$result = [
			'success' => true,
			'message' => $message
		];

		$this->cache()->forget('page_' . $this->alias . '_comments');

		exit(json_encode($result));
	}

	private function remove(): void
	{
		$items = $this->request()->json('items');

		if (empty($items))
			exit(json_encode(['success' => false]));

		$this->repository->remove($items, $this->alias);

		$this->cache()->forget('page_' . $this->alias . '_comments');

		exit(json_encode(['success' => true]));
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
		if (! (empty($this->modSettings['lp_frontpage_mode']) || $this->modSettings['lp_frontpage_mode'] !== 'chosen_page') && ! empty($this->modSettings['lp_frontpage_alias']))
			return LP_BASE_URL;

		return $this->context['canonical_url'];
	}
}
