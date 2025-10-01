<?php declare(strict_types=1);

/**
 * @package ArticleList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 01.10.25
 */

namespace Bugo\LightPortal\Plugins\ArticleList;

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Parsers\BBCodeParser;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\HookAttribute;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Fields\RadioField;
use Bugo\LightPortal\UI\Partials\ContentClassSelect;
use Bugo\LightPortal\UI\Partials\PageSelect;
use Bugo\LightPortal\UI\Partials\TopicSelect;
use Bugo\LightPortal\Utils\Content;
use Bugo\LightPortal\Utils\ParamWrapper;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'far fa-file-alt', showContentClass: false)]
class ArticleList extends Block
{
	#[HookAttribute(PortalHook::prepareBlockParams)]
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

	#[HookAttribute(PortalHook::validateBlockParams)]
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

	#[HookAttribute(PortalHook::prepareBlockFields)]
	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CustomField::make('body_class', $this->txt['body_class'])
			->setTab(Tab::APPEARANCE)
			->setValue(static fn() => new ContentClassSelect(), [
				'id'    => 'body_class',
				'value' => $options['body_class'],
			]);

		RadioField::make('display_type', $this->txt['display_type'])
			->setTab(Tab::CONTENT)
			->setOptions($this->txt['display_type_set'])
			->setValue($options['display_type']);

		CustomField::make('include_topics', $this->txt['include_topics'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new TopicSelect(), [
				'id'    => 'include_topics',
				'hint'  => $this->txt['include_topics_select'],
				'value' => $options['include_topics'] ?? '',
			]);

		CustomField::make('include_pages', $this->txt['include_pages'])
			->setTab(Tab::CONTENT)
			->setValue(static fn() => new PageSelect(), [
				'id'    => 'include_pages',
				'hint'  => $this->txt['include_pages_select'],
				'value' => $options['include_pages'] ?? '',
			]);

		CheckboxField::make('seek_images', $this->txt['seek_images'])
			->setValue($options['seek_images']);
	}

	#[HookAttribute(PortalHook::prepareContent)]
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

		$result = Db::$db->query('
			SELECT m.id_topic, m.id_msg, m.subject, m.body, m.smileys_enabled
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (t.id_first_msg = m.id_msg)
				INNER JOIN {db_prefix}boards AS b ON (t.id_board = b.id_board)
			WHERE t.id_topic IN ({array_int:topics})
				AND {query_wanna_see_board}
				AND t.approved = {int:is_approved}
				AND m.approved = {int:is_approved}
			ORDER BY t.id_last_msg DESC',
			[
				'topics'      => explode(',', (string) $parameters['include_topics']),
				'is_approved' => 1,
			]
		);

		$topics = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['subject']);
			Lang::censorText($row['body']);

			$value = null;
			$image = empty($parameters['seek_images'])
				? ''
				: preg_match('/\[img.*]([^]\[]+)\[\/img]/U', $row['body'], $value);
			$image = $value ? array_pop($value) : ($image ?: Config::$modSettings['lp_image_placeholder'] ?? '');

			$body = BBCodeParser::load()->parse($row['body'], (bool) $row['smileys_enabled'], $row['id_msg']);

			$topics[$row['id_topic']] = [
				'id'          => $row['id_topic'],
				'title'       => $row['subject'],
				'description' => Str::getTeaser($body),
				'image'       => $image,
			];
		}

		Db::$db->free_result($result);

		return $topics;
	}

	public function getPages(ParamWrapper $parameters): array
	{
		if (empty($parameters['include_pages']))
			return [];

		$result = Db::$db->query('
			SELECT
				p.page_id, p.slug, p.type,
				COALESCE(NULLIF(t.title, {string:empty_string}), tf.title, {string:empty_string}) AS title,
				COALESCE(NULLIF(t.content, {string:empty_string}), tf.content, {string:empty_string}) AS content,
				COALESCE(NULLIF(t.description, {string:empty_string}), tf.description, {string:empty_string}) AS description
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang}
				)
			WHERE p.status = {int:status}
				AND p.entry_type = {string:entry_type}
				AND p.deleted_at = 0
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
				AND p.page_id IN ({array_int:pages})
			ORDER BY p.page_id DESC',
			[
				'empty_string'  => '',
				'lang'          => User::$me->language,
				'fallback_lang' => Config::$language,
				'status'        => Status::ACTIVE->value,
				'entry_type'    => EntryType::DEFAULT->name(),
				'current_time'  => time(),
				'permissions'   => Permission::all(),
				'pages'         => explode(',', (string) $parameters['include_pages']),
			]
		);

		$pages = [];
		while ($row = Db::$db->fetch_assoc($result)) {
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

		Db::$db->free_result($result);

		return $pages;
	}
}
