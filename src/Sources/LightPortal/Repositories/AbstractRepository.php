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

namespace LightPortal\Repositories;

use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Msg;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Exception;
use Laminas\Db\Sql\Predicate\Expression;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Database\PortalTransactionInterface;
use LightPortal\Enums\ContentType;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Utils\Language;
use LightPortal\Utils\Traits\HasCache;
use LightPortal\Utils\Traits\HasParamJoins;
use LightPortal\Utils\Traits\HasRequest;
use LightPortal\Utils\Traits\HasResponse;
use LightPortal\Utils\Traits\HasSession;
use LightPortal\Utils\Traits\HasTranslationJoins;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractRepository implements RepositoryInterface
{
	use HasCache;
	use HasParamJoins;
	use HasRequest;
	use HasResponse;
	use HasSession;
	use HasTranslationJoins;

	protected string $entity;

	protected PortalTransactionInterface $transaction;

	public function __construct(protected PortalSqlInterface $sql, protected EventDispatcherInterface $dispatcher)
	{
		$this->transaction = $this->sql->getTransaction();
	}

	public function toggleStatus(mixed $items = []): void
	{
		$items = (array) $items;

		if ($items === [])
			return;

		$table = match ($this->entity) {
			'category' => 'categories',
			default    => $this->entity . 's',
		};

		$tableName = 'lp_' . $table;

		$caseExpression = "CASE status WHEN 1 THEN 0 WHEN 0 THEN 1 WHEN 2 THEN 1 WHEN 3 THEN 0 ELSE status END";

		$update = $this->sql->update($tableName);
		$update->set(['status' => new Expression($caseExpression)]);
		$update->where->in($this->entity . '_id', $items);

		$this->sql->execute($update);

		$this->session('lp')->free('active_' . $table);
	}

	protected function prepareBbcContent(array &$entity): void
	{
		if ($entity['type'] !== ContentType::BBC->name())
			return;

		$entity['content'] = Utils::htmlspecialchars($entity['content'], ENT_QUOTES);

		Msg::preparseCode($entity['content']);
	}

	protected function saveTranslations(array $data, bool $replace = false): void
	{
		$values = [
			'item_id'     => $data['id'],
			'type'        => $this->entity,
			'lang'        => User::$me->language,
			'title'       => $data['title'] ?? '',
			'content'     => $data['content'] ?? '',
			'description' => Utils::htmlspecialchars($data['description'] ?? ''),
		];

		$sqlObject = $replace
			? $this->sql->replace('lp_translations')->setConflictKeys(['item_id', 'type', 'lang'])->values($values)
			: $this->sql->insert('lp_translations')->values($values);

		if (! Language::isDefault()) {
			$default = $this->getDefaultTranslations($data['id']);

			foreach (['title', 'content', 'description'] as $field) {
				if ($values[$field] === $default[$field]) {
					unset($values[$field]);
				}
			}
		}

		$this->sql->execute($sqlObject);
	}

	protected function saveOptions(array $data, bool $replace = false): void
	{
		if (empty($data['options']))
			return;

		$rows = [];
		foreach ($data['options'] as $name => $value) {
			$value = is_array($value) ? implode(',', $value) : $value;
			$rows[] = [
				'item_id' => $data['id'],
				'type'    => $this->entity,
				'name'    => $name,
				'value'   => $value,
			];
		}

		if ($rows === [])
			return;

		$sqlObject = $replace
			? $this->sql->replace('lp_params')->setConflictKeys(['item_id', 'type'])->batch($rows)
			: $this->sql->insert('lp_params')->batch($rows);

		$this->sql->execute($sqlObject);
	}

	protected function executeInTransaction(callable $callback): int
	{
		try {
			$this->transaction->begin();

			$result = $callback();

			$this->transaction->commit();

			return $result ?? 0;
		} catch (Exception $e) {
			$this->transaction->rollback();

			ErrorHandler::fatal($e->getMessage(), false);

			return 0;
		}
	}

	protected function deleteRelatedData(array $items): void
	{
		$deleteTranslations = $this->sql->delete('lp_translations');
		$deleteTranslations->where->in('item_id', $items);
		$deleteTranslations->where->equalTo('type', $this->entity);
		$this->sql->execute($deleteTranslations);

		$deleteParams = $this->sql->delete('lp_params');
		$deleteParams->where->in('item_id', $items);
		$deleteParams->where->equalTo('type', $this->entity);
		$this->sql->execute($deleteParams);
	}

	private function getDefaultTranslations(int $item): array
	{
		$select = $this->sql->select('lp_translations')
			->columns(['title', 'content', 'description'])
			->where([
				'item_id = ?' => $item,
				'type = ?'    => $this->entity,
				'lang = ?'    => Config::$language,
			]);

		$result = $this->sql->execute($select)->current();

		return $result ?: ['title' => null, 'content' => null, 'description' => null];
	}
}
