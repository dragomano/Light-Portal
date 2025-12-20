<script>
  let { id, name, value = '', option = {} } = $props();

  let pattern = $derived(option.pattern);
  let placeholder = $derived(option.placeholder);
  let required = $derived(option.required);
</script>

<div>
  <input type="text" {id} {name} bind:value {pattern} {placeholder} {required} />
</div>
