<template>
  <!-- Admin Sidebar: fixed full height, light gradient biru-cyan lembut -->
  <aside class="fixed inset-y-0 left-0 h-screen w-64 border-r border-slate-100 z-40 flex flex-col"
    style="background: linear-gradient(180deg, #cbe2ff 0%, #a5cfff 100%);">

    <!-- Logo Brand -->
    <div class="h-16 flex items-center gap-3 px-6 border-b border-blue-900/10">
      <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-600 to-purple-600 flex items-center justify-center shadow-md">
        <Icon name="mdi:book-open-page-variant" class="text-white text-lg" />
      </div>
      <div>
        <h1 class="text-slate-900 font-black text-lg tracking-tight leading-none">BookStore</h1>
        <p class="text-slate-600 text-[10px] font-bold uppercase tracking-wider mt-0.5">Admin Panel</p>
      </div>
    </div>

    <!-- Search Input in Sidebar -->
    <div class="px-4 py-3">
      <div class="relative">
        <input type="text" placeholder="Search"
          class="w-full pl-3 pr-3 py-2 text-xs rounded-lg border-0 bg-white/60 focus:bg-white text-slate-800 placeholder-slate-500 focus:outline-none shadow-sm" />
      </div>
    </div>

    <!-- Navigation & Bottom Logout -->
    <div class="flex-1 flex flex-col justify-between overflow-y-auto px-3 py-2 scrollbar-hide">
      <!-- Main Menu -->
      <nav class="space-y-1">
        <NuxtLink v-for="item in menuItems" :key="item.href" :to="item.href"
          class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm text-slate-700 hover:bg-white/20 hover:text-slate-900 active:scale-95 transition-all duration-200"
          :class="{ '!bg-white/50 !text-primary-700 shadow-sm': isActive(item.href) }">
          <Icon :name="item.icon" class="text-lg flex-shrink-0" />
          <span>{{ item.label }}</span>
        </NuxtLink>
      </nav>

      <!-- Logout Button (pinned at bottom) -->
      <div class="pt-4 pb-2">
        <div class="h-px bg-blue-900/10 mb-3"></div>
        <button @click="logout"
          class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-bold text-sm text-red-700 hover:bg-red-50/40 hover:text-red-950 active:scale-95 transition-all duration-200">
          <Icon name="mdi:logout" class="text-lg flex-shrink-0" />
          <span>Logout</span>
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
const authStore = useAuthStore()
const api       = useApi()
const router    = useRouter()
const route     = useRoute()

// Updated list of items to match screenshots exactly
const menuItems = [
  { href: '/admin/dashboard',  label: 'Dashboard',   icon: 'mdi:view-dashboard-outline' },
  { href: '/admin/categories', label: 'Kategori',    icon: 'mdi:shape-outline' },
  { href: '/admin/books',      label: 'Book',        icon: 'mdi:book-open-page-variant-outline' },
  { href: '/admin/users',      label: 'Pengguna',    icon: 'mdi:account-group-outline' },
  { href: '/admin/kasir',      label: 'Pesanan Anda', icon: 'mdi:cart-outline' },
  { href: '/admin/reports',    label: 'Laporan',     icon: 'mdi:file-document-outline' },
  { href: '/admin/chat',       label: 'Chat',        icon: 'mdi:chat-processing-outline' },
]

const isActive = (href: string) => {
  if (href === '/admin/dashboard') {
    return route.path === href
  }
  return route.path.startsWith(href)
}

const logout = async () => {
  try {
    await api.post('/auth/logout')
  } finally {
    authStore.clearAuth()
    router.push('/')
  }
}
</script>

