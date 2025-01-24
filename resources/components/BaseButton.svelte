<script>
  import { iconState } from '../js/states.svelte.js';

  let { tag = 'button', icon = '', children, ...rest } = $props();

  const button = tag === 'button'
    ? {
        tag: 'button',
        class: 'button',
        role: undefined
      }
    : {
        tag: 'span',
        class: undefined,
        role: 'button'
      };

  const preparedIcon = iconState[icon] ?? '';
</script>

<svelte:element this={button.tag} {...rest} class={[button.class, rest.class]} role={button.role}>
  {@html preparedIcon}
  {@render children?.()}
</svelte:element>
