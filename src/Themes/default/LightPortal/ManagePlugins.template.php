<?php

function template_manage_plugins()
{
	global $context, $db_show_debug, $settings;

	if (! empty($context['lp_addon_chart'])) {
		echo '
	<canvas id="addon_chart"></canvas>';
	}

	echo '
	<div id="vue_plugins"></div>
	<script>
		const vueGlobals = {
			plugins: ', $context['lp_json']['plugins'], ',
			context: ', $context['lp_json']['context'], ',
			icons: ', $context['lp_json']['icons'], ',
			txt: ', $context['lp_json']['txt'], ',
		}
	</script>';

	if ($db_show_debug) {
		echo '
	<script src="https://cdn.jsdelivr.net/combine/npm/vue@3/dist/vue.global', ($db_show_debug ? '' : '.prod'), '.min.js,npm/vue3-sfc-loader@0.8.4,npm/vue-demi@0.14.6,npm/pinia@2,npm/vue-i18n@9/dist/vue-i18n.global.prod.min.js,npm/@vueform/multiselect@2,npm/@vueform/toggle@2/dist/toggle.global.min.js,npm/@vueuse/shared@10,npm/@vueuse/core@10"></script>
	<script src="', $settings['default_theme_url'], '/scripts/light_portal/dev/helpers.js"></script>
	<script src="', $settings['default_theme_url'], '/scripts/light_portal/dev/vue_plugins.js"></script>';
	} else {
		echo '
	<script type="module" src="', $settings['default_theme_url'], '/scripts/light_portal/bundle_plugins.js"></script>';
	}
}
