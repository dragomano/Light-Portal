<script lang="ts">
  import { slide } from 'svelte/transition';
  import { SvelteShowdown } from 'svelte-showdown';
  import type { ShowdownExtension } from 'showdown';

  const classMap = {
    blockquote: 'bbc_standard_quote',
    code: 'bbc_code',
    h1: 'titlebg',
    h2: 'titlebg',
    h3: 'titlebg',
    image: 'bbc_img',
    a: 'bbc_link',
    ul: 'bbc_list',
    table: 'table_grid',
    tr: 'windowbg'
  };

  const bindings: ShowdownExtension[] = Object.keys(classMap).map((key) => ({
    type: 'output',
    regex: new RegExp(`<${key}(.*)>`, 'g'),
    replace: `<${key} class="${classMap[key]}" $1>`
  }));

  const options = {
    emoji: true,
    encodeEmails: true,
    openLinksInNewWindow: true
  };

  let { content = '', ...rest } = $props();
</script>

<fieldset transition:slide {...rest}>
  <SvelteShowdown {content} extensions={bindings} {options} />
</fieldset>
