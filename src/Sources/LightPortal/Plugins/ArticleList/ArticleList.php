<?php declare(strict_types=1);

/**
 * @package ArticleList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 17.10.25
 */

namespace LightPortal\Plugins\ArticleList;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Parsers\BBCodeParser;
use LightPortal\Enums\ContentClass;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\Status;
use LightPortal\Enums\Tab;
use LightPortal\Plugins\Block;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\UI\Fields\CheckboxField;
use LightPortal\UI\Fields\CustomField;
use LightPortal\UI\Fields\RadioField;
use LightPortal\UI\Partials\SelectFactory;
use LightPortal\Utils\Content;
use LightPortal\Utils\ForumPermissions;
use LightPortal\Utils\ParamWrapper;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasTranslationJoins;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'far fa-file-alt', showContentClass: false)]
class ArticleList extends Block
{
	use HasTranslationJoins;

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'body_class'     => 'descbox',
			'display_type'   => 0,
			'include_topics' => '',
			'include_pages'  => '',
			'seek_images'    => false,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'body_class'     => FILTER_DEFAULT,
			'display_type'   => FILTER_VALIDATE_INT,
			'include_topics' => FILTER_DEFAULT,
			'include_pages'  => FILTER_DEFAULT,
			'seek_images'    => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CustomField::make('body_class', $this->txt['body_class'])
			->setTab(Tab::APPEARANCE)
			->setValue(static fn() => SelectFactory::contentClass([
				'id'    => 'body_class',
				'value' => $options['body_class'],
			]));

		RadioField::make('display_type', $this->txt['display_type'])
			->setTab(Tab::CONTENT)
			->setOptions($this->txt['display_type_set'])
			->setValue(Str::typed('int', $options['display_type']));

		CustomField::make('include_topics', $this->txt['include_topics'])
			->setTab(Tab::CONTENT)
			->setValue(fn() => SelectFactory::topic([
				'id'    => 'include_topics',
				'hint'  => $this->txt['include_topics_select'],
				'value' => $options['include_topics'] ?? '',
			]));

		CustomField::make('include_pages', $this->txt['include_pages'])
			->setTab(Tab::CONTENT)
			->setValue(fn() => SelectFactory::page([
				'id'    => 'include_pages',
				'hint'  => $this->txt['include_pages_select'],
				'value' => $options['include_pages'] ?? '',
			]));

		CheckboxField::make('seek_images', $this->txt['seek_images'])
			->setValue($options['seek_images']);
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		$type = Str::typed('int', $parameters['display_type']);

		$articles = $this->langCache($this->name . '_addon_b' . $e->args->id)
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $type === 0 ? $this->getTopics($parameters) : $this->getPages($parameters));

		if ($articles) {
			$articleList = Str::html('div')->class($this->name);
			$bodyClass = Str::typed('string', $parameters['body_class']);

			if ($type === 0) {
				foreach ($articles as $topic) {
					$content = Str::html();

					if ($topic['image']) {
						$content->addHtml(
							Str::html('div')
								->class('article_image')
								->addHtml(
									Str::html('img')
										->src($topic['image'])
										->addAttributes(['loading' => 'lazy', 'alt' => $topic['title']])
								)
						);
					}

					$content->addHtml(
						Str::html('a')
							->href(Config::$scripturl . '?topic=' . $topic['id'] . '.0')
							->setText($topic['title'])
					);

					$articleList->addHtml(sprintf(ContentClass::values()[$bodyClass], $content));
				}
			} else {
				foreach ($articles as $page) {
					if (empty($title = $page['title'])) {
						continue;
					}

					$content = Str::html();

					if ($page['image']) {
						$content->addHtml(
							Str::html('div')
								->class('article_image')
								->addHtml(
									Str::html('img')
										->src($page['image'])
										->addAttributes(['loading' => 'lazy', 'alt' => $title])
								)
						);
					}

					$content->addHtml(
						Str::html('a', $title)
							->href(LP_PAGE_URL . $page['slug'])
					);

					$articleList->addHtml(
						sprintf(ContentClass::values()[$bodyClass], $content)
					);
				}
			}

			echo $articleList;
		} else {
			echo Str::html('div', $this->txt['no_items'])
				->class('errorbox');
		}
	}

	public function getTopics(ParamWrapper $parameters): array
	{
		if (empty($parameters['include_topics']))
			return [];

		$select = $this->sql->select()
			->from(['t' => 'topics'])
			->columns([])
			->join(
				['m' => 'messages'],
				't.id_first_msg = m.id_msg',
				['id_topic', 'id_msg', 'subject', 'body', 'smileys_enabled']
			)
			->join(['b' => 'boards'], 't.id_board = b.id_board', [])
			->where(['t.id_topic' => explode(',', (string) $parameters['include_topics'])])
			->where(['t.approved' => 1, 'm.approved' => 1]);

		if (ForumPermissions::shouldApplyBoardPermissionCheck()) {
			$select->where(ForumPermissions::canSeeBoard());
		}

		$select->order('t.id_last_msg DESC');

		$result = $this->sql->execute($select);

		$topics = [];
		foreach ($result as $row) {
			Lang::censorText($row['subject']);
			Lang::censorText($row['body']);

			$body = BBCodeParser::load()->parse($row['body'], (bool) $row['smileys_enabled'], $row['id_msg']);

			$topics[$row['id_topic']] = [
				'id'          => $row['id_topic'],
				'title'       => $row['subject'],
				'description' => Str::getTeaser($body),
				'image'       => $this->getImage($row['body'], ! empty($parameters['seek_images'])),
			];
		}

		return $topics;
	}

	public function getPages(ParamWrapper $parameters): array
	{
		if (empty($parameters['include_pages']))
			return [];

		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['page_id', 'slug', 'type'])
			->where([
				'p.status'          => Status::ACTIVE->value,
				'p.entry_type'      => EntryType::DEFAULT->name(),
				'p.deleted_at'      => 0,
				'p.created_at <= ?' => time(),
			])
			->where(['p.permissions' => Permission::all()])
			->where(['p.page_id' => explode(',', (string) $parameters['include_pages'])])
			->order('p.page_id DESC');

		$this->addTranslationJoins($select, ['fields' => ['title', 'content', 'description']]);

		$result = $this->sql->execute($select);

		$pages = [];
		foreach ($result as $row) {
			if (Setting::isFrontpage($row['slug']))
				continue;

			Lang::censorText($row['title']);
			Lang::censorText($row['content']);
			Lang::censorText($row['description']);

			$row['content'] = Content::parse($row['content'], $row['type']);

			$image = empty($parameters['seek_images']) ? '' : Str::getImageFromText($row['content']);

			$pages[$row['page_id']] = [
				'id'          => $row['page_id'],
				'slug'        => $row['slug'],
				'image'       => $image ?: (Config::$modSettings['lp_image_placeholder'] ?? ''),
				'title'       => $row['title'],
				'description' => Str::getTeaser($row['description'] ?: strip_tags($row['content'])),
			];
		}

		return $pages;
	}

	private function getImage(mixed $body, bool $seekImages = true)
	{
		$value = null;
		$image = $seekImages ? preg_match('/\[img.*]([^]\[]+)\[\/img]/U', $body, $value) : '';

		return $value ? array_pop($value) : ($image ?: Config::$modSettings['lp_image_placeholder'] ?? '');
	}
}
