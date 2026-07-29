import { defineStore } from 'pinia'

export interface AuthUser {
  id: number
  role: string
  nama: string
  username: string
  email: string
  no_hp: string
  created_at: string
}

interface AuthState {
  user:  AuthUser | null
  token: string | null
}

// Cookie options — 7 hari
const COOKIE_OPTS = { path: '/', maxAge: 60 * 60 * 24 * 7 }

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user:  null,
    token: null,
  }),

  getters: {
    isLoggedIn:  (state): boolean         => !!state.token && !!state.user,
    isAdmin:     (state): boolean         => state.user?.role === 'admin',
    isUser:      (state): boolean         => state.user?.role === 'user',
    currentUser: (state): AuthUser | null => state.user,
  },

  actions: {
    /**
     * Simpan user dan token ke Pinia store dan cookie browser.
     *
     * ⚠️ PENTING: Simpan user sebagai AuthUser (objek) bukan JSON.stringify(user).
     * Nuxt useCookie secara internal melakukan serialisasi dengan `destr` yang
     * lebih toleran. Jika kita memanggil JSON.stringify secara manual, Nuxt akan
     * stringify lagi (double encoding) sehingga cookie menjadi string berlapis
     * yang tidak bisa di-parse saat restore.
     */
    setAuth(user: AuthUser, token: string) {
      this.user  = user
      this.token = token

      // Simpan token sebagai string biasa
      const tokenCookie = useCookie<string | null>('auth_token', COOKIE_OPTS)
      // Simpan user sebagai objek — Nuxt menangani JSON serialisasi otomatis
      const userCookie  = useCookie<AuthUser | null>('auth_user', COOKIE_OPTS)

      tokenCookie.value = token
      userCookie.value  = user
    },

    /**
     * Hapus semua data autentikasi dari store dan cookie.
     */
    clearAuth() {
      this.user  = null
      this.token = null

      const tokenCookie = useCookie<string | null>('auth_token', { path: '/' })
      const userCookie  = useCookie<AuthUser | null>('auth_user', { path: '/' })
      tokenCookie.value = null
      userCookie.value  = null
    },

    /**
     * Restore state autentikasi dari cookie browser.
     *
     * Dipanggil dari:
     * - Setiap middleware (sebelum route evaluation)
     * - app.vue onMounted (setelah browser boot)
     *
     * Aman dipanggil berulang kali (idempoten).
     */
    restoreFromCookie() {
      const tokenCookie = useCookie<string | null>('auth_token', { path: '/' })
      const userCookie  = useCookie<AuthUser | string | null>('auth_user', { path: '/' })

      const rawToken = tokenCookie.value
      const rawUser  = userCookie.value

      if (!rawToken || !rawUser) {
        this.user  = null
        this.token = null
        return
      }

      this.token = rawToken

      // Handle dua kasus:
      // 1. rawUser adalah objek (normal — Nuxt deserialisasi otomatis)
      // 2. rawUser adalah string (jika cookie tersimpan dari versi lama yang double-stringify)
      if (typeof rawUser === 'string') {
        try {
          const parsed = JSON.parse(rawUser) as AuthUser
          if (parsed?.id && parsed?.role) {
            this.user = parsed
          } else {
            this.clearAuth()
          }
        } catch {
          this.clearAuth()
        }
      } else if (rawUser && typeof rawUser === 'object') {
        const userObj = rawUser as AuthUser
        if (userObj.id && userObj.role) {
          this.user = userObj
        } else {
          this.clearAuth()
        }
      } else {
        this.clearAuth()
      }
    },

    /**
     * Verifikasi token dan perbarui data profil langsung dari database Laravel.
     * Dipanggil sekali saat app boot (onMounted di app.vue).
     */
    async fetchProfile() {
      if (!this.token) return

      try {
        const { $api } = useNuxtApp() as any
        const res = await $api.get('/auth/profile')
        const profileData = res.data?.data

        if (profileData && profileData.id && profileData.role) {
          this.user = profileData
          // Perbarui cookie dengan data terbaru dari database
          const userCookie = useCookie<AuthUser | null>('auth_user', COOKIE_OPTS)
          userCookie.value = profileData
        }
      } catch (err: any) {
        // Token tidak valid/kadaluarsa → hapus semua session
        if (err.response?.status === 401) {
          this.clearAuth()
        }
        // Error lain (network, dll) — jangan paksa logout
      }
    },
  },
})
