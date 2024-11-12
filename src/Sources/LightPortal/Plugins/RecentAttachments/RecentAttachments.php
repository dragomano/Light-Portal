<?php

/**
 * @package RecentAttachments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 13.11.24
 */

namespace Bugo\LightPortal\Plugins\RecentAttachments;

use Bugo\Compat\{Theme, User};
use Bugo\LightPortal\Areas\Fields\{NumberField, TextField};
use Bugo\LightPortal\Plugins\{Block, Event};
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class RecentAttachments extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-paperclip';

	public function prepareBlockParams(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$e->args->params = [
			'num_attachments' => 5,
			'extensions'      => 'jpg',
		];
	}

	public function validateBlockParams(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$e->args->params = [
			'num_attachments' => FILTER_VALIDATE_INT,
			'extensions'      => FILTER_DEFAULT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

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

	public function getData(array $parameters): array
	{
		$extensions = empty($parameters['extensions']) ? [] : explode(',', (string) $parameters['extensions']);

		return $this->getFromSsi('recentAttachments', $parameters['num_attachments'], $extensions, 'array');
	}

	public function prepareContent(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$parameters = $e->args->parameters;
		$id = $e->args->id;

		$attachmentList = $this->cache($this->name . '_addon_b' . $id . '_u' . User::$info['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

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
