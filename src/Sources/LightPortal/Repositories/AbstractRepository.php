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

namespace LightPortal\Repositories;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Msg;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Laminas\Db\Sql\Predicate\Expression;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Database\PortalTransactionInterface;
use LightPortal\Enums\ContentType;
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

	public function __construct(protected PortalSqlInterface $sql)
	{
		$this->transaction = $this->sql->getTransaction();
	}

	public function toggleStatus(array $items = []): void
	{
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

	public function getTranslationFilter(
		string $tableAlias = 'p',
		string $idField = 'page_id',
		array $fields = ['title', 'content', 'description']
	): Expression
	{
		$fieldConditions = [];
		foreach ($fields as $field) {
			$fieldConditions[] = "$field != ''";
		}

		$fieldsSql = implode(' OR ', $fieldConditions);

		$where = "item_id = $tableAlias.$idField AND type = ? AND lang IN (?, ?) AND ($fieldsSql)";
		$sql = "EXISTS (SELECT 1 FROM {$this->sql->getPrefix()}lp_translations WHERE $where)";

		$params = $this->getLangQueryParams();

		return new Expression($sql, [$this->entity, $params['lang'], $params['fallback_lang']]);
	}

	protected function prepareBbcContent(array &$entity): void
	{
		if ($entity['type'] !== ContentType::BBC->name())
			return;

		$entity['content'] = Utils::htmlspecialchars($entity['content'], ENT_QUOTES);

		Msg::preparseCode($entity['content']);
	}

	protected function getLangQueryParams(): array
	{
		return [
			'lang'          => User::$me->language,
			'fallback_lang' => Config::$language,
			'guest'         => Lang::$txt['guest_title'],
		];
	}

	protected function saveTranslations(int $item, bool $replace = false): void
	{
		$values = [
			'item_id'     => $item,
			'type'        => $this->entity,
			'lang'        => User::$me->language,
			'title'       => Utils::$context['lp_' . $this->entity]['title'] ?? '',
			'content'     => Utils::$context['lp_' . $this->entity]['content'] ?? '',
			'description' => Utils::htmlspecialchars(Utils::$context['lp_' . $this->entity]['description'] ?? ''),
		];

		$sqlObject = $replace
			? $this->sql->replace('lp_translations')->setConflictKeys(['item_id', 'type', 'lang'])->values($values)
			: $this->sql->insert('lp_translations')->values($values);

		if (! Language::isDefault()) {
			$default = $this->getDefaultTranslations($item);

			foreach (['title', 'content', 'description'] as $field) {
				if ($values[$field] === $default[$field]) {
					unset($values[$field]);
				}
			}
		}

		$this->sql->execute($sqlObject);
	}

	protected function saveOptions(int $item, bool $replace = false): void
	{
		if (empty(Utils::$context['lp_' . $this->entity]['options']))
			return;

		$rows = [];
		foreach (Utils::$context['lp_' . $this->entity]['options'] as $name => $value) {
			$value = is_array($value) ? implode(',', $value) : $value;
			$rows[] = [
				'item_id' => $item,
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
