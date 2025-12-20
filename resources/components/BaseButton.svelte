<script lang="ts">
  import { iconState } from '../js/states.svelte';
  import type { Snippet } from 'svelte';
  import type { HTMLAttributes } from 'svelte/elements';

  type AllowedTags = 'button' | 'span';

  type CommonProps = {
    tag?: AllowedTags;
    icon?: string;
    children?: Snippet;
  };

  type ButtonProps = CommonProps & HTMLAttributes<HTMLButtonElement> & {
    name?: string;
    form?: string;
    type?: string;
    disabled?: boolean;
  };

  type SpanProps = CommonProps & HTMLAttributes<HTMLSpanElement>;

  let { tag = 'button', icon = '', children, ...rest }: ButtonProps | SpanProps = $props();

  // svelte-ignore state_referenced_locally
  const elementProps = tag === 'button'
    ? { tag: 'button', class: 'button', role: undefined }
    : { tag: 'span', class: undefined, role: 'button' };

  // svelte-ignore state_referenced_locally
  const preparedIcon = iconState && (iconState[icon] ?? '');
</script>

<svelte:element
  this={elementProps.tag}
  {...rest}
  class={[elementProps.class, rest.class].filter(Boolean).join(' ')}
  role={elementProps.role}
>
  {@html preparedIcon}
  {@render children?.()}
</svelte:element>
