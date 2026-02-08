<script>
  import Postfix from './Postfix.svelte';

  let { id, name, value = '', option = { postfix: false } } = $props();

  let min = $derived(option.min ?? 0);
  let max = $derived(option.max);
  let step = $derived(option.step ?? (option[0] === 'int' ? 1 : 0.01));
  let postfix = $derived(option.postfix);
</script>

<div>
  <input type="number" bind:value id={id} name={name} min={min} max={max} step={step} />

  {#if postfix}
    <Postfix>{postfix}</Postfix>
  {/if}
</div>
