<template>
  <div class="glass-card-dark p-8 md:p-10 border border-white/20">
    <!-- Step 1: Input Email -->
    <div v-if="step === 1">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-500/20 text-blue-300 border border-blue-400/30 mb-3 shadow-inner">
          <Icon name="mdi:email-outline" class="text-3xl" />
        </div>
        <h2 class="text-3xl font-black text-white tracking-wider uppercase font-sans">Lupa Password</h2>
        <p class="text-blue-100/70 text-sm mt-2">Masukkan email akun Anda untuk memulai proses reset password</p>
      </div>

      <form @submit.prevent="handleCheckEmail" class="space-y-6">
        <div>
          <label class="block text-xs uppercase tracking-wider text-blue-200/80 mb-2 font-bold text-center">
            Alamat Email
          </label>
          <input v-model="form.email" type="email" required placeholder="Contoh: user@email.com"
            class="input-field-glass !py-3.5 w-full text-center text-sm rounded-full focus:ring-2 focus:ring-blue-400" :disabled="loading" />
        </div>

        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-blue-400 to-indigo-500 text-white font-black rounded-full shadow-lg hover:from-blue-300 hover:to-indigo-400 active:scale-95 transition-all duration-200 text-sm tracking-wider uppercase flex items-center justify-center gap-2"
          :disabled="loading">
          <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          <span v-else class="flex items-center gap-2">
            Lanjut
            <Icon name="mdi:arrow-right" class="text-lg" />
          </span>
        </button>
      </form>
    </div>

    <!-- Step 2: Confirm WhatsApp Number -->
    <div v-else-if="step === 2">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 mb-3 shadow-inner">
          <Icon name="mdi:whatsapp" class="text-3xl" />
        </div>
        <h2 class="text-3xl font-black text-white tracking-wider uppercase font-sans">Konfirmasi WhatsApp</h2>
        <p class="text-blue-100/70 text-sm mt-2">Apakah nomor WhatsApp berikut terhubung dengan akun Anda?</p>
      </div>

      <div class="space-y-6">
        <div class="bg-white/5 border border-white/10 rounded-2xl p-4 text-center">
          <p class="text-xs text-blue-200/60 uppercase tracking-wider font-bold mb-1">Nomor WhatsApp Terhubung</p>
          <p class="text-2xl font-black text-emerald-300 font-mono tracking-wider">{{ maskedPhone }}</p>
        </div>

        <div class="flex flex-col gap-3">
          <button @click="handleSendOtp" class="w-full py-3.5 bg-gradient-to-r from-emerald-400 to-teal-500 text-slate-950 font-black rounded-full shadow-lg hover:from-emerald-300 hover:to-teal-400 active:scale-95 transition-all duration-200 text-sm tracking-wider uppercase flex items-center justify-center gap-2"
            :disabled="loading">
            <span v-if="loading" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
            <span v-else class="flex items-center gap-2">
              <Icon name="mdi:whatsapp" class="text-lg" />
              Ya, Kirim Kode OTP
            </span>
          </button>
          
          <button @click="step = 1" class="w-full py-3 border border-white/20 rounded-full text-white font-bold hover:bg-white/5 active:scale-95 transition-all text-sm uppercase tracking-wider"
            :disabled="loading">
            Tidak, Kembali
          </button>
        </div>
      </div>
    </div>

    <!-- Step 3: Input & Verify OTP -->
    <div v-else-if="step === 3">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-purple-500/20 text-purple-300 border border-purple-400/30 mb-3 shadow-inner">
          <Icon name="mdi:shield-key-outline" class="text-3xl" />
        </div>
        <h2 class="text-3xl font-black text-white tracking-wider uppercase font-sans">Verifikasi OTP</h2>
        <p class="text-blue-100/70 text-xs mt-2">Kode OTP telah dikirimkan ke nomor WhatsApp {{ maskedPhone }}</p>
      </div>

      <form @submit.prevent="handleVerifyOtp" class="space-y-6">
        <div>
          <label class="block text-xs uppercase tracking-wider text-purple-200/90 mb-2 font-bold text-center">
            Masukkan 6 Digit OTP
          </label>
          <input v-model="form.otp" type="text" maxlength="6" pattern="[0-9]*" inputmode="numeric" required placeholder="123456"
            class="input-field-glass !py-3.5 w-full text-center text-2xl font-mono tracking-[0.5em] font-black rounded-2xl border-purple-400/40 focus:border-purple-300" :disabled="loading" />
          
          <!-- Resend Timer -->
          <div class="flex justify-end mt-2">
            <button type="button" @click="handleSendOtp" :disabled="resendCooldown > 0 || loading"
              class="text-xs text-purple-300 hover:text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1">
              <Icon name="mdi:refresh" class="text-sm" />
              <span v-if="resendCooldown > 0">Kirim Ulang OTP ({{ resendCooldown }}s)</span>
              <span v-else>Kirim Ulang OTP via WA</span>
            </button>
          </div>
        </div>

        <div class="flex flex-col gap-3">
          <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-purple-400 to-indigo-500 text-white font-black rounded-full shadow-lg hover:from-purple-300 hover:to-indigo-400 active:scale-95 transition-all duration-200 text-sm tracking-wider uppercase flex items-center justify-center gap-2"
            :disabled="loading">
            <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span v-else class="flex items-center gap-2">
              Verifikasi OTP
            </span>
          </button>
          
          <button type="button" @click="step = 2" class="w-full py-3 border border-white/20 rounded-full text-white font-bold hover:bg-white/5 active:scale-95 transition-all text-sm uppercase tracking-wider"
            :disabled="loading">
            Kembali
          </button>
        </div>
      </form>
    </div>

    <!-- Step 4: Password Baru -->
    <div v-else-if="step === 4">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 mb-3 shadow-inner">
          <Icon name="mdi:lock-reset" class="text-3xl" />
        </div>
        <h2 class="text-3xl font-black text-white tracking-wider uppercase font-sans">Ubah Password</h2>
        <p class="text-blue-100/70 text-sm mt-2">Buat password baru untuk akun Anda</p>
      </div>

      <form @submit.prevent="handleResetPassword" class="space-y-5">
        <!-- Password Baru -->
        <div>
          <label class="block text-xs uppercase tracking-wider text-blue-200/80 mb-1.5 font-bold">Password Baru</label>
          <div class="relative">
            <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required placeholder="Minimal 8 karakter"
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
            <input v-model="form.password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'" required placeholder="Ulangi password baru"
              class="input-field-glass !py-3 !pr-10 w-full text-center text-sm rounded-full" :disabled="loading" />
            <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation" class="absolute inset-y-0 right-4 flex items-center text-white/50 hover:text-white transition-colors">
              <Icon :name="showPasswordConfirmation ? 'mdi:eye-outline' : 'mdi:eye-off-outline'" class="text-xl" />
            </button>
          </div>
        </div>

        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-indigo-400 to-blue-500 text-white font-black rounded-full shadow-lg hover:from-indigo-300 hover:to-blue-400 active:scale-95 transition-all duration-200 text-sm tracking-wider uppercase flex items-center justify-center gap-2"
          :disabled="loading">
          <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          <span v-else class="flex items-center gap-2">
            Simpan Password Baru
          </span>
        </button>
      </form>
    </div>

    <!-- Bottom link to login -->
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
const router = useRouter()
const { $toast } = useNuxtApp() as any

