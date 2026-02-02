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

namespace LightPortal\DataHandlers;

use Bugo\Compat\Config;
use Bugo\Compat\User;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\DataHandlers\Traits\HasInserts;
use LightPortal\DataHandlers\Traits\HasParams;
use LightPortal\DataHandlers\Traits\HasTransactions;
use LightPortal\DataHandlers\Traits\HasTranslations;
use LightPortal\Utils\ErrorHandlerInterface;

use const LP_ALIAS_PATTERN;

if (! defined('SMF'))
	die('No direct access...');

abstract class DataHandler implements DataHandlerInterface
{
	use HasInserts;
	use HasParams;
	use HasTransactions;
	use HasTranslations;

	protected string $entity;

	public function __construct(protected PortalSqlInterface $sql, protected ErrorHandlerInterface $errorHandler) {}

	abstract public function main(): void;

	protected function generateSlug(array $titles): string
	{
		if (empty($titles)) {
			return $this->getShortPrefix() . '-' . $this->generateShortId();
		}

		$selectedTitle = $this->selectTitleByPriority($titles);
		$slug = $this->cleanAndFormatSlug($selectedTitle);

		return $slug ?: $this->getShortPrefix() . '-' . $this->generateShortId();
	}

	protected function selectTitleByPriority(array $titles): string
	{
		$priority = ['english', Config::$language ?? 'english', User::$me->language ?? 'english'];

		foreach ($priority as $lang) {
			if (isset($titles[$lang]) && ! empty(trim($titles[$lang]))) {
				return $titles[$lang];
			}
		}

		return reset($titles) ?: '';
	}

	protected function cleanAndFormatSlug(string $text): string
	{
		$slug = strtolower(trim($text));
		$slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
		$slug = preg_replace('/\s+/', '-', $slug);
		$slug = preg_replace('/-+/', '-', $slug);
		$slug = trim($slug, '-');

		if (! preg_match('/' . LP_ALIAS_PATTERN . '/', $slug)) {
			$slug = $this->getShortPrefix() . $slug;
		}

		if (strlen($slug) > 255) {
			$slug = substr($slug, 0, 255);
			$slug = rtrim($slug, '-');
		}

		return $slug;
	}

	protected function getShortPrefix(): string
	{
		return match ($this->entity) {
			'categories' => 'cat',
			'pages'      => 'page',
			'blocks'     => 'block',
			default      => 'item',
		};
	}

	protected function generateShortId(): string
	{
		return substr((string) time(), -6);
	}
}
