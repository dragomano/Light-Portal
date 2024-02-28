<?php

use Bugo\Compat\{Config, Theme, Utils};

function template_manage_plugins(): void
{
	if (! empty(Utils::$context['lp_addon_chart'])) {
		echo '
	<canvas id="addon_chart"></canvas>';
	}

	echo '
	<div id="vue_plugins"></div>
	<script>
		const vueGlobals = {
			plugins: ', Utils::$context['lp_json']['plugins'], ',
			context: ', Utils::$context['lp_json']['context'], ',
			icons: ', Utils::$context['lp_json']['icons'], ',
			txt: ', Utils::$context['lp_json']['txt'], ',
		}
	</script>';

	if (is_file(Theme::$current->settings['default_theme_dir'] . '/scripts/light_portal/dev/helpers.js')) {
		echo '
	<script src="https://cdn.jsdelivr.net/combine/npm/vue@3/dist/vue.global.min.js,npm/vue3-sfc-loader@0,npm/vue-demi@0,npm/pinia@2,npm/vue-i18n@9/dist/vue-i18n.global.prod.min.js,npm/@vueform/multiselect@2,npm/@vueform/toggle@2/dist/toggle.global.min.js,npm/@vueuse/shared@10,npm/@vueuse/core@10"></script>
	<script src="', Theme::$current->settings['default_theme_url'], '/scripts/light_portal/dev/helpers.js"></script>
	<script src="', Theme::$current->settings['default_theme_url'], '/scripts/light_portal/dev/vue_plugins.js"></script>';
	} else {
		echo '
	<script type="module" src="', Theme::$current->settings['default_theme_url'], '/scripts/light_portal/bundle_plugins.js"></script>';
	}
}
