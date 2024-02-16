<?php

declare(strict_types=1);

/**
 * PageRepository.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\{Config, Database as Db, Logging};
use Bugo\Compat\{Security, User, Utils};
use Bugo\LightPortal\Utils\{DateTime, Notify};
use IntlException;

if (! defined('SMF'))
	die('No direct access...');

final class PageRepository extends AbstractRepository
{
	protected string $entity = 'page';

	/**
	 * @throws IntlException
	 */
	public function getAll(
		int $start,
		int $items_per_page,
		string $sort,
		string $query_string = '',
		array $query_params = []
	): array
	{
		$result = Db::$db->query('', '
			SELECT p.page_id, p.category_id, p.author_id, p.alias, p.type, p.permissions, p.status,
				p.num_views, p.num_comments, GREATEST(p.created_at, p.updated_at) AS date,
				mem.real_name AS author_name, t.title, tf.title AS fallback_title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS tf ON (
					p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang}
				)
			WHERE 1=1' . (empty($query_string) ? '' : '
				' . $query_string) . '
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array_merge($query_params, [
				'lang'          => User::$info['language'],
				'fallback_lang' => Config::$language,
				'user_id'       => User::$info['id'],
				'sort'          => $sort,
				'start'         => $start,
				'limit'         => $items_per_page,
			])
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['page_id']] = [
				'id'           => (int) $row['page_id'],
				'category_id'  => (int) $row['category_id'],
				'alias'        => $row['alias'],
				'type'         => $row['type'],
				'status'       => (int) $row['status'],
				'num_views'    => (int) $row['num_views'],
				'num_comments' => (int) $row['num_comments'],
				'author_id'    => (int) $row['author_id'],
				'author_name'  => $row['author_name'],
				'created_at'   => DateTime::relative((int) $row['date']),
				'is_front'     => $this->isFrontpage($row['alias']),
				'title'        => ($row['title'] ?: $row['fallback_title']) ?: $row['alias'],
			];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}

	public function getTotalCount(string $query_string = '', array $query_params = []): int
	{
		$result = Db::$db->query('', '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
			WHERE 1=1' . (empty($query_string) ? '' : '
				' . $query_string),
			array_merge($query_params, [
				'lang'    => User::$info['language'],
				'user_id' => User::$info['id'],
			])
		);

		[$num_entries] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return (int) $num_entries;
	}

	public function setData(int $item = 0): void
	{
		if (isset(Utils::$context['post_errors']) || (
			$this->request()->hasNot('save') &&
			$this->request()->hasNot('save_exit'))
		)
			return;

		Security::checkSubmitOnce('check');

		$this->prepareDescription();
		$this->prepareKeywords();

		$this->prepareBbcContent(Utils::$context['lp_page']);

		if (empty($item)) {
			Utils::$context['lp_page']['titles'] = array_filter(Utils::$context['lp_page']['titles']);
			$item = $this->addData();
		} else {
			$this->updateData($item);
		}

		$this->cache()->flush();

		if ($this->request()->has('save_exit'))
			Utils::redirectexit('action=admin;area=lp_pages;sa=main');

		if ($this->request()->has('save'))
			Utils::redirectexit('action=admin;area=lp_pages;sa=edit;id=' . $item);
	}

	private function addData(): int
	{
		Db::$db->transaction('begin');

		$item = (int) Db::$db->insert('',
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
			], Config::$db_type === 'postgresql' ? ['page_id' => 'int'] : []),
			array_merge([
				Utils::$context['lp_page']['category_id'],
				Utils::$context['lp_page']['author_id'],
				Utils::$context['lp_page']['alias'],
				Utils::$context['lp_page']['description'],
				Utils::$context['lp_page']['content'],
				Utils::$context['lp_page']['type'],
				Utils::$context['lp_page']['permissions'],
				Utils::$context['lp_page']['status'],
				$this->getPublishTime(),
			], Config::$db_type === 'postgresql' ? [$this->getAutoIncrementValue()] : []),
			['page_id'],
			1
		);

		Utils::$context['lp_num_queries']++;

		if (empty($item)) {
			Db::$db->transaction('rollback');
			return 0;
		}

		$this->hook('onPageSaving', [$item]);

		$this->saveTitles($item);
		$this->saveTags();
		$this->saveOptions($item);

		Db::$db->transaction('commit');

		// Notify page moderators about new page
		$title = Utils::$context['lp_page']['titles'][User::$info['language']]
			?? Utils::$context['lp_page']['titles'][Config::$language];

		$options = [
			'item'      => $item,
			'time'      => $this->getPublishTime(),
			'author_id' => Utils::$context['lp_page']['author_id'],
			'title'     => $title,
			'url'       => LP_PAGE_URL . Utils::$context['lp_page']['alias']
		];

		if (empty(Utils::$context['allow_light_portal_manage_pages_any']))
			Notify::send('new_page', 'page_unapproved', $options);

		return $item;
	}

	private function updateData(int $item): void
	{
		Db::$db->transaction('begin');

		Db::$db->query('', '
			UPDATE {db_prefix}lp_pages
			SET category_id = {int:category_id}, author_id = {int:author_id}, alias = {string:alias},
				description = {string:description}, content = {string:content}, type = {string:type},
				permissions = {int:permissions}, status = {int:status}, updated_at = {int:updated_at}
			WHERE page_id = {int:page_id}',
			[
				'category_id' => Utils::$context['lp_page']['category_id'],
				'author_id'   => Utils::$context['lp_page']['author_id'],
				'alias'       => Utils::$context['lp_page']['alias'],
				'description' => Utils::$context['lp_page']['description'],
				'content'     => Utils::$context['lp_page']['content'],
				'type'        => Utils::$context['lp_page']['type'],
				'permissions' => Utils::$context['lp_page']['permissions'],
				'status'      => Utils::$context['lp_page']['status'],
				'updated_at'  => time(),
				'page_id'     => $item,
			]
		);

		Utils::$context['lp_num_queries']++;

		$this->hook('onPageSaving', [$item]);

		$this->saveTitles($item, 'replace');
		$this->saveTags();
		$this->saveOptions($item, 'replace');

		if (Utils::$context['lp_page']['author_id'] !== User::$info['id']) {
			$title = Utils::$context['lp_page']['titles'][User::$info['language']];
			Logging::logAction('update_lp_page', [
				'page' => '<a href="' . LP_PAGE_URL . Utils::$context['lp_page']['alias'] . '">' . $title . '</a>'
			]);
		}

		Db::$db->transaction('commit');
	}

	private function saveTags(): void
	{
		$newTagIds = array_diff(Utils::$context['lp_page']['keywords'], array_keys(Utils::$context['lp_tags']));
		$oldTagIds = array_intersect(Utils::$context['lp_page']['keywords'], array_keys(Utils::$context['lp_tags']));

		array_walk($newTagIds, function (&$item) {
			$item = ['value' => $item];
		});

		if ($newTagIds) {
			$newTagIds = Db::$db->insert('',
				'{db_prefix}lp_tags',
				[
					'value' => 'string',
				],
				$newTagIds,
				['tag_id'],
				2
			);

			Utils::$context['lp_num_queries']++;
		}

		Utils::$context['lp_page']['options']['keywords'] = array_merge($oldTagIds, $newTagIds);
	}

	private function prepareDescription(): void
	{
		$this->cleanBbcode(Utils::$context['lp_page']['description']);

		Utils::$context['lp_page']['description'] = strip_tags(Utils::$context['lp_page']['description']);
	}

	private function prepareKeywords(): void
	{
		// Remove all punctuation symbols
		Utils::$context['lp_page']['keywords'] = preg_replace(
			"#[[:punct:]]#", "", Utils::$context['lp_page']['keywords']
		);
	}

	private function getPublishTime(): int
	{
		$publishTime = time();

		if (Utils::$context['lp_page']['date'])
			$publishTime = strtotime(Utils::$context['lp_page']['date']);

		if (Utils::$context['lp_page']['time']) {
			$publishTime = strtotime(
				date('Y-m-d', $publishTime) . ' ' . Utils::$context['lp_page']['time']
			);
		}

		return $publishTime;
	}

	private function getAutoIncrementValue(): int
	{
		$result = Db::$db->query('', /** @lang text */ "
			SELECT setval('{db_prefix}lp_pages_seq', (SELECT MAX(page_id) FROM {db_prefix}lp_pages))"
		);

		[$value] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return (int) $value + 1;
	}
}
