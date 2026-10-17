@include('partials.div')

@yield('extra_html')

<script>
	window['VirtualSelect'].init(Object.assign({!! $initJs !!}, {
		hideClearButton: false,
		labelRenderer: function (data) {
			return `<i class="${data.value}"></i> ${data.value}`;
		},
		onServerSearch: async function (search, virtualSelect) {
			await fetch("{{ $context['form_action'] }};icons", {
				method: 'POST',
				headers: { 'Content-Type': 'application/json; charset=utf-8' },
				body: JSON.stringify({
					search,
					@yield('extra_params')
				})
			})
				.then(response => response.json())
				.then(data => {
					const icons = [];
					for (let i = 0; i < data.length; i++) {
						icons.push({ label: data[i].innerHTML, value: data[i].value })
					}

					virtualSelect.setServerOptions(icons)
				})
				.catch(function () {
					virtualSelect.setServerOptions(false)
				})
		}
	}));

	@yield('extra_js')
</script>
