@unless (empty($context['import_successful']))
	<div class="infobox">{{ $context['import_successful'] }}</div>
@endunless

<div class="cat_bar">
	<h3 class="catbg">{{ $context['page_area_title'] }}</h3>
</div>

<div class="information">{{ $context['page_area_info'] }}</div>

<div class="descbox">
	<form action="{{ $context['form_action'] }}" method="post" enctype="multipart/form-data">
		<div class="centertext">
			<input type="hidden" name="MAX_FILE_SIZE" value="{{ $context['max_file_size'] }}">
			<input name="import_file" type="file" accept="{{ $context['lp_file_type'] }}">
			<button class="button floatnone" type="submit">{{ $txt['lp_import_run'] }}</button>
		</div>
	</form>
</div>
