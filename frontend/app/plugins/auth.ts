/**
 * plugins/auth.ts
 *
 * Plugin yang berjalan paling awal di Nuxt lifecycle — SEBELUM middleware
 * dan SEBELUM halaman apapun di-render.
 *
 * Di SPA mode (ssr: false), urutan eksekusinya saat F5 adalah:
 *   1. Nuxt boot di browser
 *   2. Plugin berjalan (SINKRON) ← file ini
 *   3. Route middleware berjalan → store sudah terisi dari cookie
 *   4. Halaman di-render
 *
 * Dengan plugin ini, Pinia auth state SELALU terisi dengan benar
 * sebelum middleware (auth.ts, admin.ts) melakukan evaluasi rute.
 * Ini mencegah redirect paksa ke halaman lain saat F5 / hard refresh.
 */
export default defineNuxtPlugin(() => {
  // Hanya jalan di browser (SPA mode)
  if (import.meta.server) return

  const authStore = useAuthStore()

  // Restore auth state dari browser cookie secara SINKRON
  authStore.restoreFromCookie()
})
