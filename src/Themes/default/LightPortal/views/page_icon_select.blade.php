@extends('partials.base_icon')

@section('extra_html')
	<input
		type="checkbox"
		name="show_in_menu"
		id="show_in_menu"
		{{ $context['lp_page']['options']['show_in_menu'] ? 'checked=""' : '' }}
		class="checkbox"
	>
	<label class="label" for="show_in_menu" style="margin-left: 1em"></label>
@endsection

@section('extra_js')
	// <script>
	document.querySelector("#show_in_menu").addEventListener("change", function (e) {
		if (e.target.checked) {
			document.querySelector("#{{ $id }}").enable();
		} else {
			document.querySelector("#{{ $id }}").disable();
		}
	});
@endsection
