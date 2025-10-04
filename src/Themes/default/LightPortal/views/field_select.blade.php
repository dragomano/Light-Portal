@include('partials.div')

<script>
	VirtualSelect.init(Object.assign({!! $initJs !!}, {
		labelRenderer: function (data) {
			return `<div>${data.label}</div>`;
		}
	}));
</script>
