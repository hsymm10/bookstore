<template>
  <div class="pt-24 pb-20 bg-slate-50 min-h-screen">
    <div class="section-container">
      
      <h1 class="text-3xl font-black text-slate-800 mb-2">Riwayat Pesanan Anda</h1>
      <p class="text-slate-500 mb-8">Pantau status pesanan buku dan unduh invoice resmi Anda</p>

      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="card p-6 animate-pulse space-y-3">
          <div class="h-6 bg-slate-200 rounded w-1/4" />
          <div class="h-4 bg-slate-200 rounded w-1/3" />
          <div class="h-12 bg-slate-200 rounded" />
        </div>
      </div>

      <div v-else-if="orders.length === 0" class="card p-12 text-center text-slate-500 max-w-lg mx-auto">
        <span class="text-6xl">📦</span>
        <h3 class="font-bold text-lg text-slate-800 mt-4">Belum Ada Pesanan</h3>
        <p class="text-sm mt-1">Anda belum membuat transaksi pemesanan apapun.</p>
        <NuxtLink to="/books" class="btn-primary mt-6">Mulai Belanja</NuxtLink>
      </div>

      <div v-else class="space-y-6 max-w-4xl mx-auto">
        <div v-for="order in orders" :key="order.id" class="card overflow-hidden hover:shadow-md transition-shadow">
          <!-- Order Header -->
          <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex flex-wrap justify-between items-center gap-3">
            <div class="flex items-center gap-3">
              <span class="font-mono font-black text-lg text-primary-600">#{{ order.kode_pesanan }}</span>
              <span :class="statusColor(order.status)" class="badge capitalize">{{ order.status }}</span>
            </div>
            <div class="text-xs text-slate-400 font-semibold">{{ order.created_at }}</div>
          </div>

          <!-- Order Details/Items -->
          <div class="p-6 space-y-4">
            <div class="space-y-3">
              <div v-for="detail in order.orderDetails" :key="detail.id" class="flex items-center gap-4">
                <div class="w-10 h-14 bg-slate-100 rounded border border-slate-100 overflow-hidden flex-shrink-0">
                  <img v-if="detail.book?.gambar_url" :src="detail.book.gambar_url" :alt="detail.book.nama_buku" class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full flex items-center justify-center text-xs" style="background:#f0f7ff">
                    <Icon name="mdi:book-open-page-variant" class="text-xs" style="color:#2E5AA7" />
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="text-sm font-bold text-slate-700 truncate">{{ detail.book?.nama_buku || 'Buku Terhapus' }}</h4>
                  <p class="text-xs text-slate-400 mt-0.5">{{ detail.qty }} x {{ formatRupiah(detail.harga) }}</p>
                </div>
                <div class="text-sm font-bold text-slate-700">{{ formatRupiah(detail.subtotal) }}</div>
              </div>
            </div>

            <!-- Footer Summary -->
            <div class="pt-4 border-t border-slate-100 flex flex-wrap justify-between items-center gap-4">
              <div>
                <p class="text-xs text-slate-400">Total Pembayaran</p>
                <p class="text-xl font-black text-primary-700 mt-0.5">{{ formatRupiah(order.total) }}</p>
              </div>

              <!-- Actions -->
              <div class="flex gap-2">
                <!-- PDF Download -->
                <button v-if="order.status === 'selesai'" @click="downloadInvoice(order)" :disabled="downloading[order.id]"
                  class="btn-secondary !py-2 !px-4 !text-xs flex items-center gap-1">
                  <span v-if="downloading[order.id]" class="w-3.5 h-3.5 border-2 border-primary-600 border-t-transparent rounded-full animate-spin"></span>
                  <DocumentArrowDownIcon v-else class="w-4 h-4" />
                  Unduh Invoice
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="flex justify-center items-center gap-2 mt-8">
          <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
            class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-medium hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed">
            Sebelumnya
          </button>
          <span class="text-sm font-semibold text-slate-600">
            Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
          </span>
          <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
            class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-medium hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed">
            Selanjutnya
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">


definePageMeta({ layout: 'default', middleware: 'auth' })

const authStore = useAuthStore()
authStore.restoreFromCookie()

if (authStore.isAdmin) {
  await navigateTo('/admin/kasir')
}

const api = useApi()

const orders = ref<any[]>([])
const loading = ref(true)
const downloading = ref<Record<number, boolean>>({})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10
})

const fetchOrders = async () => {
  loading.value = true
  try {
    const res = await api.get('/orders', {
      params: {
        page: pagination.value.current_page,
        per_page: pagination.value.per_page
      }
    })
    orders.value = res.data.data.data
    pagination.value = {
      current_page: res.data.data.meta.current_page,
      last_page: res.data.data.meta.last_page,
      per_page: res.data.data.meta.per_page
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const changePage = (page: number) => {
  pagination.value.current_page = page
  fetchOrders()
}

const downloadInvoice = async (order: any) => {
  downloading.value[order.id] = true
  try {
    // Get file stream as a blob
    const res = await api.get(`/orders/${order.id}/invoice`, {
      responseType: 'blob'
    })
    
    // Create local object URL & trigger download
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `invoice-${order.kode_pesanan}.pdf`)
    document.body.appendChild(link)
    link.click()
    
    // Cleanup
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error(error)
  } finally {
    downloading.value[order.id] = false
  }
}

onMounted(() => {
  fetchOrders()
})
</script>
