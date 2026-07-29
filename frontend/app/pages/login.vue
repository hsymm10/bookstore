<template>
  <div class="glass-card-dark p-8 md:p-10 border border-white/20">
    <div class="text-center mb-8">
      <h2 class="text-3xl font-black text-white tracking-wider uppercase font-sans">Welcome Back!</h2>
      <p class="text-blue-100/60 text-sm mt-2">Login to your account</p>
    </div>

    <form @submit.prevent="handleLogin" class="space-y-6">
      <!-- Username -->
      <div>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/50">
            <Icon name="mdi:account-outline" class="text-xl" />
          </span>
          <input v-model="form.username" type="text" required placeholder="Username"
            class="input-field-glass !pl-12 !py-3.5 w-full text-sm rounded-full" :disabled="loading" />
        </div>
      </div>

      <!-- Password -->
      <div>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/50">
            <Icon name="mdi:lock-outline" class="text-xl" />
          </span>
          <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required placeholder="Password"
            class="input-field-glass !pl-12 !pr-12 !py-3.5 w-full text-sm rounded-full" :disabled="loading" />
          <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-4 flex items-center text-white/50 hover:text-white transition-colors">
            <Icon :name="showPassword ? 'mdi:eye-outline' : 'mdi:eye-off-outline'" class="text-xl" />
          </button>
        </div>
      </div>

      <!-- Forgot Password Link -->
      <div class="text-center">
        <NuxtLink to="/forgot-password" class="text-xs text-primary-200 hover:text-white hover:underline transition-colors font-bold uppercase tracking-wider">
          Forgot Password?
        </NuxtLink>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="w-full py-3.5 mt-2 bg-white text-primary-950 font-black rounded-full shadow-lg hover:bg-slate-100 active:scale-95 transition-all duration-200 text-sm tracking-widest uppercase flex items-center justify-center gap-2"
        :disabled="loading">
        <span v-if="loading" class="w-5 h-5 border-2 border-primary-950 border-t-transparent rounded-full animate-spin"></span>
        <span v-else>Login</span>
      </button>
    </form>

    <div class="text-center mt-6">
      <p class="text-sm text-blue-100/60">
        Belum punya akun?
        <NuxtLink to="/register" class="text-white font-bold hover:underline ml-1">Daftar</NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: 'guest'
})

const authStore = useAuthStore()
const api = useApi()
const router = useRouter()
const { $toast } = useNuxtApp() as any

const form = ref({
  username: '',
  password: ''
})
const showPassword = ref(false)
const loading = ref(false)

const handleLogin = async () => {
  loading.value = true
  try {
    const res = await api.post('/auth/login', form.value)
    const { user, token } = res.data.data
    
    // Save to pinia & cookies
    authStore.setAuth(user, token)
    
    $toast.fire({
      icon: 'success',
      title: res.data.message || 'Login berhasil!'
    })

    // Redirect based on role
    if (authStore.isAdmin) {
      await navigateTo('/admin/dashboard')
    } else {
      await navigateTo('/user/transaksi')
    }
  } catch (error: any) {
    const message = error.response?.data?.message || 'Username atau password salah.'
    $toast.fire({
      icon: 'error',
      title: message
    })
  } finally {
    loading.value = false
  }
}
</script>
