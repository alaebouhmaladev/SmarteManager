<!-- src/components/ui/TableBase.vue -->
<template>
  <div class="overflow-hidden rounded-2xl border border-neutral-100 bg-white">
    <table class="min-w-full divide-y divide-neutral-100">
      <thead class="bg-neutral-50">
        <tr>
          <slot name="head" />
        </tr>
      </thead>
      <tbody class="divide-y divide-neutral-100 bg-white">
        <slot name="body" />
      </tbody>
    </table>

    <div
      v-if="$slots.footer"
      class="px-4 py-2 text-[11px] text-neutral-500 bg-neutral-50 border-t border-neutral-100"
    >
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
// base table shell
</script>
