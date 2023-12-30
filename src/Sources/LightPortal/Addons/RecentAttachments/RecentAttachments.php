<?php

/**
 * RecentAttachments.php
 *
 * @package RecentAttachments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.12.23
 */

namespace Bugo\LightPortal\Addons\RecentAttachments;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\NumberField;
use Bugo\LightPortal\Areas\Fields\TextField;

if (! defined('LP_NAME'))
	die('No direct access...');

class RecentAttachments extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-paperclip';

	public function blockOptions(array &$options): void
	{
		$options['recent_attachments']['parameters'] = [
			'num_attachments' => 5,
			'extensions'      => 'jpg',
		];
	}

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'recent_attachments')
			return;

		$parameters['num_attachments'] = FILTER_VALIDATE_INT;
		$parameters['extensions']      = FILTER_DEFAULT;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'recent_attachments')
			return;

		NumberField::make('num_attachments', $this->txt['lp_recent_attachments']['num_attachments'])
			->setAttribute('min', 1)
			->setValue($this->context['lp_block']['options']['parameters']['num_attachments']);

		TextField::make('extensions', $this->txt['lp_recent_attachments']['extensions'])
			->setAfter($this->txt['lp_recent_attachments']['extensions_subtext'])
			->setAttribute('maxlength', 30)
			->setAttribute('style', 'width: 100%')
			->setValue($this->context['lp_block']['options']['parameters']['extensions']);
	}

	public function getData(array $parameters): array
	{
		$extensions = empty($parameters['extensions']) ? [] : explode(',', $parameters['extensions']);

		return $this->getFromSsi('recentAttachments', $parameters['num_attachments'], $extensions, 'array');
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'recent_attachments')
			return;

		$attachment_list = $this->cache('recent_attachments_addon_b' . $data->block_id . '_u' . $this->user_info['id'])
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($attachment_list))
			return;

		$fancybox = class_exists('FancyBox');

		echo '
		<div class="recent_attachments' . ($this->isInSidebar($data->block_id) ? ' column_direction' : '') . '">';

		foreach ($attachment_list as $attach) {
			if ($attach['file']['image']) {
				echo '
			<div class="item">
				<a', ($fancybox ? ' class="fancybox" data-fancybox="recent_attachments_' . $data->block_id . '"' : ''), ' href="', $attach['file']['href'], ';image">', $attach['file']['image']['thumb'], '</a>
			</div>';
			} else {
				echo '
			<div class="item">
				<a href="', $attach['file']['href'], '">
					<img class="centericon" src="', $this->settings['images_url'], '/icons/clip.png" alt="', $attach['file']['filename'], '"> ', $attach['file']['filename'], '
				</a> (', $attach['file']['filesize'], ')
			</div>';
			}
		}

		echo '
		</div>';
	}
}
