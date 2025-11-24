<template>
  <div class="pointer-events-none fixed inset-0 z-[60] flex items-start justify-end px-4 py-6 sm:p-6">
    <div class="flex w-full flex-col items-end space-y-2 sm:w-auto">
      <transition-group
        name="toast"
        tag="div"
        class="space-y-2 w-full sm:w-80"
      >
        <div
          v-for="toast in ui.toasts"
          :key="toast.id"
          class="pointer-events-auto rounded-xl border shadow-sm px-4 py-3 text-sm
                 bg-white/95 backdrop-blur-sm dark:bg-neutral-900 dark:border-neutral-700
                 flex items-start gap-3"
        >
          <div
            class="mt-0.5 h-2 w-2 rounded-full"
            :class="dotClass(toast.type)"
          ></div>

          <div class="flex-1">
            <p class="font-medium text-sm-dark dark:text-neutral-50" v-if="toast.title">
              {{ toast.title }}
            </p>
            <p class="text-xs text-neutral-600 dark:text-neutral-300">
              {{ toast.message }}
            </p>
          </div>

          <button
            class="ml-2 text-xs text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-100"
            @click="ui.removeToast(toast.id)"
          >
            ✕
          </button>
        </div>
      </transition-group>
    </div>
  </div>
</template>

<script setup>
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()

function dotClass(type) {
  switch (type) {
    case 'error':
      return 'bg-red-500'
    case 'warning':
      return 'bg-amber-500'
    case 'info':
      return 'bg-sky-500'
    default:
      return 'bg-emerald-500'
  }
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.18s ease-out;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.96);
}
</style>
