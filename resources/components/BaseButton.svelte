<script>
  import { useIconStore } from '../js/stores.js';

  let { tag = 'button', icon = '', children, ...rest } = $props();

  const button = $derived(
    tag === 'button'
      ? {
          tag: 'button',
          class: 'button',
          role: undefined
        }
      : {
          tag: 'span',
          class: undefined,
          role: 'button'
        }
  );

  const preparedIcon = $derived($useIconStore[icon] ?? '');
</script>

<svelte:element this={button.tag} {...rest} class={[button.class, rest.class]} role={button.role}>
  {@html preparedIcon}
  {@render children?.()}
</svelte:element>
