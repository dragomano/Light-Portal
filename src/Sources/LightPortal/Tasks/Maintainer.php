<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Tasks;

use Bugo\Compat\Tasks\BackgroundTask;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Repositories\CommentRepositoryInterface;

use function LightPortal\app;

final class Maintainer extends BackgroundTask
{
	private readonly PortalSqlInterface $sql;

	private readonly CommentRepositoryInterface $commentRepository;

	public function __construct(array $details)
	{
		parent::__construct($details);

		$this->sql = app(PortalSqlInterface::class);

		$this->commentRepository = app(CommentRepositoryInterface::class);
	}

	public function execute(): bool
	{
		@ini_set('opcache.enable', '0');

		$this->removeRedundantValues();
		$this->updateNumComments();
		$this->updateLastCommentIds();
		$this->optimizeTables();

		$insert = $this->sql->insert('background_tasks')
			->values([
				'task_file'    => '$sourcedir/LightPortal/Tasks/Maintainer.php',
				'task_class'   => '\\' . self::class,
				'task_data'    => '',
				'claimed_time' => time() + (7 * 24 * 60 * 60),
			]);

		$this->sql->execute($insert);

		return true;
	}

	private function removeRedundantValues(): void
	{
		$deleteEmptyParams = $this->sql->delete('lp_params')->where(['value = ?' => '']);
		$this->sql->execute($deleteEmptyParams);

		$select = $this->sql->select()
			->from(['c1' => 'lp_comments'])
			->columns(['id'])
			->join(['c2' => 'lp_comments'], 'c1.parent_id = c2.id', [], Select::JOIN_LEFT)
			->where(function (Where $where) {
				$where->notEqualTo('c1.parent_id', 0)
					->and->isNull('c2.id');
			});

		$result = $this->sql->execute($select);

		$commentIds = [];
		foreach ($result as $row) {
			$commentIds[] = $row['id'];
		}

		$this->commentRepository->remove($commentIds);
	}

	private function updateNumComments(): void
	{
		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['page_id', 'amount' => new Expression('COUNT(c.id)')])
			->join(['c' => 'lp_comments'], 'c.page_id = p.page_id', [], Select::JOIN_LEFT)
			->group('p.page_id')
			->order('p.page_id');

		$result = $this->sql->execute($select);

		$pages = [];
		foreach ($result as $row) {
			$pages[$row['page_id']] = $row['amount'];
		}

		if (empty($pages))
			return;

		$caseParts = [];
		foreach ($pages as $pageId => $commentsCount) {
			$caseParts[] = "WHEN page_id = $pageId THEN $commentsCount";
		}

		$caseExpression = 'CASE ' . implode(' ', $caseParts) . ' ELSE num_comments END';

		$update = $this->sql->update('lp_pages')
			->set(['num_comments' => new Expression($caseExpression)])
			->where('page_id', array_keys($pages));

		$this->sql->execute($update);
	}

	private function updateLastCommentIds(): void
	{
		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['page_id', 'last_comment_id' => new Expression('MAX(c.id)')])
			->join(['c' => 'lp_comments'], 'c.page_id = p.page_id', [], Select::JOIN_LEFT)
			->group('p.page_id')
			->order('p.page_id');

		$result = $this->sql->execute($select);

		$pages = [];
		foreach ($result as $row) {
			$pages[$row['page_id']] = $row['last_comment_id'] ?? 0;
		}

		if (empty($pages))
			return;

		$caseParts = [];
		foreach ($pages as $pageId => $lastCommentId) {
			$caseParts[] = "WHEN page_id = $pageId THEN $lastCommentId";
		}

		$caseExpression = 'CASE ' . implode(' ', $caseParts) . ' ELSE last_comment_id END';

		$update = $this->sql->update('lp_pages')
			->set(['last_comment_id' => new Expression($caseExpression)])
			->where('page_id', array_keys($pages));

		$this->sql->execute($update);
	}

	private function optimizeTables(): void
	{
		$tables = [
			'lp_blocks',
			'lp_categories',
			'lp_comments',
			'lp_page_tag',
			'lp_pages',
			'lp_params',
			'lp_plugins',
			'lp_tags',
			'lp_translations',
		];

		foreach ($tables as $table) {
			$sql = sprintf('OPTIMIZE TABLE `%s%s`', $this->sql->getPrefix(), $table);
			$this->sql->getAdapter()->query($sql, Adapter::QUERY_MODE_EXECUTE);
		}
	}
}
