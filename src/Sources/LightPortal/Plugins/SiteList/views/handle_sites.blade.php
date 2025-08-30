<script>
	function handleSites() {
		return {
			sites: [
				@foreach ($urls as $url => $data)
				{
					url: "{{ $url }}",
					image: "{{ $data[0] }}",
					title: "{{ $data[1] }}",
					desc: "{{ $data[2] }}"
				},
				@endforeach
			],
			add() {
				this.sites.push({
					url: "",
					image: "",
					title: "",
					desc: ""
				});
			},
			remove(index) {
				this.sites.splice(index, 1);
			}
		}
	}
</script>
