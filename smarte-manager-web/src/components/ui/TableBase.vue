<template>
  <div class="sm-card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-neutral-100 dark:divide-neutral-800">
        <thead class="bg-sm-cream/60 dark:bg-neutral-900">
          <tr>
            <slot name="head" />
          </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800 bg-white dark:bg-neutral-950">
          <slot name="body" />
        </tbody>
      </table>
    </div>
    <div
      v-if="$slots.footer"
      class="px-4 py-2 border-t border-neutral-100 dark:border-neutral-800 text-xs text-neutral-500 flex items-center justify-between"
    >
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
</script>
