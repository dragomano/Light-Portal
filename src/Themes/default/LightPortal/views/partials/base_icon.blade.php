@include('partials.div')

@yield('extra_html')

<script>
	VirtualSelect.init(Object.assign({!! $initJs !!}, {
		labelRenderer: function (data) {
			return `<i class="${data.value}"></i> ${data.value}`;
		},
		onServerSearch: async function (search, virtualSelect) {
			await axios.post("{{ $context['form_action'] }};icons", {
				search,
				@yield('extra_params')
			})
				.then(({ data }) => {
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
