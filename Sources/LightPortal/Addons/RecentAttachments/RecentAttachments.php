<?php

/**
 * RecentAttachments.php
 *
 * @package RecentAttachments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 04.01.22
 */

namespace Bugo\LightPortal\Addons\RecentAttachments;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class RecentAttachments extends Plugin
{
	public string $icon = 'fas fa-paperclip';

	public function blockOptions(array &$options)
	{
		$options['recent_attachments']['parameters'] = [
			'num_attachments' => 5,
			'extensions'      => '',
			'direction'       => 'horizontal',
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'recent_attachments')
			return;

		$parameters['num_attachments'] = FILTER_VALIDATE_INT;
		$parameters['extensions']      = FILTER_SANITIZE_STRING;
		$parameters['direction']       = FILTER_SANITIZE_STRING;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'recent_attachments')
			return;

		$this->context['posting_fields']['num_attachments']['label']['text'] = $this->txt['lp_recent_attachments']['num_attachments'];
		$this->context['posting_fields']['num_attachments']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id' => 'num_attachments',
				'min' => 1,
				'value' => $this->context['lp_block']['options']['parameters']['num_attachments']
			]
		];

		$this->context['posting_fields']['extensions']['label']['text']  = $this->txt['lp_recent_attachments']['extensions'];
		$this->context['posting_fields']['extensions']['input'] = [
			'type' => 'text',
			'after' => $this->txt['lp_recent_attachments']['extensions_subtext'],
			'attributes' => [
				'id'        => 'extensions',
				'maxlength' => 30,
				'value'     => $this->context['lp_block']['options']['parameters']['extensions'],
				'style'     => 'width: 100%'
			]
		];

		$this->context['posting_fields']['direction']['label']['text'] = $this->txt['lp_recent_attachments']['direction'];
		$this->context['posting_fields']['direction']['input'] = [
			'type' => 'radio_select',
			'attributes' => [
				'id' => 'direction'
			],
			'options' => []
		];

		$directions = array_combine(['vertical', 'horizontal'], $this->txt['lp_panel_direction_set']);

		foreach ($directions as $direction => $title) {
			$this->context['posting_fields']['direction']['input']['options'][$title] = [
				'value'    => $direction,
				'selected' => $direction == $this->context['lp_block']['options']['parameters']['direction']
			];
		}
	}

	public function getData(array $parameters): array
	{
		$extensions = empty($parameters['extensions']) ? [] : explode(',', $parameters['extensions']);

		return $this->getFromSsi('recentAttachments', $parameters['num_attachments'], $extensions, 'array');
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'recent_attachments')
			return;

		$attachment_list = $this->cache('recent_attachments_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData', $parameters);

		if (empty($attachment_list))
			return;

		$fancybox = class_exists('FancyBox');

		echo '
		<div class="recent_attachments' . ($parameters['direction'] == 'vertical' ? ' column_direction' : '') . '">';

		foreach ($attachment_list as $attach) {
			if ($attach['file']['image']) {
				echo '
			<div class="item">
				<a', ($fancybox ? ' class="fancybox" data-fancybox="recent_attachments_' . $block_id . '"' : ''), ' href="', $attach['file']['href'], ';image">', $attach['file']['image']['thumb'], '</a>
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
