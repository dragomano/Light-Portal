<script>
  import { Converter } from 'showdown';

  let { content = '', extensions = [], flavor = 'github', options = {} } = $props();

  const converter = new Converter({ extensions });

  converter.setFlavor(flavor);

  Object.entries(options).forEach(({ key, value }) => converter.setOption(key, value));

  const htmlContent = $derived(converter.makeHtml(content));
</script>

{@html htmlContent}
