@php use LightPortal\Enums\{FrontPageMode, Tab}; @endphp
@php use LightPortal\UI\Partials\SelectFactory; @endphp

<dl style="margin-top: -1.4em">
	<dt data-show="$frontpage_mode === '{{ FrontPageMode::CHOSEN_PAGE->value }}'">
		<label for="lp_frontpage_chosen_page">{{ $txt['lp_frontpage_chosen_page'] }}</label>
	</dt>
	<dd data-show="$frontpage_mode === '{{ FrontPageMode::CHOSEN_PAGE->value }}'">
		{!! SelectFactory::pageSlug() !!}
	</dd>

	<dt data-show="$frontpage_mode === '{{ FrontPageMode::ALL_PAGES->value }}'">
		<label for="lp_frontpage_categories">{{ $txt['lp_frontpage_categories'] }}</label>
	</dt>
	<dd data-show="$frontpage_mode === '{{ FrontPageMode::ALL_PAGES->value }}'">
		{!! SelectFactory::category() !!}
	</dd>

	<dt data-show="$frontpage_mode === '{{ FrontPageMode::CHOSEN_PAGES->value }}'">
		<label for="lp_frontpage_pages">{{ $txt['lp_frontpage_pages'] }}</label>
	</dt>
	<dd data-show="$frontpage_mode === '{{ FrontPageMode::CHOSEN_PAGES->value }}'">
		{!! SelectFactory::page() !!}
	</dd>

	<dt data-show="$frontpage_mode === '{{ FrontPageMode::CHOSEN_TOPICS->value }}'">
		<label for="lp_frontpage_topics">{{ $txt['lp_frontpage_topics'] }}</label>
	</dt>
	<dd data-show="$frontpage_mode === '{{ FrontPageMode::CHOSEN_TOPICS->value }}'">
		{!! SelectFactory::topic() !!}
	</dd>

	<dt data-show="['{{ FrontPageMode::ALL_TOPICS->value }}', '{{ FrontPageMode::CHOSEN_BOARDS->value }}'].includes($frontpage_mode)">
		<label for="lp_frontpage_boards">{{ $txt['lp_frontpage_boards'] }}</label>
	</dt>
	<dd data-show="['{{ FrontPageMode::ALL_TOPICS->value }}', '{{ FrontPageMode::CHOSEN_BOARDS->value }}'].includes($frontpage_mode)">
		{!! SelectFactory::board() !!}
	</dd>
</dl>
