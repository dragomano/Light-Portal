@php use LightPortal\Utils\Setting; @endphp
@php $layout ??= '' @endphp
@php $id ??= 'lp_layout' @endphp

@if ($layout === 'above')
	<div id="{{ $id }}" class="{{ empty($modSettings['lp_swap_header_footer']) ? '' : 'column reverse' }}">
		@include('partials._panel', ['panel' => 'header'])

		<div class="row {{ Setting::isSwapLeftRight() ? 'reverse' : '' }}">
			@include('partials._panel', ['panel' => 'left'])

			@php
				$lg = Setting::getColumnWidth('lg');
				$xl = Setting::getColumnWidth('xl');

				$hasBlocks = ! (empty($blocks['left']) && empty($blocks['right']));
				$classes = $hasBlocks ? '-12 col-sm-12 col-md-12 col-lg-' . $lg . ' col-xl-' . $xl : '';
			@endphp

			<div class="col-xs{{ $classes }}">
				<div class="{{ empty($modSettings['lp_swap_top_bottom']) ? '' : 'column reverse' }}">
					@include('partials._panel', ['panel' => 'top'])

					<div class="row">
						<div class="col-xs noup">
@elseif ($layout === 'below')
						</div>
					</div>

					@include('partials._panel', ['panel' => 'bottom'])
				</div>
			</div>

			@include('partials._panel', ['panel' => 'right'])
		</div>

		@include('partials._panel', ['panel' => 'footer'])
	</div>
@endif
