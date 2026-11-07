@yield('preview')

<div id="lp_post_errors">@postErrors()</div>

<form
	id="lp_post"
	action="{{ $context['form_action'] }}"
	method="post"
	accept-charset="{{ $context['character_set'] }}"
	onsubmit="submitonce(this);"
	data-on:submit="{{ $context['lp_form_submit_action'] }}"
	@yield('form_data')
>
	<div class="roundframe{{ isset($context['preview_content']) ? '' : ' noup' }}">
		@yield('tabs')
		<br class="clear">
		<div class="centertext">
			@stack('inputs')
			@stack('buttons')
		</div>
	</div>
</form>

<script>
	@stack('script')

	const tabs = new PortalTabs();
</script>
