<script>
  import Postfix from './Postfix.svelte';

  let { id, name, value = '', option = { postfix: false } } = $props();

  const min = $derived(option.min ?? 0);
  const max = $derived(option.max);
  const step = $derived(option.step ?? (option[0] === 'int' ? 1 : 0.01));
  const postfix = $derived(option.postfix);
</script>

<div>
  <input type="number" bind:value id={id} name={name} min={min} max={max} step={step} />

  {#if postfix}
    <Postfix>{postfix}</Postfix>
  {/if}
</div>
