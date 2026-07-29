<template>
  <div class="min-h-screen flex flex-col font-sans">

    <!-- ─── Fixed Navbar ─────────────────────────────────────────────────── -->
    <header class="fixed top-0 left-0 right-0 z-50">
      <div class="relative mx-4 mt-4">
        <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl px-5 py-3 flex items-center justify-between shadow-2xl">
          <!-- Logo -->
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-700 flex items-center justify-center shadow-md">
              <Icon name="mdi:book-open-page-variant" class="text-white text-lg" />
            </div>
            <span class="text-white font-black text-lg tracking-wider uppercase drop-shadow">BookStore</span>
          </div>

          <!-- Desktop nav links -->
          <nav class="hidden md:flex items-center gap-6 text-xs font-bold text-white/80 uppercase tracking-widest">
            <a href="#katalog" class="hover:text-white transition-colors">Katalog</a>
            <a href="#tentang" class="hover:text-white transition-colors">Tentang</a>
          </nav>

          <!-- Login / Daftar buttons (guest) or Dashboard (logged in) -->
          <div class="flex items-center gap-2">
            <template v-if="!authStore.isLoggedIn">
              <NuxtLink
                to="/login"
                class="flex items-center gap-1.5 px-3.5 py-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-xl text-[11px] font-black uppercase tracking-wider transition-all duration-200 active:scale-95"
              >
                <Icon name="mdi:login" class="text-sm" />
                <span>Login</span>
              </NuxtLink>
              <NuxtLink
                to="/register"
                class="flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-xl text-[11px] font-black uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-md shadow-blue-900/40"
              >
                <Icon name="mdi:account-plus" class="text-sm" />
                <span>Daftar</span>
              </NuxtLink>
            </template>
            <template v-else>
              <NuxtLink
                :to="dashboardLink"
                class="flex items-center gap-1.5 px-4 py-2 bg-white/20 hover:bg-white/30 border border-white/30 text-white rounded-xl text-[11px] font-black uppercase tracking-wider transition-all duration-200 active:scale-95"
              >
                <Icon name="mdi:view-dashboard" class="text-sm" />
                <span class="hidden sm:inline">Dashboard</span>
              </NuxtLink>
            </template>
          </div>
        </div>
      </div>
    </header>

    <!-- ─── Hero: Full-bleed gambar1.png ────────────────────────────────── -->
    <section class="relative h-screen min-h-[600px] flex items-end overflow-hidden">
      <!-- Background image, full bleed, no frame, no crop borders -->
      <img
        src="/gambar1.png"
        class="absolute inset-0 w-full h-full object-cover object-center"
        alt="BookStore Landing"
        fetchpriority="high"
      />

      <!-- Gradient overlay on bottom for text readability -->
      <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/40 to-transparent"></div>

      <!-- Hero Text Content (over image at bottom) -->
      <div class="relative z-10 w-full max-w-7xl mx-auto px-6 sm:px-8 pb-16 sm:pb-20">
        <div class="max-w-2xl">
          <span class="inline-block px-3.5 py-1 bg-blue-500/20 border border-blue-400/30 text-blue-300 text-[10px] font-black rounded-full uppercase tracking-[0.2em] mb-4 backdrop-blur-sm">
            ✦ Selamat Datang di BookStore
          </span>
          <h1 class="text-4xl sm:text-5xl md:text-6xl font-black text-white leading-none tracking-tight uppercase mb-4">
            Temukan <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300">Dunia</span><br />
            Dalam Sebuah<br />
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300">Buku</span>
          </h1>
          <p class="text-slate-300 text-sm sm:text-base font-medium leading-relaxed mb-8 max-w-lg">
            Jelajahi ribuan buku digital berkualitas dari berbagai genre. Nikmati kemudahan transaksi instan dan struk digital berstandar modern.
          </p>
          <div class="flex flex-wrap gap-4">
            <a
              href="#katalog"
              class="px-7 py-3.5 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300 shadow-xl shadow-blue-900/40 active:scale-95 flex items-center gap-2"
            >
              <Icon name="mdi:book-search" class="text-base" />
              Jelajahi Buku
            </a>
         
          </div>
        </div>
      </div>

      <!-- Scroll down indicator -->
      <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-10 flex flex-col items-center gap-1 animate-bounce">
        <span class="text-[10px] text-white/50 font-bold uppercase tracking-widest">Scroll</span>
        <Icon name="mdi:chevron-down" class="text-white/40 text-xl" />
      </div>
    </section>

    <!-- ─── Stats Strip ───────────────────────────────────────────────────── -->
    <section id="tentang" class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 border-y border-white/5">
      <div class="max-w-7xl mx-auto px-6 sm:px-8 py-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
          <div class="space-y-1">
            <p class="text-2xl md:text-3xl font-black text-white">{{ stats.books }}+</p>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Judul Buku</p>
          </div>
          <div class="space-y-1">
            <p class="text-2xl md:text-3xl font-black text-white">{{ stats.users }}+</p>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pelanggan</p>
          </div>
          <div class="space-y-1">
            <p class="text-2xl md:text-3xl font-black text-white">{{ stats.categories }}+</p>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kategori</p>
          </div>
          <div class="space-y-1">
            <p class="text-2xl md:text-3xl font-black text-white">{{ stats.satisfaction }}%</p>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kepuasan</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── Catalog Section ────────────────────────────────────────────────── -->
    <section id="katalog" class="bg-[#0d1117] py-20">
      <div class="max-w-7xl mx-auto px-6 sm:px-8">

        <!-- Section header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
          <div>
            <p class="text-blue-500 text-[10px] font-black uppercase tracking-[0.2em] mb-2">✦ Koleksi Terbaru</p>
            <h2 class="text-2xl sm:text-3xl font-black text-white uppercase tracking-wide">Katalog Buku</h2>
          </div>
          <NuxtLink :to="dashboardLink" class="text-xs font-black text-blue-400 hover:text-blue-300 uppercase tracking-wider flex items-center gap-1.5 transition-colors shrink-0">
            Lihat Semua
            <Icon name="mdi:arrow-right" />
          </NuxtLink>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center items-center py-24">
          <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="books.length === 0" class="flex flex-col items-center py-20 text-center">
          <Icon name="mdi:book-off-outline" class="text-5xl text-slate-600 mb-3" />
          <p class="text-slate-400 font-semibold">Belum ada buku tersedia.</p>
        </div>

        <!-- Grid -->
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
          <div
            v-for="book in books"
            :key="book.id"
            class="group bg-white/5 hover:bg-white/10 border border-white/10 hover:border-blue-500/40 rounded-2xl overflow-hidden transition-all duration-300 cursor-pointer flex flex-col"
            @click="handleOrderClick(book)"
          >
            <!-- Cover -->
            <div class="relative aspect-[3/4] overflow-hidden bg-slate-800">
              <img
                v-if="book.gambar_url"
                :src="book.gambar_url"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                :alt="book.nama_buku"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <Icon name="mdi:book-open-page-variant" class="text-5xl text-slate-600" />
              </div>
              <!-- Stock badge -->
              <div class="absolute top-2 right-2">
                <span
                  class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider"
                  :class="book.stok > 0 ? 'bg-emerald-500/90 text-white' : 'bg-rose-600/90 text-white'"
                >
                  {{ book.stok > 0 ? 'Tersedia' : 'Habis' }}
                </span>
              </div>
            </div>

            <!-- Info -->
            <div class="p-4 flex flex-col flex-1 gap-2">
              <div>
                <p class="text-[10px] text-blue-400 font-bold uppercase tracking-wider truncate">{{ book.category?.nama || 'Umum' }}</p>
                <p class="text-sm font-extrabold text-white leading-tight mt-0.5 line-clamp-2">{{ book.nama_buku }}</p>
              </div>
              <div class="mt-auto pt-2 border-t border-white/10 flex items-center justify-between">
                <span class="text-sm font-black text-blue-300">{{ formatRupiah(book.harga_jual) }}</span>
                <button
                  :disabled="book.stok <= 0"
                  class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all duration-200 active:scale-95"
                  :class="book.stok > 0
                    ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-900/40'
                    : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                >
                  Order
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- CTA -->
        <div class="mt-14 text-center">
          <p class="text-slate-500 text-sm font-semibold mb-4">Siap untuk berbelanja lebih banyak?</p>
          <NuxtLink
            :to="dashboardLink"
            class="inline-flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-xl shadow-blue-900/30 active:scale-95"
          >
            <Icon :name="authStore.isLoggedIn ? 'mdi:shopping' : 'mdi:login'" />
            {{ authStore.isLoggedIn ? 'Masuk ke Toko' : 'Login untuk Berbelanja' }}
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- ─── Footer ────────────────────────────────────────────────────────── -->
    <footer class="bg-[#080c11] border-t border-white/5 py-10 mt-auto">
      <div class="max-w-7xl mx-auto px-6 sm:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
          <!-- Logo -->
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-700 flex items-center justify-center">
              <Icon name="mdi:book-open-page-variant" class="text-white text-sm" />
            </div>
            <span class="text-white font-black text-base tracking-wider uppercase">BookStore</span>
          </div>

         
        </div>

        <div class="border-t border-white/5 mt-8 pt-6 text-center">
          <p class="text-slate-600 text-xs">&copy; {{ new Date().getFullYear() }} BookStore — All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false
})

