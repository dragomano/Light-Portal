<?php

namespace Bugo\LightPortal\Addons\InstagramFeed;

use Bugo\LightPortal\Helpers;

/**
 * InstagramFeed
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.6
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class InstagramFeed
{
	/**
	 * @var string
	 */
	public $addon_icon = 'fab fa-instagram';

	/**
	 * @var string
	 */
	private $username = '';

	/**
	 * @var string
	 */
	private $tag = '';

	/**
	 * @var bool
	 */
	private $display_profile = false;

	/**
	 * @var bool
	 */
	private $display_biography = false;

	/**
	 * @var bool
	 */
	private $display_gallery = true;

	/**
	 * @var bool
	 */
	private $display_captions = false;

	/**
	 * @var bool
	 */
	private $display_igtv = false;

	/**
	 * @var int
	 */
	private $num_items = 8;

	/**
	 * @var int
	 */
	private $num_items_per_row = 4;

	/**
	 * @var float
	 */
	private $margin = 0.5;

	/**
	 * @var int
	 */
	private $image_size = 640;

	/**
	 * @var array
	 */
	private $allowed_image_sizes = [150, 240, 320, 480, 640];

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['instagram_feed']['parameters']['username']          = $this->username;
		$options['instagram_feed']['parameters']['tag']               = $this->tag;
		$options['instagram_feed']['parameters']['display_profile']   = $this->display_profile;
		$options['instagram_feed']['parameters']['display_biography'] = $this->display_biography;
		$options['instagram_feed']['parameters']['display_gallery']   = $this->display_gallery;
		$options['instagram_feed']['parameters']['display_captions']  = $this->display_captions;
		$options['instagram_feed']['parameters']['display_igtv']      = $this->display_igtv;
		$options['instagram_feed']['parameters']['items']             = $this->num_items;
		$options['instagram_feed']['parameters']['items_per_row']     = $this->num_items_per_row;
		$options['instagram_feed']['parameters']['margin']            = $this->margin;
		$options['instagram_feed']['parameters']['image_size']        = $this->image_size;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'instagram_feed')
			return;

		$parameters['username']          = FILTER_SANITIZE_STRING;
		$parameters['tag']               = FILTER_SANITIZE_STRING;
		$parameters['display_profile']   = FILTER_VALIDATE_BOOLEAN;
		$parameters['display_biography'] = FILTER_VALIDATE_BOOLEAN;
		$parameters['display_gallery']   = FILTER_VALIDATE_BOOLEAN;
		$parameters['display_captions']  = FILTER_VALIDATE_BOOLEAN;
		$parameters['display_igtv']      = FILTER_VALIDATE_BOOLEAN;
		$parameters['items']             = FILTER_VALIDATE_INT;
		$parameters['items_per_row']     = FILTER_VALIDATE_INT;
		$parameters['margin']            = FILTER_VALIDATE_FLOAT;
		$parameters['image_size']        = FILTER_VALIDATE_INT;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'instagram_feed')
			return;

		$context['posting_fields']['username']['label']['text'] = $txt['lp_instagram_feed_addon_username'];
		$context['posting_fields']['username']['input'] = array(
			'type' => 'text',
			'after' => $txt['lp_instagram_feed_addon_username_subtext'],
			'attributes' => array(
				'id'        => 'username',
				'value'     => $context['lp_block']['options']['parameters']['username'],
				'required'  => empty($context['lp_block']['options']['parameters']['tag']),
				':required' => '!$refs.instagram_tag.value',
				'style'     => 'width: 50%',
				'x-ref'     => 'instagram_username',
				'@change'   => '$refs.instagram_tag.required = !$event.target.value; $refs.instagram_items.max = $event.target.value ? 12 : 72',
				'@keydown'  => '$refs.instagram_tag.required = !$event.target.value'
			),
			'tab' => 'content'
		);

		$context['posting_fields']['tag']['label']['text'] = $txt['lp_instagram_feed_addon_tag'];
		$context['posting_fields']['tag']['input'] = array(
			'type' => 'text',
			'before' => '<i class="fas fa-hashtag"></i>',
			'after' => $txt['lp_instagram_feed_addon_tag_subtext'],
			'attributes' => array(
				'id'        => 'tag',
				'value'     => $context['lp_block']['options']['parameters']['tag'],
				'required'  => empty($context['lp_block']['options']['parameters']['username']),
				':required' => '!$refs.instagram_username.value',
				'style'     => 'width: 48%',
				'x-ref'     => 'instagram_tag',
				'@change'   => '$refs.instagram_username.required = !$event.target.value; $refs.instagram_items.max = $event.target.value ? 72 : 12',
				'@keydown'  => '$refs.instagram_username.required = !$event.target.value'
			),
			'tab' => 'content'
		);

		$context['posting_fields']['display_profile']['label']['text'] = $txt['lp_instagram_feed_addon_display_profile'];
		$context['posting_fields']['display_profile']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'display_profile',
				'checked' => !empty($context['lp_block']['options']['parameters']['display_profile'])
			)
		);

		$context['posting_fields']['display_biography']['label']['text'] = $txt['lp_instagram_feed_addon_display_biography'];
		$context['posting_fields']['display_biography']['input'] = array(
			'type' => 'checkbox',
			'after' => $txt['lp_instagram_feed_addon_display_biography_subtext'],
			'attributes' => array(
				'id'      => 'display_biography',
				'checked' => !empty($context['lp_block']['options']['parameters']['display_biography'])
			)
		);

		$context['posting_fields']['display_gallery']['label']['text'] = $txt['lp_instagram_feed_addon_display_gallery'];
		$context['posting_fields']['display_gallery']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'display_gallery',
				'checked' => !empty($context['lp_block']['options']['parameters']['display_gallery'])
			)
		);

		$context['posting_fields']['display_captions']['label']['text'] = $txt['lp_instagram_feed_addon_display_captions'];
		$context['posting_fields']['display_captions']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'display_captions',
				'checked' => !empty($context['lp_block']['options']['parameters']['display_captions'])
			)
		);

		$context['posting_fields']['display_igtv']['label']['text'] = $txt['lp_instagram_feed_addon_display_igtv'];
		$context['posting_fields']['display_igtv']['input'] = array(
			'type' => 'checkbox',
			'after' => $txt['lp_instagram_feed_addon_display_igtv_subtext'],
			'attributes' => array(
				'id'      => 'display_igtv',
				'checked' => !empty($context['lp_block']['options']['parameters']['display_igtv'])
			)
		);

		$context['posting_fields']['items']['label']['text'] = $txt['lp_instagram_feed_addon_items'];
		$context['posting_fields']['items']['input'] = array(
			'type' => 'number',
			'after' => $txt['lp_instagram_feed_addon_items_subtext'],
			'attributes' => array(
				'id'    => 'items',
				'min'   => 1,
				'max'   => !empty($context['lp_block']['options']['parameters']['username']) ? 12 : 72,
				'value' => $context['lp_block']['options']['parameters']['items'],
				'x-ref' => 'instagram_items'
			)
		);

		$context['posting_fields']['items_per_row']['label']['text'] = $txt['lp_instagram_feed_addon_items_per_row'];
		$context['posting_fields']['items_per_row']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'items_per_row',
				'min'   => 1,
				'value' => $context['lp_block']['options']['parameters']['items_per_row']
			)
		);

		$context['posting_fields']['margin']['label']['text'] = $txt['lp_instagram_feed_addon_margin'];
		$context['posting_fields']['margin']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'margin',
				'min'   => 0,
				'step'  => 0.1,
				'value' => $context['lp_block']['options']['parameters']['margin']
			)
		);

		$context['posting_fields']['image_size']['label']['text'] = $txt['lp_instagram_feed_addon_image_size'];
		$context['posting_fields']['image_size']['input'] = array(
			'type' => 'select',
			'after' => $txt['lp_instagram_feed_addon_image_size_subtext'],
			'attributes' => array(
				'id'    => 'image_size'
			),
			'options' => array()
		);

		foreach ($this->allowed_image_sizes as $key) {
			$context['posting_fields']['image_size']['input']['options'][$key] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['image_size']
			);
		}
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		if ($type !== 'instagram_feed' || (empty($parameters['username']) && empty($parameters['tag'])))
			return;

		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/instafeed@1/dist/InstagramFeed.min.js', array('external' => true));
		addInlineJavaScript('
		(function() {
			new InstagramFeed({' . (!empty($parameters['username']) ? '
				"username": "' . $parameters['username'] . '",' : '') . (!empty($parameters['tag']) ? '
				"tag": "' . str_replace('#', '', $parameters['tag']) . '",' : '') . '
				"container": document.getElementById("instagram_feed' . $block_id . '"),
				"display_profile": ' . (!empty($parameters['display_profile']) ? 'true' : 'false') . ',
				"display_biography": ' . (!empty($parameters['display_biography']) ? 'true' : 'false') . ',
				"display_gallery": ' . (!empty($parameters['display_gallery']) ? 'true' : 'false') . ',
				"display_captions": ' . (!empty($parameters['display_captions']) ? 'true' : 'false') . ',
				"display_igtv": ' . (!empty($parameters['display_igtv']) ? 'true' : 'false') . ',
				"items": ' . (!empty($parameters['items']) ? $parameters['items'] : $this->num_items) . ',
				"items_per_row": ' . (!empty($parameters['items_per_row']) ? $parameters['items_per_row'] : $this->num_items_per_row) . ',
				"lazy_load": true,
				"margin": ' . (!empty($parameters['margin']) ? $parameters['margin'] : $this->margin) . ',
				"image_size": ' . (!empty($parameters['image_size']) ? $parameters['image_size'] : $this->image_size) . '
			});
		})();', true);

		ob_start();

		echo '
			<div id="instagram_feed' . $block_id . '"></div>';

		$content = ob_get_clean();
	}

	/**
	 * @param array $links
	 * @return void
	 */
	public function credits(&$links)
	{
		$links[] = array(
			'title' => 'InstagramFeed',
			'link' => 'https://github.com/jsanahuja/InstagramFeed',
			'author' => 'Javier Sanahuja',
			'license' => array(
				'name' => 'the MIT License',
				'link' => 'https://github.com/jsanahuja/InstagramFeed/blob/master/LICENSE'
			)
		);
	}
}
