<?php declare(strict_types=1);

/**
 * @package Optimus (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 29.10.25
 */

namespace LightPortal\Plugins\Optimus;

use Bugo\Compat\Config;
use LightPortal\Enums\PluginType;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\Plugin;
use Bugo\Optimus\Prime;
use LightPortal\Plugins\SettingsFactory;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::ARTICLE)]
class Optimus extends Plugin
{
	public function addSettings(Event $e): void
	{
		$e->args->settings[$this->name] = SettingsFactory::make()
			->check('use_topic_descriptions')
			->check('show_topic_keywords')
			->toArray();
	}

	public function frontTopics(Event $e): void
	{
		if (empty($this->context['use_topic_descriptions']) || ! $this->isOptimusLoaded())
			return;

		$e->args->columns[] = 'optimus_description';
	}

	public function frontTopicsRow(Event $e): void
	{
		if (! $this->isOptimusLoaded())
			return;

		$topics = &$e->args->articles;
		$row = $e->args->row;

		if (! empty($this->context['show_topic_keywords'])) {
			$topics[$row['id_topic']]['tags'] = $this->cache('topic_keywords')
				->setFallback(fn() => $this->getKeywords($row['id_topic']));
		}

		if (
			! empty($this->context['use_topic_descriptions'])
			&& ! empty($row['optimus_description'])
			&& ! empty($topics[$row['id_topic']]['teaser'])
		) {
			$topics[$row['id_topic']]['teaser'] = $row['optimus_description'];
		}
	}

	public function getKeywords(int $topic): array
	{
		if (empty($topic))
			return [];

		$select = $this->sql->select()
			->from(['ok' => 'optimus_keywords'])
			->join(
				['olk' => 'optimus_log_keywords'],
				'ok.id = olk.keyword_id',
				['topic_id']
			)
			->order('olk.topic_id')
			->order('ok.id');

		$result = $this->sql->execute($select);

		$keywords = [];
		foreach ($result as $row) {
			$keywords[$row['topic_id']][] = [
				'name' => $row['name'],
				'href' => Config::$scripturl . '?action=keywords;id=' . $row['id'],
			];
		}

		return $keywords[$topic] ?? [];
	}

	private function isOptimusLoaded(): bool
	{
		return class_exists(Prime::class);
	}
}
