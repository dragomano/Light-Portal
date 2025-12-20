<script>
  let { id, name, value = '', option = {} } = $props();

  let placeholder = $derived(option.placeholder);
  let required = $derived(option.required);
</script>

<div>
  <input type="url" {id} {name} bind:value {placeholder} {required} />
</div>
