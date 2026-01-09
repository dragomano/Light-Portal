<?php declare(strict_types=1);

/**
 * @package RecentAttachments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 06.11.25
 */

namespace LightPortal\Plugins\RecentAttachments;

use Bugo\Compat\Theme;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Plugins\SsiBlock;
use LightPortal\UI\Fields\NumberField;
use LightPortal\UI\Fields\TextField;
use LightPortal\Utils\Str;
use Ramsey\Collection\Map\NamedParameterMap;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-paperclip')]
class RecentAttachments extends SsiBlock
{
	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'num_attachments' => 5,
			'extensions'      => 'jpg',
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'num_attachments' => FILTER_VALIDATE_INT,
			'extensions'      => FILTER_DEFAULT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		NumberField::make('num_attachments', $this->txt['num_attachments'])
			->setAttribute('min', 1)
			->setValue($options['num_attachments']);

		TextField::make('extensions', $this->txt['extensions'])
			->setDescription($this->txt['extensions_subtext'])
			->setAttribute('maxlength', 30)
			->setAttribute('style', 'width: 100%')
			->setValue($options['extensions']);
	}

	public function getData(NamedParameterMap $parameters): array
	{
		$extensions = array_filter(explode(',', $parameters['extensions'] ?? ''));

		return $this->getFromSSI(
			'recentAttachments',
			Str::typed('int', $parameters['num_attachments'], default: 5),
			$extensions,
			'array'
		);
	}

	public function prepareContent(Event $e): void
	{
		[$id, $parameters] = [$e->args->id, $e->args->parameters];

		$attachmentList = $this->userCache($this->name . '_addon_b' . $id)
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $this->getData($parameters));

		if (empty($attachmentList))
			return;

		$fancybox = class_exists('FancyBox');

		$recentAttachments = Str::html('div')
			->class('recent_attachments' . ($this->isInSidebar($id) ? ' column_direction' : ''));

		foreach ($attachmentList as $attach) {
			$item = Str::html('div', ['class' => 'item']);

			if ($attach['file']['image']) {
				$link = Str::html('a', [
					'href' => $attach['file']['href'] . ';image',
				]);

				if ($fancybox) {
					$link->class('fancybox')->setAttribute('data-fancybox', 'recent_attachments_' . $id);
				}

				$link->setHtml($attach['file']['image']['thumb']);
				$item->addHtml($link);
			} else {
				$link = Str::html('a', [
					'href' => $attach['file']['href'],
				]);

				$link->addHtml(Str::html('img', [
					'class' => 'centericon',
					'src' => Theme::$current->settings['images_url'] . '/icons/clip.png',
					'alt' => $attach['file']['filename'],
				]));

				$link->addHtml(' ' . $attach['file']['filename']);
				$item->addHtml($link)->addHtml(' (' . $attach['file']['filesize'] . ')');
			}

			$recentAttachments->addHtml($item);
		}

		echo $recentAttachments;
	}
}
