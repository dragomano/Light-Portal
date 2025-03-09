<script>
  let { id, name, value = 0, option = {} } = $props();

  let min = $state(option.min ?? 0);
  let max = $state(option.max ?? 100);
  let step = $state(option.step ?? 1);
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
