@unless (empty($modSettings['lp_comment_block']) || empty($context['lp_page']['options']['allow_comments']))
	@unless ($modSettings['lp_comment_block'] === 'none')
		@unless (empty($context['lp_' . $modSettings['lp_comment_block'] . '_comment_block']))
			{!! $context['lp_' . $modSettings['lp_comment_block'] . '_comment_block'] !!}
		@else
			@if ($modSettings['lp_comment_block'] === 'default')
				<div id="svelte_comments"></div>
				<script type="module">
					window['usePortalApi']("{{ $context['lp_comments_api_endpoint'] }}", "bundle_comments.js")
				</script>
			@endif
		@endunless
	@endunless
@endunless
