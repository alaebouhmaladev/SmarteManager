<template>
  <div class="min-h-screen flex items-center justify-center bg-sm-cream p-4">
    
    <div class="sm-card w-full max-w-md p-8">

      <!-- Brand -->
      <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-sm-dark">SmartManager</h1>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
          Sign in to continue
        </p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">

        <InputField
          v-model="email"
          label="Email"
          type="email"
          placeholder="you@example.com"
        />

        <InputField
          v-model="password"
          label="Password"
          type="password"
          placeholder="•••••••••"
        />

        <PrimaryButton
          fullWidth
          variant="primary"
          :loading="loading"
          type="submit"
        >
          Login
        </PrimaryButton>

      </form>

      <!-- Error -->
      <p
        v-if="error"
        class="text-center text-red-500 text-sm mt-4"
      >
        {{ error }}
      </p>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import InputField from '@/components/ui/InputField.vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref(null)

const handleLogin = async () => {
  loading.value = true
  error.value = null

  try {
    await auth.login(email.value, password.value)

    const redirect = route.query.redirect || '/dashboard'
    router.push(redirect)
  } catch (e) {
    error.value = 'Invalid credentials'
  } finally {
    loading.value = false
  }
}
</script>
