<?php declare(strict_types=1);

/**
 * @package TagList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 26.10.25
 */

namespace LightPortal\Plugins\TagList;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use LightPortal\Actions\TagIndex;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Enums\Tab;
use LightPortal\Plugins\Block;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\UI\Fields\CheckboxField;
use LightPortal\UI\Fields\RadioField;
use LightPortal\Utils\Str;
use Laminas\Db\Sql\Predicate\Expression;

use function LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-tags')]
class TagList extends Block
{
	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'link_in_title' => PortalSubAction::TAGS->url(),
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

		$select = $this->sql->select()
			->from(['ok' => 'optimus_keywords'])
			->columns([
				'id',
				'name',
				'frequency' => new Expression('COUNT(olk.keyword_id)')
			])
			->join(['olk' => 'optimus_log_keywords'], 'ok.id = olk.keyword_id')
			->group(['ok.id', 'ok.name'])
			->order($sort);

		$result = $this->sql->execute($select);

		$keywords = [];
		foreach ($result as $row) {
			$keywords[] = [
				'title'     => $row['name'],
				'link'      => Config::$scripturl . '?action=keywords;id=' . $row['id'],
				'frequency' => $row['frequency'],
			];
		}

		return $keywords;
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		$source  = Str::typed('string', $parameters['source'], default: 'lp_tags');
		$sorting = Str::typed('string', $parameters['sorting'], default: 'name');
		$asCloud = Str::typed('boolExtended', $parameters['as_cloud']);

		if ($source === 'lp_tags') {
			$tagList = $this->userCache($this->name . '_addon_b' . $e->args->id)
				->setLifeTime($e->args->cacheTime)
				->setFallback(fn() => app(TagIndex::class)->getAll(sort: $sorting === 'name' ? 'title' : 'frequency DESC'));
		} else {
			$tagList = $this->userCache($this->name . '_addon_b' . $e->args->id)
				->setLifeTime($e->args->cacheTime)
				->setFallback(fn() => $this->getAllTopicKeywords($sorting === 'name' ? 'ok.name' : 'frequency DESC'));
		}

		if (! $tagList) {
			echo Lang::$txt['lp_no_tags'];

			return;
		}

		if ($asCloud) {
			require_once __DIR__ . '/vendor/autoload.php';

			$cloud = new TagCloud([
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
					Str::html('span', ['class' => 'amt'])
						->setText($tag['frequency'])
				);
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
