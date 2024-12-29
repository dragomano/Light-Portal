<script>
  let { children } = $props();
</script>

<span class="postfix">{@render children?.()}</span>

<style>
  .postfix {
    margin-left: 4px;
  }
</style>
