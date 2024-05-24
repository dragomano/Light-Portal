<?php

/**
 * RecentAttachments.php
 *
 * @package RecentAttachments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 24.05.24
 */

namespace Bugo\LightPortal\Addons\RecentAttachments;

use Bugo\Compat\{Lang, Theme, User, Utils};
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{NumberField, TextField};

if (! defined('LP_NAME'))
	die('No direct access...');

class RecentAttachments extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-paperclip';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'recent_attachments')
			return;

		$params = [
			'num_attachments' => 5,
			'extensions'      => 'jpg',
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'recent_attachments')
			return;

		$params = [
			'num_attachments' => FILTER_VALIDATE_INT,
			'extensions'      => FILTER_DEFAULT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'recent_attachments')
			return;

		NumberField::make('num_attachments', Lang::$txt['lp_recent_attachments']['num_attachments'])
			->setAttribute('min', 1)
			->setValue(Utils::$context['lp_block']['options']['num_attachments']);

		TextField::make('extensions', Lang::$txt['lp_recent_attachments']['extensions'])
			->setAfter(Lang::$txt['lp_recent_attachments']['extensions_subtext'])
			->setAttribute('maxlength', 30)
			->setAttribute('style', 'width: 100%')
			->setValue(Utils::$context['lp_block']['options']['extensions']);
	}

	public function getData(array $parameters): array
	{
		$extensions = empty($parameters['extensions']) ? [] : explode(',', (string) $parameters['extensions']);

		return $this->getFromSsi('recentAttachments', $parameters['num_attachments'], $extensions, 'array');
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'recent_attachments')
			return;

		$attachmentList = $this->cache('recent_attachments_addon_b' . $data->id . '_u' . User::$info['id'])
			->setLifeTime($data->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($attachmentList))
			return;

		$fancybox = class_exists('FancyBox');

		echo '
		<div class="recent_attachments' . ($this->isInSidebar($data->id) ? ' column_direction' : '') . '">';

		foreach ($attachmentList as $attach) {
			if ($attach['file']['image']) {
				echo '
			<div class="item">
				<a', ($fancybox ? ' class="fancybox" data-fancybox="recent_attachments_' . $data->id . '"' : ''), ' href="', $attach['file']['href'], ';image">', $attach['file']['image']['thumb'], '</a>
			</div>';
			} else {
				echo '
			<div class="item">
				<a href="', $attach['file']['href'], '">
					<img class="centericon" src="', Theme::$current->settings['images_url'], '/icons/clip.png" alt="', $attach['file']['filename'], '"> ', $attach['file']['filename'], '
				</a> (', $attach['file']['filesize'], ')
			</div>';
			}
		}

		echo '
		</div>';
	}
}
