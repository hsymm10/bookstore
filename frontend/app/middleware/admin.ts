/**
 * middleware/admin.ts
 *
 * Melindungi semua rute /admin/* — hanya role 'admin' yang boleh masuk.
 * Berjalan client-side saja (ssr: false di nuxt.config.ts).
 *
 * ⚠️ PENTING: middleware ini hanya MEMBLOKIR akses yang tidak berhak.
 * Jangan tambahkan redirect yang bisa memaksa admin/user keluar dari
 * halaman yang sedang mereka akses saat F5.
 */
export default defineNuxtRouteMiddleware(() => {
  if (import.meta.server) return

  const authStore = useAuthStore()

  // ⚠️ restoreFromCookie() sudah dipanggil oleh plugin/auth.ts sebelum middleware ini berjalan.

  // Belum login → ke halaman login
  if (!authStore.isLoggedIn) {
    return navigateTo('/login')
  }

  // Login tapi bukan admin → ke halaman user
  if (!authStore.isAdmin) {
    return navigateTo('/user/transaksi')
  }
})

