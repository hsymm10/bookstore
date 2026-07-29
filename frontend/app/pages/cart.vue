<template>
  <div class="pt-24 pb-20 bg-slate-50 min-h-screen">
    <div class="section-container">
      
      <h1 class="text-3xl font-black text-slate-800 mb-2">Keranjang Belanja</h1>
      <p class="text-slate-500 mb-8">Kelola item pilihan Anda sebelum melakukan pembayaran</p>

      <!-- ── STATE: BARCODE SUKSES (Setelah Konfirmasi) ── -->
      <div v-if="orderSuccess" class="max-w-md mx-auto">
        <div class="card p-8 text-center space-y-6">
          <!-- Success Icon -->
          <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto">
            <Icon name="mdi:check-circle" class="text-4xl text-emerald-500" />
          </div>
          <div>
            <h2 class="text-xl font-black text-slate-800">Pesanan Berhasil Dibuat!</h2>
            <p class="text-slate-500 text-sm mt-1">Tunjukkan barcode di bawah ini ke kasir untuk proses pembayaran.</p>
          </div>

          <!-- Info Pesanan -->
          <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-left space-y-1.5">
            <div class="flex justify-between text-sm">
              <span class="text-slate-500 font-medium">Kode Pesanan</span>
              <span class="font-black text-slate-800 font-mono">{{ orderSuccess.kode_pesanan }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500 font-medium">Invoice</span>
              <span class="font-bold text-slate-700 font-mono">{{ orderSuccess.invoice_number }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500 font-medium">Total Pembayaran</span>
              <span class="font-black text-primary-600">{{ formatRupiah(orderSuccess.total) }}</span>
            </div>
          </div>

          <!-- Barcode Batang (1D) -->
          <div class="bg-white border-2 border-slate-200 rounded-2xl p-6 flex flex-col items-center gap-3">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Scan Barcode di Kasir</p>
            <svg id="barcode-svg" class="w-full max-w-xs"></svg>
            <p class="text-sm font-black text-slate-700 font-mono tracking-widest">{{ orderSuccess.kode_pesanan }}</p>
          </div>

          <!-- Actions -->
          <div class="space-y-3">
            <button @click="printBarcode" 
              class="w-full btn-primary justify-center !py-3">
              <Icon name="mdi:printer" class="mr-2" />
              Cetak Barcode
            </button>
            <NuxtLink to="/orders" class="w-full btn-secondary justify-center !py-3 block text-center">
              Lihat Riwayat Pesanan
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- ── STATE: KERANJANG KOSONG ── -->
      <div v-else-if="cartStore.isEmpty" class="card p-12 text-center text-slate-500 max-w-lg mx-auto">
        <span class="text-6xl">🛒</span>
        <h3 class="font-bold text-lg text-slate-800 mt-4">Keranjang Anda Kosong</h3>
        <p class="text-sm mt-1">Silakan pilih buku menarik terlebih dahulu.</p>
        <NuxtLink to="/books" class="btn-primary mt-6">Jelajahi Buku</NuxtLink>
      </div>

      <!-- ── STATE: ISI KERANJANG ── -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Cart Items List -->
        <div class="lg:col-span-2 space-y-4">
          <div v-for="item in cartStore.items" :key="item.book_id"
            class="card p-4 flex gap-4 items-center hover:shadow-card transition-shadow">
            
            <!-- Book Cover -->
            <div class="w-16 h-22 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0 border border-slate-100">
              <img v-if="item.gambar_url" :src="item.gambar_url" :alt="item.nama_buku" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-xl" style="background:#f0f7ff">
                <Icon name="mdi:book-open-page-variant" class="text-xl" style="color:#2E5AA7" />
              </div>
            </div>

            <!-- Metadata -->
            <div class="flex-1 min-w-0">
              <h3 class="font-bold text-slate-800 text-sm leading-snug truncate" :title="item.nama_buku">
                {{ item.nama_buku }}
              </h3>
              <p class="text-primary-600 font-extrabold text-sm mt-1">{{ formatRupiah(item.harga_jual) }}</p>
              <p class="text-xs text-slate-400 mt-0.5">Stok tersedia: {{ item.stok }}</p>
            </div>

            <!-- Qty Selector & Action -->
            <div class="flex items-center gap-4">
              <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden bg-slate-50">
                <button @click="cartStore.updateQty(item.book_id, item.qty - 1)"
                  class="px-2.5 py-1 hover:bg-slate-100 text-slate-500 font-bold transition-colors">-</button>
                <span class="px-3 text-xs font-bold text-slate-700">{{ item.qty }}</span>
                <button @click="cartStore.updateQty(item.book_id, item.qty + 1)"
                  class="px-2.5 py-1 hover:bg-slate-100 text-slate-500 font-bold transition-colors"
                  :disabled="item.qty >= item.stok">+</button>
              </div>

              <!-- Subtotal info -->
              <div class="text-right w-24 hidden sm:block">
                <p class="text-xs text-slate-400">Subtotal</p>
                <p class="text-sm font-bold text-slate-700">{{ formatRupiah(item.subtotal) }}</p>
              </div>

              <!-- Remove button -->
              <button @click="cartStore.removeItem(item.book_id)"
                class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                <Icon name="mdi:trash-can-outline" class="text-lg" />
              </button>
            </div>

          </div>
        </div>

        <!-- Checkout Summary -->
        <div class="lg:col-span-1">
          <div class="card p-6 sticky top-24">
            <h3 class="font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Ringkasan Pesanan</h3>
            
            <div class="space-y-3 text-sm">
              <div class="flex justify-between text-slate-500">
                <span>Total Item</span>
                <span class="font-semibold">{{ cartStore.totalItems }} unit</span>
              </div>
              <div class="flex justify-between text-slate-500">
                <span>Pajak & Biaya</span>
                <span class="font-semibold text-emerald-600">Gratis / Rp 0</span>
              </div>
              <div class="h-px bg-slate-100 my-4" />
              <div class="flex justify-between items-end">
                <span class="font-bold text-slate-800">Total Pembayaran</span>
                <span class="text-2xl font-black text-primary-600 leading-none">
                  {{ formatRupiah(cartStore.totalHarga) }}
                </span>
              </div>
            </div>

            <!-- Checkout Action Button -->
            <button @click="handleCheckout" :disabled="loading"
              class="w-full btn-primary mt-6 justify-center !py-3">
              <span v-if="loading" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <span v-else>KONFIRMASI</span>
            </button>

            <NuxtLink to="/books" class="w-full btn-secondary mt-3 justify-center !py-3">
              Kembali Belanja
            </NuxtLink>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup lang="ts">

useHead({
  script: [
    { src: 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js', defer: true }
  ]
})

definePageMeta({ layout: 'default', middleware: 'auth' })

const authStore = useAuthStore()
authStore.restoreFromCookie()

if (authStore.isAdmin) {
  await navigateTo('/admin/dashboard')
}

const cartStore = useCartStore()
const api = useApi()
const { $swal, $toast } = useNuxtApp() as any

const loading = ref(false)
const orderSuccess = ref<any | null>(null)

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(val)
}

const generateBarcode = async (kode: string) => {
  await nextTick()
  await new Promise(resolve => setTimeout(resolve, 200))
  
  const tryGenerate = () => {
    const svgEl = document.getElementById('barcode-svg')
    if (!svgEl) return false

    const JsBarcode = (window as any).JsBarcode
    if (typeof JsBarcode === 'undefined') return false

    try {
      JsBarcode('#barcode-svg', kode, {
        format: 'CODE128',
        width: 2.5,
        height: 80,
        displayValue: false,
        margin: 10,
        background: '#ffffff',
        lineColor: '#000000'
      })
      return true
    } catch (e) {
      console.error('Barcode generation error:', e)
      return false
    }
  }

  // Retry until JsBarcode loads (max 3 seconds)
  let attempts = 0
  const interval = setInterval(() => {
    attempts++
    if (tryGenerate() || attempts >= 15) {
      clearInterval(interval)
    }
  }, 200)
}

const printBarcode = () => {
  if (!orderSuccess.value) return
  const kode = orderSuccess.value.kode_pesanan
  const total = formatRupiah(orderSuccess.value.total)
  const invoice = orderSuccess.value.invoice_number

  const svgEl = document.getElementById('barcode-svg')
  const svgContent = svgEl ? svgEl.outerHTML : ''

  const printWindow = window.open('', '_blank', 'width=400,height=600')
  if (!printWindow) return

  printWindow.document.write(`
    <html>
      <head>
        <title>Barcode Pesanan - ${kode}</title>
        <style>
          @page { size: auto; margin: 10mm; }
          body {
            font-family: 'Courier New', Courier, monospace;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
          }
          .title { font-size: 14px; font-weight: bold; margin-bottom: 4px; }
          .sub { font-size: 11px; color: #555; margin-bottom: 16px; }
          .barcode-wrap { border: 2px solid #ccc; border-radius: 8px; padding: 16px; background: #fff; }
          .kode { font-size: 18px; font-weight: bold; letter-spacing: 4px; margin-top: 8px; }
          .total { font-size: 12px; color: #333; margin-top: 8px; }
          .note { font-size: 10px; color: #999; margin-top: 16px; text-align: center; }
        </style>
      </head>
      <body>
        <div class="title">BOOK STORE</div>
        <div class="sub">Invoice: ${invoice}</div>
        <div class="barcode-wrap">
          ${svgContent}
          <div class="kode">${kode}</div>
          <div class="total">Total: ${total}</div>
        </div>
        <div class="note">Tunjukkan barcode ini ke kasir untuk pembayaran</div>
        <script>
          window.onload = function() {
            window.print();
            setTimeout(function() { window.close(); }, 800);
          }
        <\/script>
      </body>
    </html>
  `)
  printWindow.document.close()
}

const handleCheckout = async () => {
  const confirm = await $swal.fire({
    title: 'Konfirmasi Pesanan?',
    text: `Anda akan membuat pesanan dengan total ${formatRupiah(cartStore.totalHarga)}.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Pesan!',
    cancelButtonText: 'Batal'
  })

  if (!confirm.isConfirmed) return

  loading.value = true
  try {
    const items = cartStore.items.map(item => ({
      book_id: item.book_id,
      qty: item.qty
    }))

    const res = await api.post('/orders', { items })
    const order = res.data.data

    cartStore.clearCart()
    orderSuccess.value = order

    // Generate barcode setelah DOM update
    generateBarcode(order.kode_pesanan)

  } catch (error: any) {
    const msg = error.response?.data?.message || 'Gagal memproses pesanan.'
    $swal.fire({
      title: 'Gagal Checkout',
      text: msg,
      icon: 'error'
    })
  } finally {
    loading.value = false
  }
}
</script>
