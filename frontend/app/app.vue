<template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
/**
 * app.vue — Root component
 *
 * SSR dimatikan di nuxt.config.ts (ssr: false), sehingga kode ini
 * hanya berjalan di browser (client-side). Pinia store aman diakses.
 *
 * Urutan eksekusi saat F5 / reload:
 * 1. Browser memuat HTML shell (kosong karena SPA)
 * 2. Vue/Nuxt boot di client
 * 3. Script setup berjalan → restoreFromCookie() SINKRON → Pinia state terisi
 * 4. Route middleware berjalan → authStore sudah berisi data yang benar
 * 5. Halaman yang benar di-render berdasarkan role dari cookie
 *
 * ⚠️ PENTING: restoreFromCookie() HARUS dipanggil di level script setup (sinkron),
 * BUKAN di onMounted (async). Middleware Nuxt berjalan sebelum onMounted,
 * sehingga jika restore hanya di onMounted, store masih kosong saat middleware
 * evaluasi route → menyebabkan redirect paksa ke halaman lain saat F5.
 */
const authStore = useAuthStore()
const api = useApi()

// ── SINKRON: Restore state dari cookie SEBELUM middleware berjalan ──────────
authStore.restoreFromCookie()

// ── Heartbeat interval reference ────────────────────────────────────────────
let heartbeatInterval: ReturnType<typeof setInterval> | null = null

const startHeartbeat = () => {
  // Send heartbeat immediately then every 30 seconds
  api.post('/auth/heartbeat').catch(() => {})
  heartbeatInterval = setInterval(() => {
    if (authStore.isLoggedIn) {
      api.post('/auth/heartbeat').catch(() => {})
    } else {
      stopHeartbeat()
    }
  }, 30000)
}

const stopHeartbeat = () => {
  if (heartbeatInterval) {
    clearInterval(heartbeatInterval)
    heartbeatInterval = null
  }
}

// ── Mark offline when browser/tab is closed ──────────────────────────────────
const handleBeforeUnload = () => {
  if (authStore.isLoggedIn && authStore.token) {
    // Use fetch with keepalive — allows headers, works on tab close
    const apiBase = useRuntimeConfig().public.apiBase as string
    fetch(`${apiBase}/auth/heartbeat`, {
      method: 'POST',
      keepalive: true,
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    }).catch(() => {})
  }
}

// ── ASYNC: Verifikasi token ke backend di background ─────────────────────────
onMounted(() => {
  if (authStore.isLoggedIn) {
    // Jalankan di background — jangan await agar tidak delay render/routing
    authStore.fetchProfile().catch(() => {
      // fetchProfile sudah handle 401 sendiri
    })
    startHeartbeat()
  }

  // Listen for storage events (cross-tab logout sync)
  window.addEventListener('storage', (e) => {
    if (e.key === 'auth_token' && !e.newValue) {
      stopHeartbeat()
    }
  })

  window.addEventListener('beforeunload', handleBeforeUnload)
})

onUnmounted(() => {
  stopHeartbeat()
  window.removeEventListener('beforeunload', handleBeforeUnload)
})

// Watch auth state to start/stop heartbeat dynamically
watch(() => authStore.isLoggedIn, (isLoggedIn) => {
  if (isLoggedIn) {
    startHeartbeat()
  } else {
    stopHeartbeat()
  }
})
</script>
