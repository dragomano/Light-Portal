<script>
	function handleSites() {
		return {
			sites: {!! $sites ?: '[]' !!},
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