const step = ref(1)
const loading = ref(false)
const resendCooldown = ref(0)
let timer: any = null

const form = ref({
  email: '',
  otp: '',
  password: '',
  password_confirmation: ''
})

const maskedPhone = ref('')
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

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

onUnmounted(() => {
  if (timer) clearInterval(timer)
})

const handleCheckEmail = async () => {
  loading.value = true
  try {
    const res = await api.post('/auth/forgot-password/check-email', { email: form.value.email })
    maskedPhone.value = res.data.data.masked_phone
    step.value = 2
  } catch (error: any) {
    const message = error.response?.data?.message || 'Email tidak terdaftar.'
    $toast.fire({
      icon: 'error',
      title: message
    })
  } finally {
    loading.value = false
  }
}

const handleSendOtp = async () => {
  loading.value = true
  try {
    const res = await api.post('/auth/forgot-password/send-otp', { email: form.value.email })
    $toast.fire({
      icon: 'success',
      title: res.data.message || 'Kode OTP telah dikirim ke WhatsApp!'
    })
    startResendTimer()
    step.value = 3
  } catch (error: any) {
    const message = error.response?.data?.message || 'Gagal mengirim kode OTP.'
    $toast.fire({
      icon: 'error',
      title: message
    })
  } finally {
    loading.value = false
  }
}

const handleVerifyOtp = async () => {
  loading.value = true
  try {
    await api.post('/auth/forgot-password/verify-otp', {
      email: form.value.email,
      otp: form.value.otp
    })
    $toast.fire({
      icon: 'success',
      title: 'Kode OTP Terverifikasi!'
    })
    step.value = 4
  } catch (error: any) {
    const message = error.response?.data?.message || 'Kode OTP tidak valid atau kedaluwarsa.'
    $toast.fire({
      icon: 'error',
      title: message
    })
  } finally {
    loading.value = false
  }
}

const handleResetPassword = async () => {
  loading.value = true
  try {
    const res = await api.post('/auth/reset-password', {
      identifier: form.value.email,
      otp: form.value.otp,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation
    })
    
    $toast.fire({
      icon: 'success',
      title: res.data.message || 'Password berhasil diubah!'
    })
    
    router.push('/login')
  } catch (error: any) {
    const errors = error.response?.data?.errors
    let message = error.response?.data?.message || 'Gagal mengubah password.'
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
