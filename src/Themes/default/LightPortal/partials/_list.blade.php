@php use LightPortal\Enums\{ContentClass, TitleClass}; use LightPortal\Utils\{Icon, Setting}; @endphp

@php
	$panelDirection = Setting::getPanelDirection($panel);
	$panelDirectionClass = $panelDirection ? ' col-xs-12 col-sm' : '';
@endphp

@if ($panelDirection)
	<div class="row">
@endif

@foreach ($blocks[$panel] as $block)
	@php
		// Skip empty content blocks
		if (in_array($block['type'], array_keys($context['lp_content_types'])) && empty($block['content'])) {
			continue;
		}

		$customClass = empty($block['custom_class']) ? '' : (' ' . $block['custom_class']);
		$class = 'block_' . $block['type'] . $panelDirectionClass . $customClass;

		// Prepare edit icon if needed
		if (! empty($block['can_edit']) && ! empty($block['title'])) {
			$editLink = $scripturl . '?action=admin;area=lp_blocks;sa=edit;id=' . $block['id'];
			$editIcon = Icon::get('tools');
			$block['title'] .= '<a class="floatright block_edit" href="' . $editLink . '">' . $editIcon . '</a>';
		}

		// Render block parts
		if (! empty($block['title'])) {
			$titleClass = TitleClass::values()[$block['title_class'] ?? ''] ?? '%s';
			$titleHtml = sprintf($titleClass, $block['title']);
		}

		$contentClass = ContentClass::values()[$block['content_class'] ?? ''] ?? '%s';
		$contentHtml = sprintf($contentClass, $block['content']);
	@endphp

	<aside id="block_{{ $block['id'] }}" class="{{ $class }}">
		{!! $titleHtml ?? '' !!}
		{!! $contentHtml ?? '' !!}
	</aside>
@endforeach

@if ($panelDirection)
	</div>
@endif
