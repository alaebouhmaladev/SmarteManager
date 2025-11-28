<!-- src/components/ui/ModalBase.vue -->
<template>
  <transition name="fade">
    <div
      v-if="open"
      class="fixed inset-0 z-40 flex items-center justify-center"
    >
      <!-- backdrop -->
      <div
        class="absolute inset-0 bg-black/40"
        @click="$emit('close')"
      ></div>

      <!-- panel -->
      <div
        class="relative z-50 w-full max-w-md mx-4 sm:max-w-lg bg-white rounded-2xl shadow-xl border border-neutral-100"
      >
        <div class="px-5 pt-4 pb-3 border-b border-neutral-100">
          <h3 class="text-sm font-semibold text-sm-dark">
            <slot name="title" />
          </h3>
        </div>

        <div class="px-5 py-4 space-y-3">
          <slot name="body" />
        </div>

        <div
          v-if="$slots.footer"
          class="px-5 py-3 border-t border-neutral-100 bg-neutral-50/60 rounded-b-2xl"
        >
          <slot name="footer" />
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
defineProps({
  open: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['close'])
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
