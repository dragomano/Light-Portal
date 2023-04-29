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
 * @version 2.1
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
		if (empty($this->alias))
			return;

		$disabledBbc = isset($this->modSettings['disabledBBC']) ? explode(',', $this->modSettings['disabledBBC']) : [];
		$this->context['lp_allowed_bbc'] = empty($this->modSettings['lp_enabled_bbc_in_comments']) ? [] : explode(',', $this->modSettings['lp_enabled_bbc_in_comments']);
		$this->context['lp_allowed_bbc'] = array_diff($this->context['lp_allowed_bbc'], array_intersect($disabledBbc, $this->context['lp_allowed_bbc']));

		if ($this->request()->isNotEmpty('sa')) {
			switch ($this->request('sa')) {
				case 'add_comment':
					$this->add();
					break;
				case 'edit_comment':
					$this->edit();
					break;
				case 'remove_comment':
					$this->remove();
					break;
			}
		}

		$comments = $this->cache('page_' . $this->alias . '_comments')
			->setFallback(CommentRepository::class, 'getByPageId', $this->context['lp_page']['id']);

		$comments = array_map(function ($comment) {
			$comment['created']    = $this->getFriendlyTime($comment['created_at']);
			$comment['created_at'] = date('Y-m-d', $comment['created_at']);

			return $comment;
		}, $comments);

		$this->txt['lp_comments'] = $this->translate('lp_comments_set', ['comments' => sizeof($comments)]);

		$limit = (int) ($this->modSettings['lp_num_comments_per_page'] ?? 10);
		$commentTree = $this->getTree($comments);
		$totalParentComments = sizeof($commentTree);

		$this->context['current_start'] = $this->request('start');

		$this->context['page_index'] = $this->constructPageIndex(
			$this->getPageIndexUrl(),
			$this->request()->get('start'),
			$totalParentComments,
			$limit
		);

		$start = $this->request('start');

		$this->context['page_info'] = [
			'num_pages' => $num_pages = floor($totalParentComments / $limit) + 1,
			'start'     => $num_pages * $limit - $limit
		];

		if ($this->context['current_start'] > $totalParentComments)
			$this->sendStatus(404);

		$this->context['lp_page']['comments'] = array_slice($commentTree, $start, $limit);

		if ($this->context['user']['is_logged']) {
			$this->addInlineJavaScript('
		const comment = new Comment({
			pageUrl: "' . $this->context['canonical_url'] . ($this->request()->has(LP_PAGE_PARAM) ? ';' : '?') . '",
			start: ' . $start . ',
			lastStart: ' . $this->context['page_info']['start'] . ',
			totalParentComments: ' . count($this->context['lp_page']['comments']) . ',
			commentsPerPage: ' . $limit . '
		});
		const toolbar = new Toolbar();');
		}
	}

	private function add(): void
	{
		$result = [
			'error' => true
		];

		if (empty($this->user_info['id']))
			exit(json_encode($result));

		$data = $this->request()->json();

		if (empty($data['message']))
			exit(json_encode($result));

		$parent      = filter_var($data['parent_id'], FILTER_VALIDATE_INT);
		$counter     = filter_var($data['counter'], FILTER_VALIDATE_INT);
		$level       = filter_var($data['level'], FILTER_VALIDATE_INT);
		$page_id     = filter_var($data['page_id'], FILTER_VALIDATE_INT);
		$page_url    = filter_var($data['page_url'], FILTER_VALIDATE_URL);
		$message     = filter_var($data['message'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$start       = filter_var($data['start'], FILTER_VALIDATE_INT);
		$commentator = filter_var($data['commentator'], FILTER_VALIDATE_INT);

		if (empty($page_id) || empty($message))
			exit(json_encode($result));

		$this->preparseCode($message);

		$item = $this->repository->save([
			'parent_id'  => $parent,
			'page_id'    => $page_id,
			'author_id'  => $this->user_info['id'],
			'message'    => $message,
			'created_at' => $time = time()
		]);

		if ($item) {
			$this->repository->updateLastCommentId($item, $page_id);

			ob_start();

			show_single_comment([
				'id'          => $item,
				'start'       => $start,
				'parent_id'   => $parent,
				'message'     => empty($this->context['lp_allowed_bbc']) ? $message : $this->parseBbc($message, true, 'lp_comments_' . $item, $this->context['lp_allowed_bbc']),
				'created_at'  => date('Y-m-d', $time),
				'created'     => $this->getFriendlyTime($time),
				'raw_message' => $this->unPreparseCode($message),
				'can_edit'    => true,
				'poster'      => [
					'id'     => $this->user_info['id'],
					'name'   => $this->user_info['name'],
					'avatar' => $this->getUserAvatar($this->user_info['id']),
				],
			], $counter + 1, $level + 1);

			$comment = ob_get_clean();

			$result = [
				'item'        => $item,
				'parent'      => $parent,
				'comment'     => $comment,
				'created'     => $time,
				'title'       => $this->context['page_title'],
				'alias'       => $this->alias,
				'page_url'    => $page_url,
				'start'       => $start,
				'commentator' => $commentator,
			];

			$notifyOptions = [
				'item'      => $item,
				'time'      => $time,
				'author_id' => empty($parent) ? $this->context['lp_page']['author_id'] : $commentator,
				'title'     => $this->context['page_title'],
				'url'       => $page_url . 'start=' . $start . '#comment' . $item,
			];

			empty($parent)
				? $this->makeNotify('new_comment', 'page_comment', $notifyOptions)
				: $this->makeNotify('new_reply', 'page_comment_reply', $notifyOptions);

			$this->cache()->forget('page_' . $this->alias . '_comments');
		}

		exit(json_encode($result));
	}

	private function edit(): void
	{
		$data = $this->request()->json();

		if (empty($data) || $this->context['user']['is_guest'])
			exit;

		$item    = $data['comment_id'];
		$message = $this->validate($data['message']);

		if (empty($item) || empty($message))
			exit;

		$this->preparseCode($message);

		$this->repository->update([
			'message' => $this->getShortenText($message, 65531),
			'id'      => $item,
			'user'    => $this->context['user']['id']
		]);

		$message = empty($this->context['lp_allowed_bbc']) ? $message : $this->parseBbc($message, true, 'lp_comments_' . $item, $this->context['lp_allowed_bbc']);

		$this->cache()->forget('page_' . $this->alias . '_comments');

		exit(json_encode($message));
	}

	private function remove(): void
	{
		$items = $this->request()->json('items');

		if (empty($items))
			return;

		$this->repository->remove($items, $this->alias);

		$this->cache()->forget('page_' . $this->alias . '_comments');

		exit;
	}

	private function getTree(array $data): array
	{
		$tree = [];

		foreach ($data as $id => &$node) {
			empty($node['parent_id'])
				? $tree[$id] = &$node
				: $data[$node['parent_id']]['children'][$id] = &$node;
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
