@php use LightPortal\Enums\{FrontPageMode, PortalHook}; @endphp
@php use LightPortal\Events\EventManagerFactory; @endphp
@php use LightPortal\Utils\Setting; @endphp
@php use function LightPortal\app; @endphp

@unless (empty($context['lp_page']['errors']))
	<aside class="errorbox">
		<ul>
			@foreach ($context['lp_page']['errors'] as $error)
				<li><strong>{!! $error !!}</strong></li>
			@endforeach
		</ul>
	</aside>
@endunless

@if ($context['lp_page']['can_edit'])
	<aside class="infobox">
		<div>
			<strong>{{ $txt['edit_permissions'] }}</strong>: {{ $txt['lp_permissions'][$context['lp_page']['permissions']] }}
		</div>
		<div>
			<a class="button floatright" href="{{ $context['lp_page_edit_link'] }}">
				@icon('edit')
				<span class="hidden-xs">{{ $txt['edit'] }}</span>
			</a>

			@if (
				! empty($context['user']['is_admin'])
				&& ! empty($modSettings['lp_frontpage_mode'])
				&& $modSettings['lp_frontpage_mode'] === FrontPageMode::CHOSEN_PAGES->value
			)
				<a class="button floatright" href="{{ $context['canonical_url'] }};promote">
					@icon('home')
					<span class="hidden-xs hidden-sm">
                        {{ $txt['lp_' . (in_array($context['lp_page']['id'], Setting::getFrontpagePages()) ? 'remove_from' : 'promote_to') . '_fp'] }}
                    </span>
				</a>
			@endif
		</div>
	</aside>
@endif

<section itemscope itemtype="https://schema.org/Article">
	<meta
		itemscope
		itemprop="mainEntityOfPage"
		itemType="https://schema.org/WebPage"
		itemid="{{ $context['canonical_url'] }}"
		content="{{ $context['canonical_url'] }}"
	>

	@if (
		! isset($context['lp_page']['options']['show_title'])
		|| ! empty($context['lp_page']['options']['show_title'])
		|| ! empty($context['lp_page']['options']['show_author_and_date'])
	)
		<div id="display_head" class="windowbg">
			@if (! isset($context['lp_page']['options']['show_title']) || ! empty($context['lp_page']['options']['show_title']))
				<h2 class="display_title" itemprop="headline">
					<span id="top_subject">{{ $context['page_title'] }}</span>
				</h2>
			@endif

			@unless (empty($context['lp_page']['options']['show_author_and_date']))
				<p>
                    <span class="floatleft" itemprop="author" itemscope itemtype="https://schema.org/Person">
                        @icon('user')<span itemprop="name">{{ $context['lp_page']['author'] }}</span>
                        <meta itemprop="url" content="{{ $scripturl }}?action=profile;u={{ $context['lp_page']['author_id'] }}">
                    </span>
					{{ $context['lp_page']['post_author'] ?? '' }}
					<time class="floatright" datetime="{{ date('c', $context['lp_page']['created_at']) }}" itemprop="datePublished">
						@icon('date'){!! $context['lp_page']['created'] !!}
						@unless (empty($context['lp_page']['updated_at']))
							/ {!! $context['lp_page']['updated'] !!}
							<meta itemprop="dateModified" content="{{ date('c', $context['lp_page']['updated_at']) }}">
						@endunless
					</time>
				</p>
			@endunless
		</div>
	@endif

	<article class="roundframe" itemprop="articleBody">
		<h3 style="display: none">
			{{ $context['lp_page']['author'] }} - {{ $context['page_title'] }}
		</h3>

		@unless (empty($context['lp_page']['tags']) || empty($modSettings['lp_show_tags_on_page']))
			<div class="smalltext">
				@foreach ($context['lp_page']['tags'] as $tag)
					<a class="button" href="{{ $tag['href'] }}">{!! $tag['icon'] !!}{{ $tag['name'] }}</a>
				@endforeach
			</div>
			<hr>
		@endunless

		@php app(EventManagerFactory::class)()->dispatch(PortalHook::beforePageContent) @endphp

		@unless (empty($settings['og_image']))
			<meta itemprop="image" content="{{ $settings['og_image'] }}">
		@endunless

		<div class="page_{{ $context['lp_page']['type'] }}">
			{!! $context['lp_page']['content'] !!}
		</div>

		@php app(EventManagerFactory::class)()->dispatch(PortalHook::afterPageContent) @endphp
	</article>

	@include('partials._navigation_links')
	@include('partials._related_pages')
	@include('partials._comments')
</section>
