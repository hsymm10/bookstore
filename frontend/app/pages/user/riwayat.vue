<template>
  <div class="flex-1 flex flex-col gap-5">

    <!-- ─── MODAL KONFIRMASI BATAL ────────────────────────────────── -->
    <Transition name="fade">
      <div v-if="cancelModal.show" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden">
          <div class="bg-gradient-to-r from-red-500 to-rose-600 p-5 text-center">
            <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-2">
              <Icon name="mdi:alert-circle-outline" class="text-4xl text-white" />
            </div>
            <h3 class="text-lg font-black text-white">Batalkan Pesanan?</h3>
          </div>
          <div class="p-5 space-y-4">
            <div class="bg-slate-50 rounded-2xl p-4 text-sm space-y-1.5">
              <div class="flex justify-between">
                <span class="text-slate-500 font-semibold">Invoice</span>
                <span class="font-black text-slate-800 font-mono text-xs">{{ cancelModal.order?.invoice_number }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500 font-semibold">Total</span>
                <span class="font-black text-blue-700">{{ formatRupiah(cancelModal.order?.total || 0) }}</span>
              </div>
            </div>
            <p class="text-xs text-slate-500 text-center leading-relaxed">
              Pesanan akan dibatalkan dan stok buku dikembalikan.
              Tindakan ini <strong class="text-red-600">tidak dapat diurungkan</strong>.
            </p>
            <div class="flex gap-2.5">
              <button
                @click="cancelModal.show = false"
                :disabled="cancelling !== null"
                class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl font-black text-sm uppercase tracking-wide transition-all active:scale-95 disabled:opacity-50"
              >Tidak</button>
              <button
                @click="cancelOrder(cancelModal.order)"
                :disabled="cancelling !== null"
                class="flex-1 py-3 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-black text-sm uppercase tracking-wide transition-all active:scale-95 shadow-lg shadow-red-200 flex items-center justify-center gap-2 disabled:opacity-70"
              >
                <span v-if="cancelling !== null" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                <template v-else>Ya, Batalkan</template>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ─── HEADER ─────────────────────────────────────────────────── -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 flex-shrink-0">
      <div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 uppercase tracking-wide">Riwayat Pesanan</h2>
        <p class="text-xs text-slate-400 font-semibold mt-0.5">Daftar semua transaksi pembelian Anda</p>
      </div>
      <div class="relative max-w-xs w-full sm:w-auto">
        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-slate-400">
          <Icon name="mdi:magnify" class="text-base" />
        </span>
        <input
          v-model="searchQuery"
          @input="fetchOrders(false)"
          type="text"
          placeholder="Cari invoice / kode..."
          class="w-full pl-9 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-300 bg-white text-slate-700 transition-all"
        />
      </div>
    </div>

    <!-- ─── LOADING ────────────────────────────────────────────────── -->
    <div v-if="loading" class="flex-1 flex items-center justify-center py-20">
      <div class="flex flex-col items-center gap-3">
        <div class="w-12 h-12 border-4 border-[#2E5AA7] border-t-transparent rounded-full animate-spin" />
        <p class="text-sm font-semibold text-slate-500">Memuat riwayat...</p>
      </div>
    </div>

    <!-- ─── EMPTY ──────────────────────────────────────────────────── -->
    <div v-else-if="orders.length === 0" class="flex-1 flex flex-col items-center justify-center py-16 text-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50">
      <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-4">
        <Icon name="mdi:history" class="text-4xl text-slate-300" />
      </div>
      <h3 class="text-base font-black text-slate-600">Belum ada transaksi</h3>
      <p class="text-sm text-slate-400 mt-1 max-w-xs">Anda belum pernah melakukan pembelian buku.</p>
      <NuxtLink to="/user/transaksi" class="mt-5 px-6 py-2.5 bg-[#2E5AA7] hover:bg-[#254b8c] text-white rounded-xl text-sm font-extrabold uppercase tracking-wide transition-colors shadow-md">
        Belanja Sekarang
      </NuxtLink>
    </div>

    <!-- ─── ORDER CARDS ────────────────────────────────────────────── -->
    <div v-else class="flex-1 flex flex-col gap-4 overflow-y-auto pr-0.5" style="scrollbar-width: thin;">
      <div
        v-for="order in orders"
        :key="order.id"
        class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow"
      >
        <!-- Card Top: Invoice + Status -->
        <div class="flex items-start justify-between gap-3 px-4 sm:px-5 pt-4 pb-3 border-b border-slate-100">
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-black text-slate-800 text-sm font-mono truncate">{{ order.invoice_number || '-' }}</span>
              <span class="text-[10px] text-slate-400 font-mono">·&nbsp;#{{ order.kode_pesanan }}</span>
            </div>
            <div class="flex items-center gap-1 mt-1 flex-wrap">
              <span class="text-[10px] font-semibold text-slate-400">
                <Icon name="mdi:clock-outline" class="text-xs" />
                {{ order.created_at }}
              </span>
            </div>

            <!-- ⏱ Countdown Timer — NO animate-pulse (menyebabkan gerak-gerak) -->
            <div
              v-if="order.status === 'pending' && order.payment_status !== 'paid'"
              class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black border-l-2 transition-colors duration-500"
              :class="getCountdown(order.id) <= 30
                ? 'bg-red-50 text-red-700 border-red-400'
                : 'bg-orange-50 text-orange-700 border-orange-300'"
            >
              <Icon name="mdi:timer-sand" class="text-xs flex-shrink-0" />
              <span v-if="getCountdown(order.id) > 0">
                Batal otomatis&nbsp;<span class="font-mono w-9 inline-block">{{ formatCountdown(getCountdown(order.id)) }}</span>
              </span>
              <span v-else class="text-red-700">Kedaluwarsa...</span>
            </div>
          </div>

          <!-- Status badges -->
          <div class="flex flex-col items-end gap-1 flex-shrink-0">
            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wide" :class="statusClass(order.status)">
              {{ order.status }}
            </span>
            <span
              class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wide"
              :class="order.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
            >
              {{ order.payment_status === 'paid' ? '✓ Lunas' : '⏳ Belum Lunas' }}
            </span>
          </div>
        </div>

        <!-- Card Middle: Books list -->
        <div class="px-4 sm:px-5 py-3 border-b border-slate-100">
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2">Buku yang dipesan</p>
          <div class="space-y-1.5">
            <div
              v-for="item in order.order_details"
              :key="item.id"
              class="flex items-center gap-2.5"
            >
              <!-- Thumbnail -->
              <div class="w-8 h-10 rounded-lg overflow-hidden bg-slate-100 flex-shrink-0 border border-slate-200">
                <img v-if="item.book?.gambar_url" :src="item.book.gambar_url" class="w-full h-full object-cover" alt="" />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <Icon name="mdi:book-open-page-variant" class="text-xs text-slate-300" />
                </div>
              </div>
              <div class="flex-1 min-w-0 flex items-center justify-between gap-2">
                <span class="text-xs font-bold text-slate-700 truncate">{{ item.book?.nama_buku || 'Buku Terhapus' }}</span>
                <span class="text-[10px] font-bold text-slate-400 flex-shrink-0">x{{ item.qty }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Card Bottom: Total + Actions -->
        <div class="px-4 sm:px-5 py-3 flex items-center justify-between gap-3 flex-wrap">
          <!-- Total -->
          <div>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider leading-none">Total Pembayaran</p>
            <p class="text-base font-black text-blue-700 leading-tight mt-0.5">{{ formatRupiah(order.total) }}</p>
            <p class="text-[10px] text-slate-400 font-semibold mt-0.5 uppercase">{{ order.payment_method }}</p>
          </div>

          <!-- Action buttons -->
          <div class="flex items-center gap-2 flex-wrap justify-end">

            <!-- BELUM LUNAS: tombol Batal + Bayar -->
            <template v-if="order.payment_status !== 'paid'" >
              <div
                v-if="order.status === 'pending'"
                class="flex items-center gap-1.5 px-3 py-2 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-[10px] font-bold"
              >
                <Icon name="mdi:information-outline" class="text-sm flex-shrink-0" />
                Selesaikan pembayaran terlebih dahulu
              </div>

              <!-- Tombol Batal (hanya untuk pending unpaid) -->
              <button
                v-if="order.status === 'pending'"
                @click="confirmCancel(order)"
                :disabled="cancelling === order.id"
                class="inline-flex items-center gap-1.5 px-3 py-2.5 bg-slate-100 hover:bg-red-50 border border-slate-200 hover:border-red-200 text-slate-600 hover:text-red-600 rounded-xl text-xs font-black uppercase tracking-wide transition-all disabled:opacity-50"
              >
                <span v-if="cancelling === order.id" class="w-3.5 h-3.5 border-2 border-current border-t-transparent rounded-full animate-spin" />
                <Icon v-else name="mdi:close-circle-outline" class="text-sm" />
                Batal
              </button>

              <!-- Tombol Bayar -->
              <button
                v-if="order.status === 'pending'"
                @click="showBarcodeModal(order)"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-black uppercase tracking-wide transition-all shadow-md"
              >
                <Icon name="mdi:barcode-scan" class="text-sm" />
                Bayar
              </button>
            </template>

            <!-- SUDAH LUNAS: tampil Invoice, PDF, Struk, QR -->
            <template v-else>
              <NuxtLink
                :to="`/invoice/${order.invoice_number}`"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 rounded-xl text-xs font-black uppercase tracking-wide transition-colors"
              >
                <Icon name="mdi:eye-outline" class="text-sm" />
                Invoice
              </NuxtLink>
              <button
                @click="downloadInvoicePdf(order)"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-black uppercase tracking-wide transition-colors"
              >
                <Icon name="mdi:file-pdf-box" class="text-sm" />
                PDF
              </button>
              <button
                @click="printReceipt(order)"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-700 hover:bg-slate-800 text-white rounded-xl text-xs font-black uppercase tracking-wide transition-colors"
              >
                <Icon name="mdi:printer" class="text-sm" />
                Struk
              </button>
              <button
                @click="showQrModal(order)"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 rounded-xl text-xs font-black uppercase tracking-wide transition-colors"
              >
                <Icon name="mdi:qrcode" class="text-sm" />
                QR
              </button>
            </template>
          </div>

        </div>
      </div>
    </div>

    <!-- ─── QR MODAL ────────────────────────────────────────────────── -->
    <Transition name="fade">
      <div v-if="qrModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white w-full max-w-sm p-6 rounded-3xl border border-slate-200 shadow-xl relative text-center">
          <button @click="closeQrModal" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
            <Icon name="mdi:close" class="text-lg" />
          </button>
          <h3 class="text-base font-black text-slate-800 mb-1">QR Code Invoice</h3>
          <p class="text-xs text-slate-400 font-mono mb-4">{{ qrModal.order?.invoice_number }}</p>
          <div class="flex justify-center bg-slate-50 p-6 rounded-2xl border border-slate-100">
            <img :src="dynamicQrUrl" class="w-44 h-44 bg-white p-2 rounded-lg border border-slate-200 shadow-sm" alt="QR Code" />
          </div>
          <p class="text-[10px] text-slate-400 mt-4 leading-relaxed italic">*Scan QR untuk menuju halaman Invoice Digital</p>
          <div class="mt-4 grid grid-cols-2 gap-2">
            <button @click="downloadReceiptFromModal" :disabled="downloadingStruk" class="py-2.5 bg-purple-600 hover:bg-purple-700 disabled:opacity-60 text-white font-extrabold text-xs rounded-xl uppercase transition-colors flex items-center justify-center gap-1.5">
              <span v-if="downloadingStruk" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin" />
              <Icon v-else name="mdi:receipt" class="text-sm" />
              Struk PDF
            </button>
            <button @click="closeQrModal" class="py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-extrabold text-xs rounded-xl uppercase tracking-wide transition-colors">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ─── BARCODE MODAL ───────────────────────────────────────────── -->
    <Transition name="fade">
      <div v-if="barcodeModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white w-full max-w-sm p-6 rounded-3xl border border-slate-200 shadow-xl relative text-center">
          <button @click="closeBarcodeModal" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
            <Icon name="mdi:close" class="text-lg" />
          </button>
          <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <Icon name="mdi:barcode-scan" class="text-2xl text-red-500" />
          </div>
          <h3 class="text-base font-black text-slate-800 mb-1">Scan Barcode Pembayaran</h3>
          <p class="text-xs text-slate-400 mb-4">Tunjukkan barcode ini kepada kasir untuk pembayaran.</p>
          <div class="flex flex-col items-center justify-center bg-white p-4 rounded-2xl border-2 border-slate-200">
            <svg id="barcode-svg" class="w-full max-h-24" />
            <span class="text-slate-800 font-black text-xl tracking-[0.3em] mt-3 font-mono">
              {{ barcodeModal.order?.kode_pesanan }}
            </span>
          </div>
          <p class="text-[10px] text-slate-400 mt-3 italic">*Tunjukkan ke kasir atau scan dengan scanner barcode</p>
          <div class="mt-4">
            <button @click="closeBarcodeModal" class="w-full py-3 bg-[#2E5AA7] hover:bg-[#254b8c] text-white font-extrabold text-xs rounded-xl uppercase tracking-wider transition-colors shadow-md">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'user', middleware: 'auth' })

