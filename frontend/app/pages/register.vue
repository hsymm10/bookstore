<template>
  <div>
    <div class="text-center mb-8">
      <h2 class="text-2xl font-bold text-white">Buat Akun Baru</h2>
      <p class="text-blue-100/60 text-sm mt-1">Daftar untuk mulai menjelajah buku</p>
    </div>

    <form @submit.prevent="handleRegister" class="space-y-4">
      <!-- Nama Lengkap -->
      <div>
        <label class="block text-white/80 text-xs font-semibold uppercase tracking-wider mb-1.5">Nama Lengkap</label>
        <input v-model="form.nama" type="text" required placeholder="Nama Lengkap"
          class="input-field-glass !py-2.5" :disabled="loading" />
      </div>

      <!-- Username -->
      <div>
        <label class="block text-white/80 text-xs font-semibold uppercase tracking-wider mb-1.5">Username</label>
        <input v-model="form.username" type="text" required placeholder="username"
          class="input-field-glass !py-2.5" :disabled="loading" />
      </div>

      <!-- Email -->
      <div>
        <label class="block text-white/80 text-xs font-semibold uppercase tracking-wider mb-1.5">Email</label>
        <input v-model="form.email" type="email" required placeholder="email@contoh.com"
          class="input-field-glass !py-2.5" :disabled="loading" />
      </div>

      <!-- Nomor HP -->
      <div>
        <label class="block text-white/80 text-xs font-semibold uppercase tracking-wider mb-1.5">Nomor HP</label>
        <input v-model="form.no_hp" type="text" required placeholder="0812xxxxxxxx"
          class="input-field-glass !py-2.5" :disabled="loading" />
      </div>

      <!-- Password -->
      <div>
        <label class="block text-white/80 text-xs font-semibold uppercase tracking-wider mb-1.5">Password</label>
        <div class="relative">
          <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required placeholder="Minimal 8 karakter"
            class="input-field-glass !py-2.5 !pr-10" :disabled="loading" />
          <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-3 flex items-center text-white/50 hover:text-white transition-colors">
            <Icon :name="showPassword ? 'mdi:eye-outline' : 'mdi:eye-off-outline'" class="text-lg" />
          </button>
        </div>
      </div>

      <!-- Password Confirmation -->
      <div>
        <label class="block text-white/80 text-xs font-semibold uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
        <div class="relative">
          <input v-model="form.password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'" required placeholder="Ulangi password"
            class="input-field-glass !py-2.5 !pr-10" :disabled="loading" />
          <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation" class="absolute inset-y-0 right-3 flex items-center text-white/50 hover:text-white transition-colors">
            <Icon :name="showPasswordConfirmation ? 'mdi:eye-outline' : 'mdi:eye-off-outline'" class="text-lg" />
          </button>
        </div>
      </div>

      <!-- Submit -->
      <button type="submit" class="w-full btn-primary bg-white text-primary-700 hover:bg-blue-50 py-3 mt-4 justify-center"
        :disabled="loading">
        <span v-if="loading" class="w-5 h-5 border-2 border-primary-700 border-t-transparent rounded-full animate-spin"></span>
        <span v-else>Daftar Sekarang</span>
      </button>
    </form>

    <div class="text-center mt-6">
      <p class="text-sm text-blue-100/60">
        Sudah punya akun?
        <NuxtLink to="/login" class="text-white font-bold hover:underline">Masuk</NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth.store'

definePageMeta({
  layout: 'auth',
  middleware: 'guest'
})

const authStore = useAuthStore()
const api = useApi()
const router = useRouter()
const { $toast } = useNuxtApp() as any

const form = ref({
  nama: '',
  username: '',
  email: '',
  no_hp: '',
  password: '',
  password_confirmation: ''
})
const loading = ref(false)
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const handleRegister = async () => {
  loading.value = true
  try {
    const res = await api.post('/auth/register', form.value)
    const { user, token } = res.data.data
    
    // Save to pinia & cookies
    authStore.setAuth(user, token)
    
    $toast.fire({
      icon: 'success',
      title: res.data.message || 'Registrasi berhasil!'
    })

    router.push('/')
  } catch (error: any) {
    const errors = error.response?.data?.errors
    let message = error.response?.data?.message || 'Gagal mendaftar. Silakan periksa kembali input Anda.'
    
    // Get first validation error message if exists
    if (errors) {
      const firstKey = Object.keys(errors)[0]
      if (errors[firstKey] && errors[firstKey].length > 0) {
        message = errors[firstKey][0]
      }
    }
    
    $toast.fire({
      icon: 'error',
      title: message
    })
  } finally {
    loading.value = false
  }
}
</script>
