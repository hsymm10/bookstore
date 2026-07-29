<template>
  <div class="min-h-screen max-w-[100vw] bg-slate-100 flex font-sans overflow-x-hidden">

    <!-- ─── MOBILE SIDEBAR OVERLAY ──────────────────────────────────── -->
    <Transition name="fade">
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"
        @click="sidebarOpen = false"
      />
    </Transition>

    <!-- ─── SIDEBAR ──────────────────────────────────────────────────── -->
    <aside
      class="w-72 bg-gradient-to-b from-[#cbe3fc] to-[#f0f7ff] border-r border-[#b3d7ff] flex flex-col fixed h-screen z-50 transition-transform duration-300 ease-in-out"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <!-- Logo + Close Button (mobile) -->
      <div class="p-5 flex items-center justify-between border-b border-[#b3d7ff]/50">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-xl bg-[#2E5AA7] flex items-center justify-center text-white shadow-md">
            <Icon name="mdi:book-open-page-variant" class="text-xl" />
          </div>
          <span class="font-extrabold text-xl text-[#2E5AA7] tracking-tight">BookStore</span>
        </div>
        <button
          class="lg:hidden w-8 h-8 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-white/50 rounded-lg transition-colors"
          @click="sidebarOpen = false"
        >
          <Icon name="mdi:close" class="text-lg" />
        </button>
      </div>

      <!-- Search -->
      <div class="px-4 py-3">
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
            <Icon name="mdi:magnify" class="text-base" />
          </span>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari buku..."
            class="w-full pl-9 pr-4 py-2.5 bg-white/70 border border-[#b3d7ff] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#2E5AA7]/30 transition-all text-slate-700"
          />
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 space-y-1 overflow-y-auto pb-4">
        <NuxtLink
          to="/user/transaksi"
          @click="sidebarOpen = false"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all"
          :class="isRouteActive('/user/transaksi')
            ? 'bg-[#2E5AA7] text-white shadow-md shadow-[#2E5AA7]/20'
            : 'text-[#2E5AA7] hover:bg-white/60'"
        >
          <Icon name="mdi:cash-register" class="text-lg flex-shrink-0" />
          <span>Transaksi</span>
        </NuxtLink>

        <NuxtLink
          to="/user/keranjang"
          @click="sidebarOpen = false"
          class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all"
          :class="isRouteActive('/user/keranjang')
            ? 'bg-[#2E5AA7] text-white shadow-md shadow-[#2E5AA7]/20'
            : 'text-[#2E5AA7] hover:bg-white/60'"
        >
          <div class="flex items-center gap-3">
            <Icon name="mdi:cart-outline" class="text-lg flex-shrink-0" />
            <span>Keranjang</span>
          </div>
          <span
            v-if="cartStore.totalItems > 0"
            class="min-w-[22px] h-[22px] px-1 rounded-full text-xs font-black flex items-center justify-center transition-colors"
            :class="isRouteActive('/user/keranjang') ? 'bg-white text-[#2E5AA7]' : 'bg-[#2E5AA7] text-white'"
          >
            {{ cartStore.totalItems }}
          </span>
        </NuxtLink>

        <NuxtLink
          to="/user/riwayat"
          @click="sidebarOpen = false"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all"
          :class="isRouteActive('/user/riwayat')
            ? 'bg-[#2E5AA7] text-white shadow-md shadow-[#2E5AA7]/20'
            : 'text-[#2E5AA7] hover:bg-white/60'"
        >
          <Icon name="mdi:history" class="text-lg flex-shrink-0" />
          <span>Riwayat Pesanan</span>
        </NuxtLink>

        <div class="h-px bg-[#b3d7ff]/70 my-3" />

        <NuxtLink
          to="/user/chat"
          @click="sidebarOpen = false"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all"
          :class="isRouteActive('/user/chat')
            ? 'bg-[#2E5AA7] text-white shadow-md shadow-[#2E5AA7]/20'
            : 'text-[#2E5AA7] hover:bg-white/60'"
        >
          <Icon name="mdi:chat-processing-outline" class="text-lg flex-shrink-0" />
          <span>Chat</span>
        </NuxtLink>

        <button
          @click="handleLogout"
          class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-red-600 hover:bg-red-50/60 transition-all text-left"
        >
          <Icon name="mdi:logout" class="text-lg flex-shrink-0" />
          <span>Logout</span>
        </button>
      </nav>
    </aside>

    <!-- ─── MAIN CONTENT ──────────────────────────────────────────────── -->
    <div class="flex-1 flex flex-col min-h-screen min-w-0 lg:ml-72 transition-all duration-300 overflow-x-hidden">

      <!-- Topbar Header -->
      <header class="h-16 lg:h-20 bg-slate-100 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-30 border-b border-slate-200/60">
        <div class="flex items-center gap-3">
          <!-- Hamburger (mobile only) -->
          <button
            class="lg:hidden w-10 h-10 flex items-center justify-center text-[#2E5AA7] hover:bg-[#2E5AA7]/10 rounded-xl transition-colors"
            @click="sidebarOpen = true"
          >
            <Icon name="mdi:menu" class="text-2xl" />
          </button>
          <h1 class="text-lg lg:text-2xl font-black text-[#2E5AA7] tracking-tight uppercase border-b-2 border-[#2E5AA7] pb-0.5 truncate max-w-[160px] sm:max-w-none">
            {{ pageTitle }}
          </h1>
        </div>

        <!-- User Profile -->
        <div class="flex items-center gap-2 lg:gap-4">
          <div class="hidden sm:block text-right">
            <p class="text-sm font-black text-slate-800 leading-none truncate max-w-[120px]">
              {{ authStore.currentUser?.nama || 'User' }}
            </p>
            <p class="text-xs text-slate-500 font-semibold mt-0.5">
              {{ authStore.currentUser?.role || 'User' }}
            </p>
          </div>
          <div class="w-10 h-10 lg:w-11 lg:h-11 rounded-full bg-gradient-to-tr from-[#FFA62B] to-[#2E5AA7] border-2 border-white shadow-md flex items-center justify-center text-white font-extrabold text-sm overflow-hidden flex-shrink-0">
            <img
              v-if="authStore.currentUser?.avatar_url"
              :src="authStore.currentUser.avatar_url"
              class="w-full h-full object-cover"
            />
            <span v-else>{{ authStore.currentUser?.nama?.charAt(0).toUpperCase() || 'U' }}</span>
          </div>
        </div>
      </header>

      <!-- Page Slot -->
      <main class="flex-1 p-4 lg:p-8 flex flex-col min-h-0">
        <div class="flex-1 bg-white border border-[#d2e4ff] rounded-2xl lg:rounded-3xl p-4 lg:p-8 shadow-sm flex flex-col min-h-0 overflow-hidden">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()
const api = useApi()

const searchQuery = ref('')
const sidebarOpen = ref(false)

// Close sidebar on route change (mobile nav)
watch(() => route.path, () => {
  sidebarOpen.value = false
})

const pageTitle = computed(() => {
  if (route.path.includes('/transaksi')) return 'Transaksi'
  if (route.path.includes('/keranjang')) return 'Keranjang'
  if (route.path.includes('/riwayat')) return 'Riwayat Pesanan'
  if (route.path.includes('/chat')) return 'Chat'
  return 'Dashboard'
})

const isRouteActive = (path: string) => route.path === path

const handleLogout = async () => {
  try {
    await api.post('/auth/logout')
  } catch (e) {
    console.error(e)
  } finally {
    authStore.clearAuth()
    router.push('/')
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
