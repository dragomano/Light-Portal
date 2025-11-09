@php use LightPortal\Utils\Setting; @endphp

@unless (empty($blocks[$panel]))
	@switch ($panel)
		@case('header')
		@case('footer')
			@php
				$method = 'get' . ucfirst($panel) . 'PanelWidth';
				$xs = Setting::$method();
			@endphp
			<div class="row between-xs">
				<div class="col-xs-{{ $xs }}" data-panel="{{ $panel }}">
					@include('partials._list', ['panel' => $panel])
				</div>
			</div>
			@break

		@case('left')
		@case('right')
			@php
				$method = 'get' . ucfirst($panel) . 'PanelWidth';
				$width = Setting::$method();
				$class = empty($modSettings['lp_' . $panel . '_panel_sticky']) ? '' : 'sticky_sidebar';
			@endphp
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-{{ $width['lg'] }} col-xl-{{ $width['xl'] }}">
				<div class="{{ $class }}" data-panel="{{ $panel }}">
					@include('partials._list', ['panel' => $panel])
				</div>
			</div>
			@break

		@case('top')
		@case('bottom')
			<div class="row">
				<div class="col-xs-12 col-sm" data-panel="{{ $panel }}">
					@include('partials._list', ['panel' => $panel])
				</div>
			</div>
			@break

		@default
			@include('partials._list', ['panel' => $panel])
	@endswitch
@endunless
