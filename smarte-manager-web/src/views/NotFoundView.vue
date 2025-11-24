<template>
  <div class="flex min-h-[60vh] flex-col items-center justify-center text-center space-y-4">
    <p class="text-xs uppercase tracking-[0.2em] text-neutral-500">
      Error 404
    </p>
    <h1 class="text-2xl font-semibold text-sm-dark dark:text-neutral-50">
      Page not found
    </h1>
    <p class="text-sm text-neutral-500 max-w-md">
      The page you’re looking for doesn’t exist or has been moved.
    </p>
    <RouterLink
      :to="{ name: 'dashboard' }"
      class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-medium
             bg-sm-dark text-sm-cream hover:bg-black dark:bg-neutral-900 dark:text-neutral-50"
    >
      Back to dashboard
    </RouterLink>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
