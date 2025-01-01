<script>
  let { id, name, value = '', option = {} } = $props();

  const placeholder = $derived(option.placeholder);
  const required = $derived(option.required);
</script>

<div>
  <input type="url" {id} {name} bind:value {placeholder} {required} />
</div>
