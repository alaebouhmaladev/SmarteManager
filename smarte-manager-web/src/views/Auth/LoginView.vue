<template>
  <div class="min-h-screen flex items-center justify-center bg-sm-cream">
    <div class="w-full max-w-md sm-card p-6">
      <h1 class="text-xl font-semibold text-sm-dark mb-1">
        SmartManager Login
      </h1>
      <p class="text-xs text-neutral-500 mb-4">
        Sign in with your admin or manager account to continue.
      </p>

      <!-- Error text under title (optional) -->
      <p
        v-if="auth.error"
        class="mb-3 text-xs text-red-500"
      >
        {{ auth.error }}
      </p>

      <form class="space-y-3" @submit.prevent="handleLogin">
        <div>
          <label class="block text-xs font-medium text-neutral-700 mb-1">
            Email
          </label>
          <input
            v-model="form.email"
            type="email"
            autocomplete="email"
            required
            class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
            placeholder="admin@example.com"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-neutral-700 mb-1">
            Password
          </label>
          <input
            v-model="form.password"
            type="password"
            autocomplete="current-password"
            required
            class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
            placeholder="••••••••"
          />
        </div>

        <button
          type="submit"
          :disabled="auth.loading"
          class="w-full mt-2 inline-flex items-center justify-center px-4 py-2 rounded-xl
                 text-xs font-medium bg-sm-dark text-sm-cream hover:bg-black
                 disabled:opacity-60 disabled:cursor-not-allowed"
        >
          <span v-if="!auth.loading">Login</span>
          <span v-else>Logging in...</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const form = reactive({
  email: '',
  password: '',
})

const handleLogin = async () => {
  // prevent double submit
  if (auth.loading) return

  // clear previous error before new attempt
  auth.error = null

  await auth.login({
    email: form.email,
    password: form.password,
  })
}
</script>
