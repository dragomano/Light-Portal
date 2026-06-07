<script lang="ts">
  import { slide } from 'svelte/transition';
  import { Marked } from 'marked';
  import DOMPurify from 'dompurify';

  const classMap: Record<string, string> = {
    blockquote: 'bbc_standard_quote',
    code: 'bbc_code',
    h1: 'titlebg',
    h2: 'titlebg',
    h3: 'titlebg',
    img: 'bbc_img',
    a: 'bbc_link',
    ul: 'bbc_list',
    table: 'table_grid',
    tr: 'windowbg'
  };

  const marked = new Marked();

  marked.use({
    gfm: true,
    hooks: {
      postprocess: (html) =>
        Object.entries(classMap).reduce(
          (acc, [tag, cls]) =>
            acc.replace(new RegExp(`<${tag}([ >])`, 'g'), `<${tag} class="${cls}"$1`),
          html
        )
    },
    renderer: {
      link: ({ href, title, text }) =>
        `<a href="${href}"${title ? ` title="${title}"` : ''} class="bbc_link" target="_blank" rel="noopener noreferrer">${text}</a>`
    }
  });

  let { content = '', ...rest } = $props();

  let html = $derived(
    DOMPurify.sanitize(marked.parse(content) as string, {
      ADD_ATTR: ['target', 'rel']
    })
  );
</script>

<fieldset transition:slide {...rest}>
  {@html html}
</fieldset>
