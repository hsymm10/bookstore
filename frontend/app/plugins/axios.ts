import axios from 'axios'

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()

  // ⚠️ PENTING: useCookie dan useAuthStore HARUS dipanggil di sini (sinkron,
  // dalam composable context), bukan di dalam callback interceptor yang async.
  // Jika dipanggil di dalam callback, Nuxt akan kehilangan context dan
  // mengembalikan nilai kosong, menyebabkan request tanpa token → 401 → logout otomatis.
  const authTokenCookie = useCookie<string | null>('auth_token', { path: '/' })
  const authStore       = useAuthStore()

  const api = axios.create({
    baseURL: config.public.apiBase,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
  })

  // ─── Request interceptor ───────────────────────────────────────────────────
  // Membaca nilai cookie secara reaktif (.value) dari ref yang sudah di-capture.
  api.interceptors.request.use((request) => {
    const token = authStore.token || authTokenCookie.value
    if (token) {
      request.headers.Authorization = `Bearer ${token}`
    }
    return request
  })

  // ─── Response interceptor ──────────────────────────────────────────────────
  api.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response?.status === 401) {
        // Token tidak valid atau kadaluarsa → bersihkan state dan arahkan ke landing page
        authStore.clearAuth()
        // Gunakan navigateTo dari Nuxt bukan router langsung agar SSR-safe
        navigateTo('/')
        return Promise.reject(error)
      }
      return Promise.reject(error)
    }
  )

  return {
    provide: { api },
  }
})
