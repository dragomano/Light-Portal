<template>
  <transition name="fade-list" mode="out-in">
    <transition-group name="fade" tag="ul" appear>
      <slot></slot>
    </transition-group>
  </transition>
</template>

<script>
export default {
  name: 'ListTransition',
};
</script>
