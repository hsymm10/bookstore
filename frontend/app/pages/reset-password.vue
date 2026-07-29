<template>
  <div class="glass-card-dark p-8 md:p-10 border border-white/20">
    <div class="text-center mb-6">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-purple-500/20 text-purple-300 border border-purple-400/30 mb-3 shadow-inner">
        <Icon name="mdi:shield-key-outline" class="text-3xl" />
      </div>
      <h2 class="text-3xl font-black text-white tracking-wider uppercase font-sans">Reset Password</h2>
      <p v-if="maskedPhone" class="text-emerald-300 text-xs font-semibold mt-2 flex items-center justify-center gap-1.5">
        <Icon name="mdi:whatsapp" class="text-base" />
        OTP dikirim ke WA: {{ maskedPhone }}
      </p>
      <p v-else class="text-blue-100/70 text-sm mt-2">Masukkan kode OTP 6-digit dari WhatsApp & Password Baru</p>
    </div>

    <form @submit.prevent="handleResetPassword" class="space-y-5">
      <!-- Identifier (hidden if prefilled, shown if missing) -->
      <div v-if="!isPreFilled">
        <label class="block text-xs uppercase tracking-wider text-blue-200/80 mb-1.5 font-bold">Email / No. HP</label>
        <input v-model="form.identifier" type="text" required placeholder="Email / Telp"
          class="input-field-glass !py-3 w-full text-center text-sm rounded-full" :disabled="loading" />
      </div>

      <!-- Kode OTP (6 Digits) -->
      <div>
        <label class="block text-xs uppercase tracking-wider text-purple-200/90 mb-1.5 font-bold text-center">
          Kode OTP (6 Digit)
        </label>
        <div class="relative">
          <input v-model="form.otp" type="text" maxlength="6" pattern="[0-9]*" inputmode="numeric" required placeholder="123456"
            autocomplete="one-time-code"
            class="input-field-glass !py-3.5 w-full text-center text-2xl font-mono tracking-[0.5em] font-black rounded-2xl border-purple-400/40 focus:border-purple-300"
            :disabled="loading" />
        </div>
        <div class="flex justify-end mt-2">
          <button type="button" @click="handleResendOtp" :disabled="resendCooldown > 0 || resendLoading"
            class="text-xs text-purple-300 hover:text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1">
            <span v-if="resendLoading" class="w-3 h-3 border-2 border-purple-300 border-t-transparent rounded-full animate-spin"></span>
            <Icon v-else name="mdi:refresh" class="text-sm" />
            <span v-if="resendCooldown > 0">Kirim Ulang OTP ({{ resendCooldown }}s)</span>
            <span v-else>Kirim Ulang OTP via WA</span>
          </button>
        </div>
      </div>

      <!-- Password Baru -->
      <div>
        <label class="block text-xs uppercase tracking-wider text-blue-200/80 mb-1.5 font-bold">Password Baru</label>
        <div class="relative">
          <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required placeholder="••••••••"
            class="input-field-glass !py-3 !pr-10 w-full text-center text-sm rounded-full" :disabled="loading" />
          <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-4 flex items-center text-white/50 hover:text-white transition-colors">
            <Icon :name="showPassword ? 'mdi:eye-outline' : 'mdi:eye-off-outline'" class="text-xl" />
          </button>
        </div>
      </div>

      <!-- Confirm Password -->
      <div>
        <label class="block text-xs uppercase tracking-wider text-blue-200/80 mb-1.5 font-bold">Konfirmasi Password</label>
        <div class="relative">
          <input v-model="form.password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'" required placeholder="••••••••"
            class="input-field-glass !py-3 !pr-10 w-full text-center text-sm rounded-full" :disabled="loading" />
          <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation" class="absolute inset-y-0 right-4 flex items-center text-white/50 hover:text-white transition-colors">
            <Icon :name="showPasswordConfirmation ? 'mdi:eye-outline' : 'mdi:eye-off-outline'" class="text-xl" />
          </button>
        </div>
      </div>

      <!-- Submit -->
      <div class="flex justify-center pt-2">
        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-purple-400 to-indigo-500 text-slate-950 font-black rounded-full shadow-lg hover:from-purple-300 hover:to-indigo-400 active:scale-95 transition-all duration-200 text-sm tracking-wider uppercase flex items-center justify-center gap-2"
          :disabled="loading">
          <span v-if="loading" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
          <span v-else class="flex items-center gap-2">
            <Icon name="mdi:check-circle" class="text-lg" />
            Konfirmasi Reset Password
          </span>
        </button>
      </div>
    </form>

    <div class="text-center mt-6">
      <p class="text-sm text-blue-100/60">
        Kembali ke
        <NuxtLink to="/login" class="text-white font-bold hover:underline ml-1">Login</NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: 'guest'
})

const api = useApi()
const route = useRoute()
const router = useRouter()
const { $toast } = useNuxtApp() as any

const form = ref({
  identifier: '',
  otp: '',
  password: '',
  password_confirmation: ''
})
const maskedPhone = ref('')
const loading = ref(false)
const resendLoading = ref(false)
const resendCooldown = ref(0)
const isPreFilled = ref(false)
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
let timer: any = null

onMounted(() => {
  if (route.query.identifier) {
    form.value.identifier = route.query.identifier as string
    isPreFilled.value = true
  }
  if (route.query.masked_phone) {
    maskedPhone.value = route.query.masked_phone as string
  }
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})

const startResendTimer = () => {
  resendCooldown.value = 60
  if (timer) clearInterval(timer)
  timer = setInterval(() => {
    if (resendCooldown.value > 0) {
      resendCooldown.value--
    } else {
      clearInterval(timer)
    }
  }, 1000)
}

const handleResendOtp = async () => {
  if (!form.value.identifier) {
    $toast.fire({
      icon: 'error',
      title: 'Email atau nomor WhatsApp wajib diisi.'
    })
    return
  }

  resendLoading.value = true
  try {
    const res = await api.post('/auth/forgot-password', { identifier: form.value.identifier })
    $toast.fire({
      icon: 'success',
      title: res.data.message || 'Kode OTP baru berhasil dikirim via WA!'
    })
    if (res.data.data?.masked_phone) {
      maskedPhone.value = res.data.data.masked_phone
    }
    startResendTimer()
  } catch (error: any) {
    const message = error.response?.data?.message || 'Gagal mengirim ulang OTP.'
    $toast.fire({
      icon: 'error',
      title: message
    })
  } finally {
    resendLoading.value = false
  }
}

const handleResetPassword = async () => {
  loading.value = true
  try {
    const res = await api.post('/auth/reset-password', form.value)
    
    $toast.fire({
      icon: 'success',
      title: res.data.message || 'Password berhasil direset! Silakan login.'
    })

    router.push('/login')
  } catch (error: any) {
    const errors = error.response?.data?.errors
    let message = error.response?.data?.message || 'Gagal mereset password.'
    
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