const authStore = useAuthStore()
const api = useApi()
const router = useRouter()
const { $toast } = useNuxtApp() as any

authStore.restoreFromCookie()

const books = ref<any[]>([])
const loading = ref(true)

const stats = ref({
  books: 500,
  users: 10000,
  categories: 20,
  satisfaction: 99
})

const dashboardLink = computed(() => {
  if (!authStore.isLoggedIn) return '/login'
  return authStore.isAdmin ? '/admin/dashboard' : '/user/transaksi'
})

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(val ?? 0)
}

const fetchBooks = async () => {
  loading.value = true
  try {
    const res = await api.get('/books', { params: { per_page: 8 } })
    books.value = res.data.data.data
  } catch (error) {
    console.error('Gagal memuat buku:', error)
  } finally {
    loading.value = false
  }
}

const handleOrderClick = (book: any) => {
  if (!authStore.isLoggedIn) {
    $toast.fire({
      icon: 'info',
      title: 'Silakan login terlebih dahulu untuk memesan buku.'
    })
    router.push('/login')
  } else {
    router.push('/user/transaksi')
  }
}

const fetchStats = async () => {
  try {
    const res = await api.get('/public-stats')
    stats.value = res.data.data
  } catch (error) {
    console.error('Gagal memuat statistik:', error)
  }
}

onMounted(() => {
  fetchBooks()
  fetchStats()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
html {
  scroll-behavior: smooth;
}
</style>
