<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    @click="onClick"
    class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium transition-all
           disabled:opacity-60 disabled:cursor-not-allowed
           focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sm-yellow focus-visible:ring-offset-2
           focus-visible:ring-offset-sm-cream"
    :class="[
      variantClass,
      fullWidth ? 'w-full' : '',
      loading ? 'gap-2' : ''
    ]"
  >
    <!-- Loader -->
    <span
      v-if="loading"
      class="h-4 w-4 rounded-full border-2 border-sm-yellow border-t-transparent animate-spin"
    ></span>

    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary', // primary | secondary | danger
  },
  type: {
    type: String,
    default: 'button',
  },
  fullWidth: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['click'])

const variantClass = computed(() => {
  switch (props.variant) {
    case 'secondary':
      return 'bg-white text-sm-dark border border-neutral-200 hover:bg-neutral-100'
    case 'danger':
      return 'bg-red-500 text-white hover:bg-red-600'
    default:
      return 'bg-sm-dark text-sm-cream hover:bg-black'
  }
})

function onClick(e) {
  if (props.disabled || props.loading) return
  emit('click', e)
}
</script>