useHead({
  script: [{ src: 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js', defer: true }]
})

const api = useApi()
const authStore = useAuthStore()
const { $toast } = useNuxtApp() as any

const orders = ref<any[]>([])
const loading = ref(true)
const searchQuery = ref('')
const cancelling = ref<number | null>(null)

// ── Countdown timer state ─────────────────────────────────────────
// Map: orderId → seconds remaining until auto-expire (2 min = 120s)
const countdowns = ref<Record<number, number>>({})
let countdownInterval: ReturnType<typeof setInterval> | null = null
let refreshInterval: ReturnType<typeof setInterval> | null = null

const AUTO_EXPIRE_SECONDS = 120 // 2 menit

// Inisialisasi countdown untuk semua order pending+unpaid
const initCountdowns = () => {
  orders.value.forEach(order => {
    if (order.status !== 'pending' || order.payment_status === 'paid') return
    if (countdowns.value[order.id] !== undefined) return // sudah ada, jangan reset
    // Hitung sisa waktu dari created_at
    const createdAt = new Date(order.raw_created_at || order.created_at_raw || order.created_at).getTime()
    const elapsed = Math.floor((Date.now() - createdAt) / 1000)
    countdowns.value[order.id] = Math.max(0, AUTO_EXPIRE_SECONDS - elapsed)
  })
}

const getCountdown = (orderId: number) => countdowns.value[orderId] ?? AUTO_EXPIRE_SECONDS

const formatCountdown = (seconds: number) => {
  if (seconds <= 0) return '0:00'
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m}:${String(s).padStart(2, '0')}`
}

// Tick setiap detik — TIDAK re-render loading, hanya update countdown map
const startCountdownTick = () => {
  countdownInterval = setInterval(() => {
    let anyExpired = false
    orders.value.forEach(order => {
      if (order.status !== 'pending' || order.payment_status === 'paid') return
      if (countdowns.value[order.id] === undefined) return
      if (countdowns.value[order.id] > 0) {
        countdowns.value[order.id]--
      } else {
        anyExpired = true
      }
    })
    // Kalau ada yang expired, refresh TANPA loading spinner (silent)
    if (anyExpired) fetchOrders(true)
  }, 1000)
}

const qrModal = ref({ show: false, order: null as any })
const downloadingStruk = ref(false)
const barcodeModal = ref({ show: false, order: null as any })

const renderBarcode = (kode: string) => {
  nextTick(() => {
    const svgEl = document.getElementById('barcode-svg')
    if (!svgEl) return
    const JsBarcode = (window as any).JsBarcode
    if (!JsBarcode) { setTimeout(() => renderBarcode(kode), 300); return }
    JsBarcode(svgEl, kode, {
      format: 'CODE128', width: 2.5, height: 80,
      displayValue: false, margin: 10,
      background: '#ffffff', lineColor: '#000000'
    })
  })
}

const dynamicQrUrl = computed(() => {
  if (!qrModal.value.order) return ''
  const invoiceNum = qrModal.value.order.invoice_number
  if (!invoiceNum) return ''
  const origin = typeof window !== 'undefined' ? window.location.origin : 'http://localhost:3000'
  return `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(`${origin}/invoice/${invoiceNum}`)}`
})

// silent = true → background refresh, jangan tampilkan loading spinner
const fetchOrders = async (silent = false) => {
  if (!silent) loading.value = true
  try {
    const res = await api.get('/orders', { params: { search: searchQuery.value } })
    orders.value = res.data.data.data
    initCountdowns()
    if (!countdownInterval) startCountdownTick()
  } catch (e) { console.error(e) }
  finally { if (!silent) loading.value = false }
}

const formatRupiah = (val: number) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val)

const statusClass = (status: string) => {
  switch (status) {
    case 'selesai': return 'bg-emerald-100 text-emerald-700'
    case 'diproses': return 'bg-blue-100 text-blue-700'
    case 'pending': return 'bg-amber-100 text-amber-700'
    case 'batal': return 'bg-rose-100 text-rose-700'
    default: return 'bg-slate-100 text-slate-600'
  }
}

const downloadInvoicePdf = async (order: any) => {
  try {
    const res = await api.get(`/orders/${order.id}/invoice`, { responseType: 'blob' })
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = `invoice-${order.invoice_number ?? order.kode_pesanan}.pdf`
    link.click()
  } catch { $toast.fire({ icon: 'error', title: 'Gagal mengunduh invoice PDF.' }) }
}

const printReceipt = (order: any) => {
  if (!order || order.payment_status !== 'paid') {
    $toast.fire({ icon: 'warning', title: 'Struk hanya bisa dicetak untuk pesanan yang sudah lunas!' }); return
  }
  const printWindow = window.open('', '_blank', 'width=350,height=600')
  if (!printWindow) return
  const itemsHtml = order.order_details.map((d: any) =>
    `<div style="display:flex;justify-content:space-between;margin-bottom:2px;">
      <span style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${d.book?.nama_buku}</span>
      <span>${d.qty} x ${formatRupiah(d.harga)}</span>
    </div>`
  ).join('')
  printWindow.document.write(`<html><head><title>Struk - ${order.invoice_number}</title>
    <style>@page{size:58mm auto;margin:0}body{font-family:'Courier New',monospace;font-size:11px;color:#000;padding:10px;width:200px;margin:0 auto}.center{text-align:center}.divider{border-top:1px dashed #000;margin:5px 0}.fb{display:flex;justify-content:space-between}.bold{font-weight:bold}</style>
    </head><body>
    <div class="center bold">====================================<br>BOOK STORE<br>====================================</div>
    <div style="margin-top:5px;">No Invoice: ${order.invoice_number}<br>Tanggal   : ${order.created_at}</div>
    <div class="divider"></div><div>${itemsHtml}</div><div class="divider"></div>
    <div class="fb bold"><span>Total</span><span>${formatRupiah(order.total)}</span></div>
    <div class="divider"></div><div class="center bold">Status : LUNAS</div>
    <div class="center" style="margin-top:6px;">Terima kasih telah berbelanja</div>
    <div class="center">====================================</div>
    <script>window.onload=function(){window.print();setTimeout(function(){window.close()},500)}<\/script>
    </body></html>`)
  printWindow.document.close()
}

const showQrModal = (order: any) => { qrModal.value = { show: true, order } }
const closeQrModal = () => { qrModal.value = { show: false, order: null } }

const showBarcodeModal = (order: any) => {
  barcodeModal.value = { show: true, order }
  renderBarcode(order.kode_pesanan)
}
const closeBarcodeModal = () => { barcodeModal.value = { show: false, order: null } }

const downloadReceiptFromModal = async () => {
  const order = qrModal.value.order
  if (!order) return
  if (order.payment_status !== 'paid') {
    $toast.fire({ icon: 'warning', title: 'Struk hanya bisa diunduh untuk pesanan yang sudah lunas!' }); return
  }
  downloadingStruk.value = true
  try {
    const res = await api.get(`/orders/${order.id}/receipt`, { responseType: 'blob' })
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `Struk-${order.invoice_number ?? order.kode_pesanan}.pdf`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
    $toast.fire({ icon: 'success', title: 'Struk PDF berhasil diunduh.' })
  } catch { $toast.fire({ icon: 'error', title: 'Gagal mengunduh struk.' }) }
  finally { downloadingStruk.value = false }
}

// ── Cancel order ─────────────────────────────────────────────────
const cancelModal = ref({ show: false, order: null as any })

const confirmCancel = (order: any) => {
  cancelModal.value = { show: true, order }
}

const cancelOrder = async (order: any) => {
  if (!order) return
  cancelling.value = order.id
  try {
    await api.delete(`/orders/${order.id}/cancel`)
    // Tutup modal
    cancelModal.value = { show: false, order: null }
    // Tampil notifikasi sukses
    $toast.fire({ icon: 'success', title: 'Pesanan dibatalkan!', text: 'Stok buku telah dikembalikan.' })
    // Hapus dari list lokal (optimistic UI)
    orders.value = orders.value.filter(o => o.id !== order.id)
    delete countdowns.value[order.id]
    // Broadcast ke transaksi.vue agar stok langsung diperbarui
    localStorage.setItem('bookstok_refresh', Date.now().toString())
  } catch (err: any) {
    cancelModal.value = { show: false, order: null }
    $toast.fire({ icon: 'error', title: err.response?.data?.message || 'Gagal membatalkan pesanan.' })
  } finally { cancelling.value = null }
}

onMounted(() => {
  fetchOrders()          // first load — tampilkan spinner
  // Background refresh setiap 30 detik: SILENT (tidak flicker)
  refreshInterval = setInterval(() => fetchOrders(true), 30_000)
})

onUnmounted(() => {
  if (countdownInterval) clearInterval(countdownInterval)
  if (refreshInterval) clearInterval(refreshInterval)
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
