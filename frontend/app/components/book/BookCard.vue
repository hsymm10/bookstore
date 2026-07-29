<template>
  <NuxtLink :to="`/books/${book.id}`"
    class="card group cursor-pointer hover:shadow-card-hover hover:-translate-y-1.5 transition-all duration-300">

    <!-- Cover Image -->
    <div class="relative overflow-hidden bg-gradient-to-br from-primary-50 to-purple-50 aspect-[3/4]">
      <img v-if="book.gambar_url" :src="book.gambar_url" :alt="book.nama_buku"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
      <div v-else class="w-full h-full flex flex-col items-center justify-center">
        <span class="text-6xl"><Icon name="mdi:book-open-page-variant" class="text-6xl text-slate-300" /></span>
        <span class="text-xs text-primary-400 mt-2 font-medium">No Cover</span>
      </div>

      <!-- Category Badge -->
      <div class="absolute top-2 left-2">
        <span class="badge badge-primary bg-white/90 backdrop-blur text-primary-700 shadow-sm text-[10px]">
          {{ book.category?.nama }}
        </span>
      </div>

      <!-- Stok Badge -->
      <div v-if="book.stok === 0" class="absolute inset-0 bg-black/50 flex items-center justify-center">
        <span class="badge bg-red-500 text-white text-sm font-bold">Habis</span>
      </div>
    </div>

    <!-- Info -->
    <div class="p-4">
      <h3 class="font-bold text-slate-800 text-sm leading-snug line-clamp-2 group-hover:text-primary-600 transition-colors mb-2">
        {{ book.nama_buku }}
      </h3>
      <div class="flex items-center justify-between mt-3">
        <span class="text-primary-600 font-black text-base">
          {{ formatRupiah(book.harga_jual) }}
        </span>
        <span class="text-xs text-slate-400">Stok: {{ book.stok }}</span>
      </div>

      <!-- Add to Cart Button -->
      <button v-if="authStore.isLoggedIn && authStore.isUser"
        @click.prevent="addToCart"
        :disabled="book.stok === 0"
        class="w-full mt-3 py-2 text-xs font-semibold rounded-xl transition-all duration-200 flex items-center justify-center gap-1.5"
        :class="book.stok === 0
          ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
          : 'bg-primary-50 text-primary-600 hover:bg-primary-600 hover:text-white'">
        <Icon name="mdi:cart-outline" class="w-4 h-4" />
        {{ book.stok === 0 ? 'Habis' : 'Tambah Keranjang' }}
      </button>
    </div>
  </NuxtLink>
</template>

<script setup lang="ts">

const props = defineProps<{
  book: {
    id: number
    nama_buku: string
    harga_jual: number
    gambar_url: string | null
    stok: number
    category?: { nama: string }
  }
}>()

const authStore = useAuthStore()
const cartStore = useCartStore()
const { $toast } = useNuxtApp() as any

const addToCart = () => {
  const ok = cartStore.addToCart({
    id:         props.book.id,
    nama_buku:  props.book.nama_buku,
    harga_jual: props.book.harga_jual,
    gambar_url: props.book.gambar_url,
    stok:       props.book.stok,
  })

  if (ok) {
    $toast.fire({ icon: 'success', title: 'Buku ditambahkan ke keranjang!' })
  } else {
    $toast.fire({ icon: 'error', title: 'Stok tidak mencukupi!' })
  }
}
</script>
