@yield('preview')

@postErrors()

<form
	id="lp_post"
	action="{{ $context['form_action'] }}"
	method="post"
	accept-charset="{{ $context['character_set'] }}"
	onsubmit="submitonce(this);"
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
