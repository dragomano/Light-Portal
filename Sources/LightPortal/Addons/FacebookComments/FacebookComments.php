<?php

/**
 * FacebookComments.php
 *
 * @package FacebookComments (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 16.04.22
 */

namespace Bugo\LightPortal\Addons\FacebookComments;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class FacebookComments extends Plugin
{
	public string $type = 'comment';

	private array $sort_order = ['reverse-time', 'time'];

	public function init()
	{
		$this->txt['lp_show_comment_block_set']['facebook'] = 'Facebook';
	}

	public function addSettings(array &$config_vars)
	{
		$this->addDefaultValues([
			'comments_per_page' => 10,
			'comment_order_by'  => 'reverse-time',
		]);

		$config_vars['facebook_comments'][] = ['int', 'comments_per_page'];
		$config_vars['facebook_comments'][] = ['select', 'comment_order_by', array_combine($this->sort_order, $this->txt['lp_facebook_comments']['comment_order_by_set'])];
		$config_vars['facebook_comments'][] = ['multicheck', 'dark_themes', $this->getForumThemes()];
	}

	public function comments()
	{
		if (! empty($this->modSettings['lp_show_comment_block']) && $this->modSettings['lp_show_comment_block'] === 'facebook') {
			$dark_themes = empty($this->context['lp_facebook_comments_plugin']['dark_themes']) ? [] : smf_json_decode($this->context['lp_facebook_comments_plugin']['dark_themes'], true);

			$this->context['lp_facebook_comment_block'] = '
				<div id="fb-root"></div>
				<script async defer crossorigin="anonymous" src="https://connect.facebook.net/' . $this->txt['lang_locale'] . '/sdk.js#xfbml=1"></script>
				<div class="fb-comments" data-href="' . $this->context['canonical_url'] . '" data-numposts="' . ($this->context['lp_facebook_comments_plugin']['comments_per_page'] ?? 10) . '" data-width="100%" data-colorscheme="' . ($dark_themes && ! empty($dark_themes[$this->settings['theme_id']]) ? 'dark' : 'light') . '"' . (empty($this->context['lp_facebook_comments_plugin']['comment_order_by']) ? '' : (' data-order-by="' . $this->context['lp_facebook_comments_plugin']['comment_order_by'] . '"')) . ' data-lazy="true"></div>';
		}
	}

	public function frontAssets()
	{
		if (empty($this->context['lp_frontpage_articles']) || empty($this->modSettings['lp_show_comment_block']) || $this->modSettings['lp_show_comment_block'] !== 'facebook')
			return;

		foreach ($this->context['lp_frontpage_articles'] as $id => $page) {
			$this->context['lp_frontpage_articles'][$id]['replies']['after'] .= ' <i class="fas fa-comment"></i> <span class="fb-comments-count" data-href="' . $page['link'] . '"></span>';
		}
	}
}
