<?php

declare(strict_types=1);

/**
 * PageRepository.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Repositories;

if (! defined('SMF'))
	die('No direct access...');

final class PageRepository extends AbstractRepository
{
	protected string $entity = 'page';

	public function getAll(int $start, int $items_per_page, string $sort, string $query_string = '', array $query_params = []): array
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT p.page_id, p.author_id, p.alias, p.type, p.permissions, p.status, p.num_views, p.num_comments,
				GREATEST(p.created_at, p.updated_at) AS date, mem.real_name AS author_name, t.title, tf.title AS fallback_title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang})
				LEFT JOIN {db_prefix}lp_titles AS tf ON (p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang})' . ($this->user_info['is_admin'] ? '
			WHERE 1=1' : '
			WHERE p.author_id = {int:user_id}') . (empty($query_string) ? '' : '
				AND ' . $query_string) . '
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array_merge($query_params, [
				'lang'          => $this->user_info['language'],
				'fallback_lang' => $this->language,
				'user_id'       => $this->user_info['id'],
				'sort'          => $sort,
				'start'         => $start,
				'limit'         => $items_per_page,
			])
		);

		$items = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			$items[$row['page_id']] = [
				'id'           => (int) $row['page_id'],
				'alias'        => $row['alias'],
				'type'         => $row['type'],
				'status'       => (int) $row['status'],
				'num_views'    => (int) $row['num_views'],
				'num_comments' => (int) $row['num_comments'],
				'author_id'    => (int) $row['author_id'],
				'author_name'  => $row['author_name'],
				'created_at'   => $this->getFriendlyTime((int) $row['date']),
				'is_front'     => $this->isFrontpage($row['alias']),
				'title'        => $row['title'] ?: $row['fallback_title'],
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $items;
	}

	public function getTotalCount(string $query_string = '', array $query_params = []): int
	{
		$request = $this->smcFunc['db_query']('', '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang})' . ($this->user_info['is_admin'] ? '
			WHERE 1=1' : '
			WHERE p.author_id = {int:user_id}') . (empty($query_string) ? '' : '
				AND ' . $query_string),
			array_merge($query_params, [
				'lang'    => $this->user_info['language'],
				'user_id' => $this->user_info['id'],
			])
		);

		[$num_entries] = $this->smcFunc['db_fetch_row']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return (int) $num_entries;
	}

	public function setData(int $item = 0)
	{
		if (isset($this->context['post_errors']) || (
			$this->request()->has('save') === false &&
			$this->request()->has('save_exit') === false)
		)
			return;

		$this->checkSubmitOnce('check');

		$this->prepareDescription();
		$this->prepareKeywords();

		$this->prepareBbcContent($this->context['lp_page']);

		if (empty($item)) {
			$item = $this->addData();
		} else {
			$this->updateData($item);
		}

		$this->cache()->flush();

		if ($this->request()->has('save_exit'))
			$this->redirect('action=admin;area=lp_pages;sa=main');

		if ($this->request()->has('save'))
			$this->redirect('action=admin;area=lp_pages;sa=edit;id=' . $item);
	}

	private function addData(): int
	{
		$this->smcFunc['db_transaction']('begin');

		$item = (int) $this->smcFunc['db_insert']('',
			'{db_prefix}lp_pages',
			array_merge([
				'category_id' => 'int',
				'author_id'   => 'int',
				'alias'       => 'string-255',
				'description' => 'string-255',
				'content'     => 'string',
				'type'        => 'string',
				'permissions' => 'int',
				'status'      => 'int',
				'created_at'  => 'int',
			], $this->db_type === 'postgresql' ? ['page_id' => 'int'] : []),
			array_merge([
				$this->context['lp_page']['category'],
				$this->context['lp_page']['page_author'],
				$this->context['lp_page']['alias'],
				$this->context['lp_page']['description'],
				$this->context['lp_page']['content'],
				$this->context['lp_page']['type'],
				$this->context['lp_page']['permissions'],
				$this->context['lp_page']['status'],
				$this->getPublishTime(),
			], $this->db_type === 'postgresql' ? [$this->getAutoIncrementValue()] : []),
			['page_id'],
			1
		);

		$this->context['lp_num_queries']++;

		if (empty($item)) {
			$this->smcFunc['db_transaction']('rollback');
			return 0;
		}

		$this->hook('onPageSaving', [$item]);

		$this->saveTitles($item);
		$this->saveTags();
		$this->saveOptions($item);

		$this->smcFunc['db_transaction']('commit');

		return $item;
	}

	private function updateData(int $item)
	{
		$this->smcFunc['db_transaction']('begin');

		$this->smcFunc['db_query']('', '
			UPDATE {db_prefix}lp_pages
			SET category_id = {int:category_id}, author_id = {int:author_id}, alias = {string:alias}, description = {string:description}, content = {string:content}, type = {string:type}, permissions = {int:permissions}, status = {int:status}, updated_at = {int:updated_at}
			WHERE page_id = {int:page_id}',
			[
				'category_id' => $this->context['lp_page']['category'],
				'author_id'   => $this->context['lp_page']['page_author'],
				'alias'       => $this->context['lp_page']['alias'],
				'description' => $this->context['lp_page']['description'],
				'content'     => $this->context['lp_page']['content'],
				'type'        => $this->context['lp_page']['type'],
				'permissions' => $this->context['lp_page']['permissions'],
				'status'      => $this->context['lp_page']['status'],
				'updated_at'  => time(),
				'page_id'     => $item,
			]
		);

		$this->context['lp_num_queries']++;

		$this->hook('onPageSaving', [$item]);

		$this->saveTitles($item, 'replace');
		$this->saveTags();
		$this->saveOptions($item, 'replace');

		if ($this->context['lp_page']['page_author'] !== $this->user_info['id'])
			logAction('update_lp_page', [
				'page' => '<a href="' . LP_PAGE_URL . $this->context['lp_page']['alias'] . '">' . $this->context['lp_page']['title'][$this->user_info['language']] . '</a>'
			]);

		$this->smcFunc['db_transaction']('commit');
	}

	private function saveTags()
	{
		$newTagIds = array_diff($this->context['lp_page']['keywords'], array_keys($this->context['lp_tags']));
		$oldTagIds = array_intersect($this->context['lp_page']['keywords'], array_keys($this->context['lp_tags']));

		array_walk($newTagIds, function (&$item) {
			$item = ['value' => $item];
		});

		if ($newTagIds) {
			$newTagIds = $this->smcFunc['db_insert']('',
				'{db_prefix}lp_tags',
				[
					'value' => 'string',
				],
				$newTagIds,
				['tag_id'],
				2
			);

			$this->context['lp_num_queries']++;
		}

		$this->context['lp_page']['options']['keywords'] = array_merge($oldTagIds, $newTagIds);
	}

	private function prepareDescription()
	{
		$this->cleanBbcode($this->context['lp_page']['description']);

		$this->context['lp_page']['description'] = strip_tags($this->context['lp_page']['description']);
	}

	private function prepareKeywords()
	{
		// Remove all punctuation symbols
		$this->context['lp_page']['keywords'] = preg_replace("#[[:punct:]]#", "", $this->context['lp_page']['keywords']);
	}

	private function getPublishTime(): int
	{
		$publish_time = time();

		if ($this->context['lp_page']['date'])
			$publish_time = strtotime($this->context['lp_page']['date']);

		if ($this->context['lp_page']['time'])
			$publish_time = strtotime(date('Y-m-d', $publish_time) . ' ' . $this->context['lp_page']['time']);

		return $publish_time;
	}

	private function getAutoIncrementValue(): int
	{
		$request = $this->smcFunc['db_query']('', /** @lang text */ "SELECT setval('{db_prefix}lp_pages_seq', (SELECT MAX(page_id) FROM {db_prefix}lp_pages))");
		[$value] = $this->smcFunc['db_fetch_row']($request);

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return (int) $value + 1;
	}
}
