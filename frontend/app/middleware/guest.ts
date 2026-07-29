/**
 * middleware/guest.ts
 *
 * Hanya untuk tamu (belum login). Jika sudah login, redirect ke halaman yang sesuai.
 * Berjalan client-side saja (ssr: false di nuxt.config.ts).
 */
export default defineNuxtRouteMiddleware(() => {
  if (import.meta.server) return

  const authStore = useAuthStore()

  // ⚠️ restoreFromCookie() sudah dipanggil oleh plugin/auth.ts sebelum middleware ini berjalan.

  if (authStore.isLoggedIn) {
    return navigateTo(authStore.isAdmin ? '/admin/dashboard' : '/user/transaksi')
  }
})

