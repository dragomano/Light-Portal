<?php

/**
 * @package RecentComments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 13.11.24
 */

namespace Bugo\LightPortal\Plugins\RecentComments;

use Bugo\Compat\{Db, Lang, User};
use Bugo\LightPortal\Areas\Fields\{NumberField, RangeField};
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Plugins\{Block, Event};
use Bugo\LightPortal\Utils\{DateTime, Str};
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class RecentComments extends Block
{
	public string $icon = 'fas fa-comments';

	public function prepareBlockParams(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$e->args->params = [
			'no_content_class' => true,
			'num_comments'     => 10,
			'length'           => 80,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$e->args->params = [
			'num_comments' => FILTER_VALIDATE_INT,
			'length'       => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$options = $e->args->options;

		NumberField::make('num_comments', $this->txt['num_comments'])
			->setAttribute('min', 1)
			->setValue($options['num_comments']);

		RangeField::make('length', $this->txt['length'])
			->setAttribute('min', 10)
			->setValue($options['length']);
	}

	public function getData(int $commentsCount, int $length = 80): array
	{
		if (empty($commentsCount))
			return [];

		$result = Db::$db->query('', '
			SELECT DISTINCT com.id, com.page_id, com.message, com.created_at, p.slug,
				COALESCE(mem.real_name, {string:guest}) AS author_name,
				(
					SELECT COUNT(*) FROM {db_prefix}lp_comments AS com2
					WHERE com2.parent_id = 0 AND com2.page_id = com.page_id
				) AS num_comments
			FROM {db_prefix}lp_comments AS com
				INNER JOIN (
					SELECT lt.page_id AS page_id, MAX(lt.created_at) AS created_at
					FROM {db_prefix}lp_comments AS lt
					GROUP BY lt.page_id
				) AS latest_comments ON (
					com.page_id = latest_comments.page_id AND com.created_at = latest_comments.created_at
				)
				LEFT JOIN {db_prefix}lp_pages AS p ON (p.page_id = com.page_id)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = com.author_id)
				LEFT JOIN {db_prefix}lp_params AS par ON (
					par.item_id = com.page_id AND par.type = {literal:page} AND par.name = {literal:allow_comments}
				)
			WHERE p.status = {int:status}
				AND p.deleted_at = 0
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
				AND par.value > 0
			ORDER BY com.created_at DESC
			LIMIT {int:limit}',
			[
				'guest'        => Lang::$txt['guest_title'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => Permission::all(),
				'limit'        => $commentsCount
			]
		);

		$comments = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['message']);

			$comments[$row['id']] = [
				'link'        => LP_PAGE_URL . $row['slug'] . '#comment=' . $row['id'],
				'message'     => Str::getTeaser($row['message'], $length),
				'created_at'  => (int) $row['created_at'],
				'author_name' => $row['author_name'],
			];
		}

		Db::$db->free_result($result);

		return $comments;
	}

	/**
	 * @throws IntlException
	 */
	public function prepareContent(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$parameters = $e->args->parameters;

		$comments = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(self::class, 'getData', (int) $parameters['num_comments'], (int) $parameters['length']);

		if (empty($comments))
			return;

		echo Str::html('ul', ['class' => $this->name . ' noup'])
			->addHtml(
				implode('', array_map(fn($comment) => Str::html('li', ['class' => 'windowbg'])
					->addHtml(Str::html('a')
						->href($comment['link'])
						->setText($comment['message']))
					->addHtml('<br>')
					->addHtml(Str::html('span', ['class' => 'smalltext'])
						->setText(Lang::$txt['by'] . ' ' . $comment['author_name']))
					->addHtml('<br>')
					->addHtml(Str::html('span', ['class' => 'smalltext'])
						->setText(DateTime::relative($comment['created_at']))), $comments))
			);
	}
}
