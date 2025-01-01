<script>
  let { children } = $props();
</script>

<span>{@render children?.()}</span>

<style>
  span {
    margin-left: 4px;
  }
</style>
