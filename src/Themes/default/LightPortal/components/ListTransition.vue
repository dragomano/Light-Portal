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

<style>
.fade-enter-active,
.fade-move {
  transition: 0.4s ease all;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(0.6);
}

.fade-leave-active {
  transition: 0.4s ease all;
  position: absolute;
}

.fade-list-enter-active,
.fade-list-leave-active {
  transition: 0.4s ease all;
}

.fade-list-enter-from,
.fade-list-leave-to {
  transform: translateY(10px);
  opacity: 0;
}
</style>
