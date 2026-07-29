/**
 * middleware/auth.ts
 *
 * Melindungi rute yang memerlukan login.
 * Karena SSR dimatikan, middleware ini selalu berjalan di browser (client-side).
 *
 * PENTING: Middleware ini hanya memblokir akses ke rute yang TIDAK boleh diakses.
 * Middleware TIDAK boleh me-redirect jika user sudah berada di halaman yang sesuai
 * rolnya, karena ini menyebabkan redirect paksa saat F5 / hard refresh.
 */
export default defineNuxtRouteMiddleware((to) => {
  if (import.meta.server) return

  const authStore = useAuthStore()

  // ⚠️ restoreFromCookie() sudah dipanggil oleh plugin/auth.ts sebelum middleware ini berjalan.
  // Tidak perlu dipanggil lagi di sini — biarkan plugin yang bertanggung jawab.

  // Belum login → ke halaman login
  if (!authStore.isLoggedIn) {
    return navigateTo('/login')
  }

  // ─── ROLE-BASED ACCESS CONTROL ───────────────────────────────────────
  // Hanya redirect jika user mencoba mengakses rute yang BUKAN haknya.
  // Jika user sudah berada di rute yang sesuai rolnya → JANGAN redirect (biarkan load).

  if (authStore.isAdmin) {
    // Admin tidak boleh akses /user/* atau /chat (non-admin)
    if (to.path.startsWith('/user/') || to.path === '/user') {
      return navigateTo('/admin/dashboard')
    }
    if (to.path === '/chat' || (to.path.startsWith('/chat/') && !to.path.startsWith('/admin'))) {
      return navigateTo('/admin/chat')
    }
  }

  if (authStore.isUser) {
    // User biasa tidak boleh akses /admin/*
    if (to.path.startsWith('/admin/') || to.path === '/admin') {
      return navigateTo('/user/transaksi')
    }
  }
})

