<template>
  <transition name="fade">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 backdrop-blur-sm"
    >
      <div
        class="sm-card w-full max-w-md max-h-[90vh] overflow-y-auto p-5 relative"
      >
        <!-- Header -->
        <div class="flex items-start justify-between gap-3 mb-3">
          <div>
            <h2 v-if="title" class="text-sm font-semibold text-sm-dark dark:text-neutral-50">
              {{ title }}
            </h2>
            <p v-if="subtitle" class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
              {{ subtitle }}
            </p>
          </div>
          <button
            class="rounded-full p-1 text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800"
            @click="close"
          >
            ✕
          </button>
        </div>

        <!-- Body -->
        <div class="text-sm">
          <slot />
        </div>

        <!-- Footer -->
        <div
          v-if="$slots.footer"
          class="mt-4 pt-3 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-end gap-2"
        >
          <slot name="footer" />
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: String,
  subtitle: String,
})

const emit = defineEmits(['update:modelValue'])

function close() {
  emit('update:modelValue', false)
}
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
