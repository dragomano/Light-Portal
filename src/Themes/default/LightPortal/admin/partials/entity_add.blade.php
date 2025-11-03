<div class="cat_bar">
	<h3 class="catbg">{{ $title }}</h3>
</div>

<div class="information">
	{!! $description !!}
</div>

<div id="lp_blocks">
	<form
		name="{{ $formName }}"
		action="{{ $context['form_action'] }}"
		method="post"
		accept-charset="{{ $context['character_set'] }}"
	>
		<div class="row">
			@foreach ($items as $item)
				<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3" x-data>
					<div class="item roundframe" data-type="{{ $item['type'] }}" @click="{{ $enityKey }}.add($el)">
						<i class="{{ $item['icon'] }} fa-2x" aria-hidden="true"></i>
						<div>
							<strong>{{ $item['title'] }}</strong>
						</div>
						<hr>
						<p>{!! $item['desc'] !!}</p>
					</div>
				</div>
			@endforeach
		</div>

		<input type="hidden" name="add_{{ $enityKey }}">
		{!! $extraInputs ?? '' !!}
	</form>
</div>

<script>
	{{ $script }}
</script>
