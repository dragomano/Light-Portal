@unless (empty($context['lp_addon_chart']))
	<canvas id="addon_chart"></canvas>
@endunless

<div id="svelte_plugins"></div>
<script type="module">
	window['usePortalApi']("{{ $context['lp_plugins_api_endpoint'] }}", "bundle_plugins.js")
</script>
