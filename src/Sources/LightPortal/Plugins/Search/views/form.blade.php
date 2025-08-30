<form
	class="search_addon centertext"
	action="{{ $baseUrl }};sa=search"
	method="post"
	accept-charset="{{ $context['character_set'] }}"
>
	<input type="search" name="search" placeholder="{{ $plugin->txt['title'] }}">
</form>

<script>
	new autoComplete({
		selector: ".search_addon input",
		@if (! empty($plugin->context['min_chars']))
		minChars: {{ $plugin->context['min_chars'] }},
		@endif
		source: async function(term, response) {
			const results = await fetch("{{ $baseUrl }};sa=qsearch", {
				method: "POST",
				headers: {
					"Content-Type": "application/json; charset=utf-8"
				},
				body: JSON.stringify({
					phrase: term
				})
			});

			if (results.ok) {
				const data = await results.json();
				response(data);
			}
		},
		renderItem: function (item, search) {
			search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, "\\$&");
			let re = new RegExp("(" + search.split(" ").join("|") + ")", "gi");

			return `
			<div
				class="autocomplete-suggestion"
				data-val="${item.title}"
				data-link="${item.link}"
				style="cursor: pointer"
			>${item.title.replace(re, "<b>$1</b>")}</div>`;
		},
		onSelect: function(e, term, item) {
			window.location = item.dataset.link;
		}
	});
</script>
