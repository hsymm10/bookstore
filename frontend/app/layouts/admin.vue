<template>
  <!-- Admin Layout: Sidebar + Main Content -->
  <div class="min-h-screen bg-slate-50 flex">

    <!-- Sidebar -->
    <TheSidebar />

    <!-- Main Area -->
    <div class="flex-1 flex flex-col min-w-0 ml-64">

      <!-- Top Header -->
      <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-6 sticky top-0 z-30 shadow-sm">
        <div class="flex items-center gap-3">
          <h1 class="text-lg font-semibold text-slate-700">
            {{ pageTitle }}
          </h1>
        </div>

        <!-- Admin Info -->
        <div class="flex items-center gap-4">
          <!-- Notification Bell -->
          <NuxtLink to="/admin/chat"
            class="relative p-2 rounded-xl hover:bg-primary-50 text-slate-500 hover:text-primary-600 transition-colors">
            <BellIcon class="w-5 h-5" />
            <span v-if="unreadCount > 0"
              class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </NuxtLink>

          <!-- User Avatar & Name -->
          <div class="flex items-center gap-3 cursor-pointer group relative">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
              {{ authStore.currentUser?.nama?.charAt(0)?.toUpperCase() ?? 'A' }}
            </div>
            <div class="hidden sm:block">
              <p class="text-sm font-semibold text-slate-700 leading-none">{{ authStore.currentUser?.nama }}</p>
              <p class="text-xs text-slate-400 mt-0.5 capitalize">{{ authStore.currentUser?.role }}</p>
            </div>

            <!-- Dropdown -->
            <div class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-glass border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
              <div class="p-2">
                <button @click="logout"
                  class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                  <ArrowRightOnRectangleIcon class="w-4 h-4" />
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-6 overflow-y-auto">
        <div class="animate-fade-in">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { BellIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'

defineOptions({ name: 'AdminLayout' })

const authStore = useAuthStore()
const api       = useApi()
const route     = useRoute()
const router    = useRouter()

// Unread chat count
const unreadCount = ref(0)

const fetchUnread = async () => {
  try {
    const res = await api.get('/admin/chats/unread-count')
    unreadCount.value = res.data.data.count
  } catch {}
}

// Poll unread setiap 10 detik
onMounted(() => {
  fetchUnread()
  const interval = setInterval(fetchUnread, 10000)
  onUnmounted(() => clearInterval(interval))
})

// Page title dari route meta
const pageTitle = computed(() => {
  const titles: Record<string, string> = {
    '/admin/dashboard':  'Dashboard',
    '/admin/categories': 'Manajemen Kategori',
    '/admin/books':      'Manajemen Buku',
    '/admin/users':      'Manajemen Pengguna',
    '/admin/kasir':      'Pesanan Anda',
    '/admin/reports':    'Laporan Penjualan',
    '/admin/chat':       'Live Chat',
  }
  return titles[route.path] ?? 'Admin Panel'
})

const logout = async () => {
  try {
    await api.post('/auth/logout')
  } finally {
    authStore.clearAuth()
    router.push('/')
  }
}
</script>
