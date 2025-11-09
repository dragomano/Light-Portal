@include('partials.div')

<script>
	VirtualSelect.init(Object.assign({!! $initJs !!}, {
		onServerSearch: async function (search, virtualSelect) {
			fetch("{{ $context['form_action'] }};topic_by_subject", {
				method: "POST",
				headers: {
					"Content-Type": "application/json; charset=utf-8"
				},
				body: JSON.stringify({
					search
				})
			})
				.then(response => response.json())
				.then(function (json) {
					let data = [];
					for (let i = 0; i < json.length; i++) {
						data.push({ label: json[i].subject, value: json[i].id })
					}

					virtualSelect.setServerOptions(data)
				})
				.catch(function () {
					virtualSelect.setServerOptions(false)
				})
		}
	}));
</script>
