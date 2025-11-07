@php use LightPortal\Enums\FrontPageMode; @endphp
@php use LightPortal\Areas\Configs\BasicConfig; @endphp

@unless (empty($context['saved_successful']))
	<div class="infobox">{{ $txt['settings_saved'] }}</div>
@else
	@unless (empty($context['saved_failed']))
		<div class="errorbox">{{ sprintf($txt['settings_not_saved'], $context['saved_failed']) }}</div>
	@endunless
@endunless

<div class="cat_bar">
	<h3 class="catbg">{{ $txt['mods_cat_features'] }}</h3>
</div>

<form
	action="{{ $context['post_url'] }}"
	method="post"
	accept-charset="{{ $context['character_set'] }}"
	onsubmit="submitonce(this);"
	x-data="{ frontpage_mode: '{{ $modSettings['lp_frontpage_mode'] ?? FrontPageMode::DEFAULT->value }}' }"
	@change-mode.window="frontpage_mode = $event.detail.front"
>
	@php $message = $context['settings_message'] ?? ''; @endphp

	@unless (empty($message))
		@php
			$tag = empty($message['tag']) ? 'span' : $message['tag'];
		@endphp

		<div class="information">
			@if (is_array($message))
				@php
					$tag = $tag ?? 'div';
					$class = $message['class'] ?? null;
					$label = $message['label'] ?? '';
				@endphp

				@switch ($tag)
					@case('span')
						<span @class([$class])>{{ $label }}</span>
					@break

					@case('div')
					@default
						<div @class([$class])>{{ $label }}</div>
				@endswitch
			@else
				{!! $message !!}
			@endif
		</div>
	@endunless

	<div class="roundframe{{ empty($message) ? ' noup' : '' }}">

		@include('admin.partials._tabs', [
			'tabs' => [
				[
					'id'    => BasicConfig::TAB_BASE,
					'icon'  => 'cog_spin',
					'title' => $txt['lp_base'],
					'type'  => BasicConfig::TAB_BASE,
				],
				[
					'id'    => BasicConfig::TAB_CARDS,
					'icon'  => 'design',
					'title' => $txt['lp_article_cards'],
					'type'  => BasicConfig::TAB_CARDS,
				],
				[
					'id'    => BasicConfig::TAB_STANDALONE,
					'icon'  => 'meteor',
					'title' => $txt['lp_standalone_mode_title'],
					'type'  => BasicConfig::TAB_STANDALONE,
					'show'  => "! ['" . FrontPageMode::DEFAULT->value . "', '" . FrontPageMode::CHOSEN_PAGE->value . "'].includes(frontpage_mode)",
				],
				[
					'id'    => BasicConfig::TAB_PERMISSIONS,
					'icon'  => 'access',
					'title' => $txt['edit_permissions'],
					'type'  => BasicConfig::TAB_PERMISSIONS,
				],
			]
		])

		<br class="clear">

		@empty ($context['settings_save_dont_show'])
			<input
				type="submit"
				value="{{ $txt['save'] }}"
				{{ ! empty($context['save_disabled']) ? 'disabled' : '' }}
				{{ ! empty($context['settings_save_onclick']) ? 'onclick="' . $context['settings_save_onclick'] . '"' : '' }}
				class="button"
			>
		@endempty

		@isset ($context['admin-ssc_token'])
			<input type="hidden" name="{{ $context['admin-ssc_token_var'] }}" value="{{ $context['admin-ssc_token'] }}">
		@endisset

		@isset ($context['admin-dbsc_token'])
			<input type="hidden" name="{{ $context['admin-dbsc_token_var'] }}" value="{{ $context['admin-dbsc_token'] }}">
		@endisset

		@isset ($context['admin-mp_token'])
			<input type="hidden" name="{{ $context['admin-mp_token_var'] }}" value="{{ $context['admin-mp_token'] }}">
		@endisset

		<input type="hidden" name="{{ $context['session_var'] }}" value="{{ $context['session_id'] }}">
	</div>

	<script>
		const tabs = new PortalTabs();
	</script>
</form>
