<?php

use Bugo\Compat\{Config, Lang, Utils};

return [
	'basic_settings' => '
		{
			element: document.getElementById("admin_content"),
			intro: "' . Lang::$txt['lp_hello_portal']['basic_settings_tour'][0] . '",
			position: "right"
		},
		{
			element: document.getElementById("lp_frontpage_mode"),
			intro: "' . Lang::$txt['lp_hello_portal']['basic_settings_tour'][1] . '"
		},' . (! empty(Config::$modSettings['lp_frontpage_mode']) && Config::$modSettings['lp_frontpage_mode'] !== 'chosen_page' ? ('
		{
			element: document.getElementById("lp_frontpage_order_by_replies"),
			intro: "' . Lang::$txt['lp_hello_portal']['basic_settings_tour'][2] . '"
		},') : '') . '
		{
			element: document.getElementById("setting_lp_standalone_mode").parentNode.parentNode.parentNode,
			intro: "' . Lang::$txt['lp_hello_portal']['basic_settings_tour'][3] . '"
		},
		{
			element: document.getElementById("setting_light_portal_view").parentNode.parentNode,
			intro: "' . Lang::$txt['lp_hello_portal']['basic_settings_tour'][4] . '"
		},
		{
			element: document.querySelector(".information"),
			intro: "' . Lang::$txt['lp_hello_portal']['basic_settings_tour'][5] . '"
		}',
	'extra_settings' => '
		{
			element: document.getElementById("setting_lp_show_tags_on_page").parentNode.parentNode,
			intro: "' . Lang::$txt['lp_hello_portal']['extra_settings_tour'][0] . '",
			position: "right"
		},' . (Utils::$context['lp_show_default_comments'] ? ('
		{
			element: document.getElementById("setting_lp_show_comment_block").parentNode.parentNode.parentNode,
			intro: "' . Lang::$txt['lp_hello_portal']['extra_settings_tour'][1] . '"
		},') : '') . '
		{
			element: document.getElementById("setting_lp_fa_source").parentNode.parentNode,
			intro: "' . Lang::$txt['lp_hello_portal']['extra_settings_tour'][2] . '"
		},',
	'categories' => '
		{
			element: document.querySelector(".lp_categories dd"),
			intro: "' . Lang::$txt['lp_hello_portal']['categories_tour'][0] . '"
		},
		{
			element: document.querySelector(".lp_categories dt"),
			intro: "' . Lang::$txt['lp_hello_portal']['categories_tour'][1] . '"
		},',
	'panels' => '
		{
			element: document.querySelector(".generic_list_wrapper"),
			intro: "' . Lang::$txt['lp_hello_portal']['panels_tour'][0] . '"
		},
		{
			element: document.getElementById("lp_left_panel_width[xl]").parentNode.parentNode.parentNode,
			intro: "' . Lang::$txt['lp_hello_portal']['panels_tour'][1] . '"
		},
		{
			element: document.getElementById("setting_lp_swap_header_footer").parentNode.parentNode,
			intro: "' . Lang::$txt['lp_hello_portal']['panels_tour'][2] . '"
		},
		{
			element: document.querySelector("label[for=lp_panel_direction_header]").parentNode.parentNode.parentNode.parentNode,
			intro: "' . Lang::$txt['lp_hello_portal']['panels_tour'][3] . '"
		},',
	'misc' => '
		{
			element: document.getElementById("setting_lp_show_debug_info_help").parentNode.parentNode,
			intro: "' . Lang::$txt['lp_hello_portal']['misc_tour'][0] . '"
		},
		{
			element: document.getElementById("setting_lp_portal_action").parentNode.parentNode,
			intro: "' . Lang::$txt['lp_hello_portal']['misc_tour'][1] . '"
		},
		{
			element: document.getElementById("lp_weekly_cleaning").parentNode.parentNode,
			intro: "' . Lang::$txt['lp_hello_portal']['misc_tour'][2] . '"
		}',
	'blocks' => '
		{
			element: document.getElementById("admin_content"),
			intro: "' . Lang::$txt['lp_hello_portal']['blocks_tour'][0] . '",
			position: "right"
		},
		{
			element: document.querySelector(".noticebox + .cat_bar"),
			intro: "' . Lang::$txt['lp_hello_portal']['blocks_tour'][1] . '"
		},
		{
			element: document.querySelector("tbody[data-placement=header]"),
			intro: "' . Lang::$txt['lp_hello_portal']['blocks_tour'][2] . '"
		},
		{
			element: document.querySelector("td[class=status]"),
			intro: "' . Lang::$txt['lp_hello_portal']['blocks_tour'][3] . '"
		},
		{
			element: document.querySelector("td[class=actions]"),
			intro: "' . Lang::$txt['lp_hello_portal']['blocks_tour'][4] . '"
		},
		{
			element: document.querySelector("td[class=priority]"),
			intro: "' . Lang::$txt['lp_hello_portal']['blocks_tour'][5] . '"
		}',
	'pages' => '
		{
			element: document.getElementById("admin_content"),
			intro: "' . Lang::$txt['lp_hello_portal']['pages_tour'][0] . '",
			position: "right"
		},
		{
			element: document.querySelector("tbody tr"),
			intro: "' . Lang::$txt['lp_hello_portal']['pages_tour'][1] . '"
		},
		{
			element: document.querySelector("td.date"),
			intro: "' . Lang::$txt['lp_hello_portal']['pages_tour'][2] . '"
		},
		{
			element: document.querySelector("td.num_views"),
			intro: "' . Lang::$txt['lp_hello_portal']['pages_tour'][3] . '"
		},
		{
			element: document.querySelector("td.alias"),
			intro: "' . sprintf(Lang::$txt['lp_hello_portal']['pages_tour'][4], '<strong>?' . LP_PAGE_PARAM . '=</strong>') . '"
		},
		{
			element: document.querySelector("td.status"),
			intro: "' . Lang::$txt['lp_hello_portal']['pages_tour'][5] . '"
		},
		{
			element: document.querySelector("td.actions"),
			intro: "' . Lang::$txt['lp_hello_portal']['pages_tour'][6] . '"
		},
		{
			element: document.querySelector(".additional_row input[type=search]"),
			intro: "' . Lang::$txt['lp_hello_portal']['pages_tour'][7] . '"
		}',
	'plugins' => '
		{
			element: document.getElementById("admin_content"),
			intro: "' . Lang::$txt['lp_hello_portal']['plugins_tour'][0] . '",
			position: "right"
		},
		{
			element: document.getElementById("filter"),
			intro: "' . Lang::$txt['lp_hello_portal']['plugins_tour'][1] . '"
		},
		{
			element: document.querySelector("#admin_content .windowbg"),
			intro: "' . Lang::$txt['lp_hello_portal']['plugins_tour'][2] . '",
			position: "right"
		},
		{
			element: document.querySelector(".features .fa-gear"),
			intro: "' . Lang::$txt['lp_hello_portal']['plugins_tour'][3] . '"
		},
		{
			element: document.querySelector(".features span[data-toggle]"),
			intro: "' . Lang::$txt['lp_hello_portal']['plugins_tour'][4] . '"
		}',
	'add_plugins' => '
		{
			element: document.getElementById("lp_post"),
			intro: "' . Lang::$txt['lp_hello_portal']['add_plugins_tour'][0] . '",
			position: "right"
		},
		{
			element: document.getElementById("name"),
			intro: "' . Lang::$txt['lp_hello_portal']['add_plugins_tour'][1] . '"
		},
		{
			element: document.querySelector("#type"),
			intro: "' . Lang::$txt['lp_hello_portal']['add_plugins_tour'][2] . '"
		},
		{
			element: document.querySelector("input[name^=description_]"),
			intro: "' . Lang::$txt['lp_hello_portal']['add_plugins_tour'][3] . '"
		},
		{
			element: document.querySelector("[data-tab=copyright]"),
			intro: "' . Lang::$txt['lp_hello_portal']['add_plugins_tour'][4] . '"
		},
		{
			element: document.querySelector("[data-tab=settings]"),
			intro: "' . Lang::$txt['lp_hello_portal']['add_plugins_tour'][5] . '"
		},
		{
			element: document.querySelector("[data-tab=tuning"),
			intro: "' . Lang::$txt['lp_hello_portal']['add_plugins_tour'][6] . '"
		}'
];
