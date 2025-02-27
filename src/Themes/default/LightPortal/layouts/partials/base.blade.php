@empty ($context['lp_active_blocks'])
	<div class="col-xs">
@endempty

@yield('content')

@empty ($context['lp_active_blocks'])
	</div>
@endempty
