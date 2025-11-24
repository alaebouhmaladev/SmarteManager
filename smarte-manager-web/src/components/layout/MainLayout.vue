<template>
  <div class="flex h-screen w-full overflow-hidden bg-sm-cream dark:bg-neutral-900">
    <!-- Sidebar -->
    <Sidebar
      :open="sidebarOpen"
      @close="sidebarOpen = false"
      class="flex-shrink-0"
    />

    <!-- Main content area -->
    <div class="flex flex-col flex-1 overflow-hidden">
      <!-- Topbar -->
      <header
        class="h-14 flex items-center justify-between px-4 border-b border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900"
      >
        <div class="flex items-center gap-3">
          <!-- Mobile sidebar toggle -->
          <button
            class="md:hidden p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800"
            @click="sidebarOpen = true"
          >
            ☰
          </button>

          <!-- Page title (from route meta or fallback) -->
          <h1 class="text-lg font-semibold text-sm-dark dark:text-neutral-50">
            {{ pageTitle }}
          </h1>
        </div>

        <div class="flex items-center gap-3">
          <!-- User role badge -->
          <span v-if="auth.user?.role" class="sm-badge">
            {{ auth.user.role.toUpperCase() }}
          </span>

          <!-- Logout (will do nothing in dev bypass if not logged in, that's fine) -->
          <button
            @click="auth.logout"
            class="text-sm text-red-500 hover:underline"
          >
            Logout
          </button>
        </div>
      </header>

      <!-- PAGE CONTENT: this is where child routes render -->
      <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-sm-cream dark:bg-neutral-900">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute, RouterView } from 'vue-router'
import Sidebar from './Sidebar.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()

const sidebarOpen = ref(false)

const pageTitle = computed(() => route.meta.title || 'Dashboard')
</script>

<style scoped>
main::-webkit-scrollbar {
  width: 6px;
}
main::-webkit-scrollbar-thumb {
  background: #cfcfcf;
  border-radius: 20px;
}
main::-webkit-scrollbar-track {
  background: transparent;
}
</style>
