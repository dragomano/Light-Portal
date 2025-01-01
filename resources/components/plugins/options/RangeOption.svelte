<script>
  let { id, name, value = 0, option = {} } = $props();

  const min = $derived(option.min ?? 0);
  const max = $derived(option.max ?? 100);
  const step = $derived(option.step ?? 1);
</script>

<div>
  <input type="range" bind:value {id} {name} {min} {max} {step} />
  <span>{value}</span>
</div>

<style>
  span {
    margin-left: 6px;
    font-weight: bold;
  }
</style>
