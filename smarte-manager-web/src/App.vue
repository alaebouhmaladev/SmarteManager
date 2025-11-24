<!-- src/App.vue -->
<template>
  <div class="min-h-screen bg-sm-cream text-sm-dark">
    <RouterView />
    <ToastContainer />
  </div>
</template>

<script setup>
import { RouterView } from 'vue-router'
import ToastContainer from '@/components/ui/ToastContainer.vue'
</script>
