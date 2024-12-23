<?php declare(strict_types=1);

/**
 * @package TagList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\TagList;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\RadioField;
use Bugo\LightPortal\Utils\Str;
use Laminas\Tag\Cloud;

use function array_combine;
use function array_map;
use function class_exists;

if (! defined('LP_NAME'))
	die('No direct access...');

class TagList extends Block
{
	public string $icon = 'fas fa-tags';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'link_in_title' => Config::$scripturl . '?action=portal;sa=tags',
			'source'        => 'lp_tags',
			'sorting'       => 'name',
			'as_cloud'      => false,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'source'   => FILTER_DEFAULT,
			'sorting'  => FILTER_DEFAULT,
			'as_cloud' => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		$sources = array_combine(['lp_tags', 'keywords'], $this->txt['source_set']);

		if (! class_exists('\Bugo\Optimus\Handlers\TagHandler')) {
			unset($sources['keywords']);
		}

		RadioField::make('source', $this->txt['source'])
			->setTab(Tab::CONTENT)
			->setOptions($sources)
			->setValue($options['source']);

		RadioField::make('sorting', $this->txt['sorting'])
			->setTab(Tab::CONTENT)
			->setOptions(array_combine(['name', 'frequency'], $this->txt['sorting_set']))
			->setValue($options['sorting']);

		CheckboxField::make('as_cloud', $this->txt['as_cloud'])
			->setTab(Tab::APPEARANCE)
			->setValue($options['as_cloud']);
	}

	public function getAllTopicKeywords(string $sort = 'ok.name'): array
	{
		if (! class_exists('\Bugo\Optimus\Handlers\TagHandler'))
			return [];

		$result = Db::$db->query('', '
			SELECT ok.id, ok.name, COUNT(olk.keyword_id) AS frequency
			FROM {db_prefix}optimus_keywords AS ok
				INNER JOIN {db_prefix}optimus_log_keywords AS olk ON (ok.id = olk.keyword_id)
			GROUP BY ok.id, ok.name
			ORDER BY {raw:sort}',
			[
				'sort' => $sort,
			]
		);

		$keywords = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$keywords[] = [
				'title'     => $row['name'],
				'link'      => Config::$scripturl . '?action=keywords;id=' . $row['id'],
				'frequency' => $row['frequency'],
			];
		}

		Db::$db->free_result($result);

		return $keywords;
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		if ($parameters['source'] === 'lp_tags') {
			$tagList = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
				->setLifeTime($e->args->cacheTime)
				->setFallback(fn() => app('tag')->getAll(0, 0, $parameters['sorting'] === 'name' ? 'title' : 'frequency DESC'));
		} else {
			$tagList = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
				->setLifeTime($e->args->cacheTime)
				->setFallback(fn() => $this->getAllTopicKeywords($parameters['sorting'] === 'name' ? 'ok.name' : 'frequency DESC'));
		}

		if ($tagList) {
			if ($parameters['as_cloud']) {
				require_once __DIR__ . '/vendor/autoload.php';

				$cloud = new Cloud([
					'tags' => array_map(fn($item) => [
						'title'  => $item['title'],
						'params' => ['url' => $item['link']],
						'weight' => $item['frequency'],
					], $tagList),
				]);

				echo $cloud;

				return;
			}

			foreach ($tagList as $tag) {
				echo Str::html('a', ['href' => $tag['link'], 'class' => 'button'])
					->setHtml(
					($tag['icon'] ?? '') .
						$tag['title'] .	' ' .
						Str::html('span', ['class' => 'amt'])->setText($tag['frequency'])
					);
			}
		} else {
			echo Lang::$txt['lp_no_tags'];
		}
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'laminas-tag',
			'link' => 'https://github.com/laminas/laminas-tag/',
			'author' => 'Laminas Project a Series of LF Projects, LLC.',
			'license' => [
				'name' => 'the BSD-3-Clause License',
				'link' => 'https://github.com/laminas/laminas-tag/#BSD-3-Clause-1-ov-file'
			]
		];
	}
}
