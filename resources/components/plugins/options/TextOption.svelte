<script>
  let { id, name, value = '', option = {} } = $props();

  const pattern = $derived(option.pattern);
  const placeholder = $derived(option.placeholder);
  const required = $derived(option.required);
</script>

<div>
  <input type="text" {id} {name} bind:value {pattern} {placeholder} {required} />
</div>
