<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ClockIcon } from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(email.value, password.value)
    router.push({ name: 'dashboard' })
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Invalid credentials'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-header">
        <ClockIcon class="login-logo" />
        <h1 class="login-title">TimeTracker</h1>
        <p class="login-subtitle">Sign in to your account</p>
      </div>

      <form class="login-form" @submit.prevent="handleLogin">
        <div v-if="error" class="login-error">{{ error }}</div>

        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            class="form-input"
            placeholder="you@example.com"
            required
            autofocus
          />
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input
            id="password"
            v-model="password"
            type="password"
            class="form-input"
            placeholder="Password"
            required
          />
        </div>

        <button type="submit" class="btn-primary login-submit" :disabled="loading">
          {{ loading ? 'Signing in...' : 'Sign in' }}
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped>
@reference "tailwindcss";
.login-page {
  @apply flex min-h-screen items-center justify-center bg-gray-50 px-4;
}

.login-card {
  @apply w-full max-w-sm;
}

.login-header {
  @apply flex flex-col items-center mb-8;
}

.login-logo {
  @apply h-12 w-12 text-blue-600;
}

.login-title {
  @apply mt-3 text-2xl font-bold text-gray-900;
}

.login-subtitle {
  @apply mt-1 text-sm text-gray-500;
}

.login-form {
  @apply bg-white rounded-lg border border-gray-200 shadow-sm px-6 py-4 space-y-4;
}

.login-error {
  @apply rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 border border-red-200;
}

.login-submit {
  @apply w-full;
}
</style>
