<script>
  let { id, name, value = '' } = $props();
</script>

<div>
  <input bind:value {id} {name} type="color" />
</div>
