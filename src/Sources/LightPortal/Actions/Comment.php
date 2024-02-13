<?php declare(strict_types=1);

/**
 * Comment.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Actions;

use Bugo\LightPortal\Tasks\Notifier;
use Bugo\Compat\{Config, PageIndex, User, Utils};
use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Repositories\CommentRepository;
use IntlException;

if (! defined('SMF'))
	die('No direct access...');

final class Comment implements ActionInterface
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
	public function show(): void
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
			->setFallback(CommentRepository::class, 'getByPageId', Utils::$context['lp_page']['id']);

		$comments = array_map(function ($comment) {
			$comment['human_date']    = DateTime::relative($comment['created_at']);
			$comment['published_at']  = date('Y-m-d', $comment['created_at']);
			$comment['authorial']     = Utils::$context['lp_page']['author_id'] === $comment['poster']['id'];
			$comment['extra_buttons'] = [];

			$this->hook('commentButtons', [$comment, &$comment['extra_buttons']]);

			return $comment;
		}, $comments);

		$start = (int) $this->request()->get('start');
		$limit = (int) (Config::$modSettings['lp_num_comments_per_page'] ?? 10);

		$commentTree  = $this->getTree($comments);
		$parentsCount = sizeof($commentTree);

		Utils::$context['page_index'] = new PageIndex(
			$this->getPageIndexUrl(), $start, $parentsCount, $limit
		);

		$start = (int) $this->request('start');

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

		if (empty(User::$info['id']))
			exit(json_encode($result));

		$data = $this->request()->json();

		if (empty($data['message']))
			exit(json_encode($result));

		$parentId = $this->filterVar($data['parent_id'], 'int');
		$message  = Utils::$smcFunc['htmlspecialchars']($data['message']);
		$author   = $this->filterVar($data['author'], 'int');
		$pageId   = Utils::$context['lp_page']['id'];
		$pageUrl  = Utils::$context['canonical_url'];

		if (empty($pageId) || empty($message))
			exit(json_encode($result));

		$item = $this->repository->save([
			'parent_id'  => $parentId,
			'page_id'    => $pageId,
			'author_id'  => User::$info['id'],
			'message'    => $message,
			'created_at' => $time = time()
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
					'avatar' => $this->getUserAvatar(User::$info['id']),
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
				? Notifier::send('new_comment', 'page_comment', $options)
				: Notifier::send('new_reply', 'page_comment_reply', $options);

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

		if (empty($data) || Utils::$context['user']['is_guest'])
			exit(json_encode($result));

		$item    = $data['comment_id'];
		$message = Utils::$smcFunc['htmlspecialchars']($data['message']);

		if (empty($item) || empty($message) || empty(trim($message)))
			exit(json_encode($result));

		$this->repository->update([
			'message' => Utils::shorten($message, 65531),
			'id'      => $item,
			'user'    => Utils::$context['user']['id']
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
		if (! (
			empty(Config::$modSettings['lp_frontpage_mode'])
			|| Config::$modSettings['lp_frontpage_mode'] !== 'chosen_page'
		) && ! empty(Config::$modSettings['lp_frontpage_alias']))
			return LP_BASE_URL;

		return Utils::$context['canonical_url'];
	}
}
