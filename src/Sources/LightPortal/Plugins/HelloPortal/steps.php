<?php

use Bugo\LightPortal\Utils\Setting;

function getSteps(array $txt, array $modSettings): array
{
	return [
		'basic_settings' => '
			{
				element: document.getElementById("admin_content"),
				intro: "' . $txt['basic_settings_tour'][0] . '",
				position: "right"
			},
			{
				element: document.getElementById("lp_frontpage_mode"),
				intro: "' . $txt['basic_settings_tour'][1] . '"
			},' . (! empty($modSettings['lp_frontpage_mode']) && $modSettings['lp_frontpage_mode'] !== 'chosen_page' ? ('
			{
				element: document.getElementById("caption_lp_frontpage_order_by_replies"),
				intro: "' . $txt['basic_settings_tour'][2] . '"
			},') : '') . '
			{
				element: document.querySelector("[data-tab=standalone]"),
				intro: "' . $txt['basic_settings_tour'][3] . '"
			},
			{
				element: document.querySelector("[data-tab=permissions]"),
				intro: "' . $txt['basic_settings_tour'][4] . '"
			}',
		'extra_settings' => '
			{
				element: document.getElementById("setting_lp_show_tags_on_page").parentNode.parentNode,
				intro: "' . $txt['extra_settings_tour'][0] . '",
				position: "right"
			},' . (Setting::getCommentBlock() === 'default' ? ('
			{
				element: document.getElementById("setting_lp_comment_block").parentNode.parentNode.parentNode,
				intro: "' . $txt['extra_settings_tour'][1] . '"
			},') : '') . '
			{
				element: document.getElementById("setting_lp_fa_source").parentNode.parentNode,
				intro: "' . $txt['extra_settings_tour'][2] . '"
			},',
		'panels' => '
			{
				element: document.querySelector(".generic_list_wrapper"),
				intro: "' . $txt['panels_tour'][0] . '"
			},
			{
				element: document.getElementById("lp_left_panel_width[xl]").parentNode.parentNode.parentNode,
				intro: "' . $txt['panels_tour'][1] . '"
			},
			{
				element: document.getElementById("setting_lp_swap_header_footer").parentNode.parentNode,
				intro: "' . $txt['panels_tour'][2] . '"
			},
			{
				element: document.querySelector("label[for=lp_panel_direction_header]").parentNode.parentNode.parentNode.parentNode,
				intro: "' . $txt['panels_tour'][3] . '"
			},',
		'misc' => '
			{
				element: document.getElementById("setting_lp_show_debug_info_help").parentNode.parentNode,
				intro: "' . $txt['misc_tour'][0] . '"
			},
			{
				element: document.getElementById("setting_lp_portal_action").parentNode.parentNode,
				intro: "' . $txt['misc_tour'][1] . '"
			},
			{
				element: document.getElementById("lp_weekly_cleaning").parentNode.parentNode,
				intro: "' . $txt['misc_tour'][2] . '"
			}',
		'blocks' => '
			{
				element: document.getElementById("admin_content"),
				intro: "' . $txt['blocks_tour'][0] . '",
				position: "right"
			},
			{
				element: document.querySelector(".infobox + .cat_bar"),
				intro: "' . $txt['blocks_tour'][1] . '"
			},
			{
				element: document.querySelector("tbody[data-placement=header]"),
				intro: "' . $txt['blocks_tour'][2] . '"
			},
			{
				element: document.querySelector("td[class=status]"),
				intro: "' . $txt['blocks_tour'][3] . '"
			},
			{
				element: document.querySelector("td[class=actions]"),
				intro: "' . $txt['blocks_tour'][4] . '"
			},
			{
				element: document.querySelector("td[class^=priority]"),
				intro: "' . $txt['blocks_tour'][5] . '"
			}',
		'pages' => '
			{
				element: document.getElementById("admin_content"),
				intro: "' . $txt['pages_tour'][0] . '",
				position: "right"
			},
			{
				element: document.querySelector("tbody tr"),
				intro: "' . $txt['pages_tour'][1] . '"
			},
			{
				element: document.querySelector("td.date"),
				intro: "' . $txt['pages_tour'][2] . '"
			},
			{
				element: document.querySelector("td.num_views"),
				intro: "' . $txt['pages_tour'][3] . '"
			},
			{
				element: document.querySelector("td.slug"),
				intro: "' . sprintf($txt['pages_tour'][4], '<strong>?' . LP_PAGE_PARAM . '=</strong>') . '"
			},
			{
				element: document.querySelector("td.status"),
				intro: "' . $txt['pages_tour'][5] . '"
			},
			{
				element: document.querySelector("td.actions"),
				intro: "' . $txt['pages_tour'][6] . '"
			},
			{
				element: document.querySelector(".additional_row input[type=search]"),
				intro: "' . $txt['pages_tour'][7] . '"
			}',
		'categories' => '
			{
				element: document.getElementById("admin_content"),
				intro: "' . $txt['categories_tour'][0] . '"
			},
			{
				element: document.querySelector("tbody tr"),
				intro: "' . $txt['categories_tour'][1] . '"
			},
			{
				element: document.querySelector("td.priority"),
				intro: "' . $txt['categories_tour'][2] . '"
			},
			{
				element: document.querySelector("td.status"),
				intro: "' . $txt['categories_tour'][3] . '"
			},
			{
				element: document.querySelector("td.actions"),
				intro: "' . $txt['categories_tour'][4] . '"
			},',
		'plugins' => '
			{
				element: document.getElementById("admin_content"),
				intro: "' . $txt['plugins_tour'][0] . '",
				position: "right"
			},
			{
				element: document.getElementById("filter"),
				intro: "' . $txt['plugins_tour'][1] . '"
			},
			{
				element: document.querySelector("#admin_content .windowbg"),
				intro: "' . $txt['plugins_tour'][2] . '",
				position: "right"
			},
			{
				element: document.querySelector(".features .fa-gear"),
				intro: "' . $txt['plugins_tour'][3] . '"
			},
			{
				element: document.querySelector(".features button[role=switch]"),
				intro: "' . $txt['plugins_tour'][4] . '"
			}',
		'add_plugins' => '
			{
				element: document.getElementById("lp_post"),
				intro: "' . $txt['add_plugins_tour'][0] . '",
				position: "right"
			},
			{
				element: document.getElementById("name"),
				intro: "' . $txt['add_plugins_tour'][1] . '"
			},
			{
				element: document.querySelector("#type"),
				intro: "' . $txt['add_plugins_tour'][2] . '"
			},
			{
				element: document.querySelector("input[name^=description_]"),
				intro: "' . $txt['add_plugins_tour'][3] . '"
			},
			{
				element: document.querySelector("[data-tab=copyright]"),
				intro: "' . $txt['add_plugins_tour'][4] . '"
			},
			{
				element: document.querySelector("[data-tab=settings]"),
				intro: "' . $txt['add_plugins_tour'][5] . '"
			},
			{
				element: document.querySelector("[data-tab=tuning"),
				intro: "' . $txt['add_plugins_tour'][6] . '"
			}'
	];
}
