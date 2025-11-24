<template>
  <div class="space-y-1.5">
    <label
      v-if="label"
      class="block text-xs font-medium text-neutral-700 dark:text-neutral-300"
      :for="id"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div
      class="flex items-center gap-2 rounded-xl border bg-white px-3 py-2 text-sm transition
             focus-within:ring-2 focus-within:ring-sm-yellow focus-within:border-sm-yellow
             dark:bg-neutral-900 dark:border-neutral-700"
      :class="{
        'border-red-400 focus-within:ring-red-400 focus-within:border-red-400': error,
        'opacity-60 bg-neutral-100 cursor-not-allowed': disabled,
      }"
    >
      <slot name="icon-left" />
      <input
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        @input="onInput"
        class="flex-1 bg-transparent outline-none border-none text-sm
               text-neutral-800 dark:text-neutral-100 placeholder:text-neutral-400"
      />
      <slot name="icon-right" />
    </div>

    <p v-if="help && !error" class="text-[11px] text-neutral-500">
      {{ help }}
    </p>
    <p v-if="error" class="text-[11px] text-red-500">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
const props = defineProps({
  label: String,
  id: String,
  modelValue: [String, Number],
  type: {
    type: String,
    default: 'text',
  },
  placeholder: String,
  help: String,
  error: String,
  required: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])

function onInput(e) {
  emit('update:modelValue', e.target.value)
}
</script>
