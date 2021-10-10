<?php

/**
 * ExtendedMetaTags
 *
 * @package Light Portal
 * @link https://github.com/dragomano/Light-Portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9.3
 */

namespace Bugo\LightPortal\Addons\ExtendedMetaTags;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

/**
 * Generated by PluginMaker
 */
class ExtendedMetaTags extends Plugin
{
	/** @var string */
	public $type = 'page_options';

	/**
	 * @var array
	 */
	private $meta_robots = ['', 'index, follow', 'index, nofollow', 'noindex, follow', 'noindex, nofollow'];

	/**
	 * @var array
	 */
	private $meta_rating = ['', '14 years', 'adult', 'general', 'mature', 'restricted', 'save for kids'];

	/**
	 * @return void
	 */
	public function init()
	{
		add_integration_function('integrate_theme_context', __CLASS__ . '::themeContext#', false, __FILE__);
	}

	/**
	 * @return void
	 */
	public function themeContext()
	{
		global $context;

		if (Helpers::request()->has('page') === false || empty($context['lp_page']['options']))
			return;

		if (!empty($context['lp_page']['options']['meta_robots'])) {
			$context['meta_tags'][] = array('name' => 'robots', 'content' => $context['lp_page']['options']['meta_robots']);
		}

		if (!empty($context['lp_page']['options']['meta_rating'])) {
			$context['meta_tags'][] = array('name' => 'rating', 'content' => $context['lp_page']['options']['meta_rating']);
		}
	}

	/**
	 * @param array $options
	 * @return void
	 */
	public function pageOptions(&$options)
	{
		$options['meta_robots'] = '';
		$options['meta_rating'] = '';
	}

	/**
	 * @param array $parameters
	 * @return void
	 */
	public function validatePageData(&$parameters)
	{
		$parameters += array(
			'meta_robots' => FILTER_SANITIZE_STRING,
			'meta_rating' => FILTER_SANITIZE_STRING,
		);
	}

	/**
	 * @return void
	 */
	public function preparePageFields()
	{
		global $context, $txt;

		// Meta robots
		$context['posting_fields']['meta_robots']['label']['text'] = $txt['lp_extended_meta_tags']['meta_robots'];
		$context['posting_fields']['meta_robots']['input'] = array(
			'type' => 'select',
			'tab'  => 'seo'
		);

		$robots_variants = array_combine($this->meta_robots, $txt['lp_extended_meta_tags']['meta_robots_set']);

		foreach ($robots_variants as $value => $title) {
			$context['posting_fields']['meta_robots']['input']['options'][$title] = array(
				'value'    => $value,
				'selected' => $value == $context['lp_page']['options']['meta_robots']
			);
		}

		addInlineJavaScript('
		new SlimSelect({
			select: "#meta_robots",
			showSearch: false,
			hideSelectedOption: true,
			closeOnSelect: true,
			showContent: "down"
		});', true);

		// Meta rating
		$context['posting_fields']['meta_rating']['label']['text'] = $txt['lp_extended_meta_tags']['meta_rating'];
		$context['posting_fields']['meta_rating']['input'] = array(
			'type' => 'select',
			'tab'  => 'seo'
		);

		$rating_variants = array_combine($this->meta_rating, $txt['lp_extended_meta_tags']['meta_rating_set']);

		foreach ($rating_variants as $value => $title) {
			$context['posting_fields']['meta_rating']['input']['options'][$title] = array(
				'value'    => $value,
				'selected' => $value == $context['lp_page']['options']['meta_rating']
			);
		}

		addInlineJavaScript('
		new SlimSelect({
			select: "#meta_rating",
			showSearch: false,
			hideSelectedOption: true,
			closeOnSelect: true,
			showContent: "down"
		});', true);
	}
}