<?php

use Bugo\Compat\{Theme, Utils};

function template_manage_plugins(): void
{
	if (! empty(Utils::$context['lp_addon_chart'])) {
		echo '
	<canvas id="addon_chart"></canvas>';
	}

	echo /** @lang text */ '
	<div id="vue_plugins"></div>
	<script>
		let vueGlobals = "";

		fetch("', Utils::$context['lp_plugins_api_endpoint'], '")
			.then(response => {
				return response.json();
			})
			.then(data => {
				vueGlobals = data;

				const devMode = "', app('config')['debug'], '" === "1";

				const scripts = [
					"https://cdn.jsdelivr.net/combine/npm/vue@3/dist/vue.global.min.js,npm/vue3-sfc-loader@0,npm/vue-demi@0,npm/pinia@2,npm/vue-i18n@10/dist/vue-i18n.global.prod.min.js,npm/@vueform/multiselect@2,npm/@vueform/toggle@2/dist/toggle.global.min.js,npm/@vueuse/shared@10,npm/@vueuse/core@10",
					smf_default_theme_url + "/scripts/light_portal/dev/helpers.js",
					smf_default_theme_url + "/scripts/light_portal/dev/vue_plugins.js"
				];

				const loadScripts = async () => {
					if (devMode) {
						for (const script of scripts) {
							await loadExternalScript(script);
						}
					} else {
						await loadExternalScript(smf_default_theme_url + "/scripts/light_portal/bundle_plugins.js", true);
					}
				};

				return loadScripts();
			});
	</script>';
}
