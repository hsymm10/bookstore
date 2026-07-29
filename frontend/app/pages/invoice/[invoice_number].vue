<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4 md:p-8 font-sans">
    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center gap-3">
      <div class="w-12 h-12 border-4 border-[#2E5AA7] border-t-transparent rounded-full animate-spin"></div>
      <p class="text-sm font-semibold text-slate-500">Memuat detail invoice...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="max-w-md w-full bg-white p-8 rounded-3xl border border-slate-200 text-center shadow-lg">
      <Icon name="mdi:alert-circle-outline" class="text-6xl text-rose-500 mb-4" />
      <h3 class="text-lg font-bold text-slate-700">Invoice Tidak Ditemukan</h3>
      <p class="text-sm text-slate-400 mt-1 mb-6">{{ errorMessage }}</p>
      <button @click="goBack" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-xl text-xs uppercase tracking-wider transition-colors">
        Kembali
      </button>
    </div>

    <!-- Main Content -->
    <div v-else class="max-w-3xl w-full space-y-6">

      <!-- Main Invoice Card -->
      <div id="invoice-card" class="w-full bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
        <!-- Invoice Header Banner -->
        <div class="bg-gradient-to-r from-blue-700 to-indigo-900 text-white p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-widest text-blue-200 font-bold">Digital Invoice</p>
            <h2 class="text-2xl md:text-3xl font-black mt-1 font-mono tracking-tight">{{ order.invoice_number }}</h2>
            <p class="text-xs text-blue-100 mt-1">Kode Pesanan: #{{ order.kode_pesanan }}</p>
          </div>
          <div class="text-left md:text-right">
            <span
              class="inline-block px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider shadow-sm"
              :class="order.payment_status === 'paid' ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white'"
            >
              {{ order.payment_status_label }}
            </span>
            <p class="text-xs text-blue-200 mt-2">Status Pesanan: <span class="font-bold text-white capitalize">{{ order.status }}</span></p>
          </div>
        </div>

        <!-- Invoice Details Panel -->
        <div class="p-6 md:p-8 space-y-8">
          <!-- Meta Row -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm pb-6 border-b border-slate-100">
            <div class="space-y-1 text-slate-600">
              <p class="text-xs uppercase tracking-wider text-slate-400 font-bold">Pelanggan</p>
              <p class="font-bold text-slate-800 text-base">{{ order.user }}</p>
              <p>{{ order.user_email }}</p>
            </div>
            <div class="space-y-1 text-slate-600 md:text-right">
              <p class="text-xs uppercase tracking-wider text-slate-400 font-bold">Tanggal &amp; Metode Pembayaran</p>
              <p><strong>Transaksi:</strong> {{ order.created_at }}</p>
              <p v-if="order.paid_at"><strong>Waktu Bayar:</strong> {{ order.paid_at }}</p>
              <p><strong>Metode:</strong> {{ order.payment_method_label }}</p>
            </div>
          </div>

          <!-- Order Items Table -->
          <div>
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Daftar Buku Yang Dipesan</h3>
            <div class="overflow-x-auto border border-slate-200 rounded-2xl">
              <table class="w-full text-left border-collapse text-sm">
                <thead>
                  <tr class="bg-slate-50 border-b border-slate-200 text-xs font-black text-slate-900 uppercase">
                    <th class="p-4">Judul Buku</th>
                    <th class="p-4 text-center">Qty</th>
                    <th class="p-4 text-right">Harga</th>
                    <th class="p-4 text-right">Subtotal</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                  <tr v-for="item in order.order_details" :key="item.id">
                    <td class="p-4 font-bold text-slate-800">{{ item.book?.nama_buku || 'Buku Terhapus' }}</td>
                    <td class="p-4 text-center font-mono">{{ item.qty }}</td>
                    <td class="p-4 text-right font-mono">{{ formatRupiah(item.harga) }}</td>
                    <td class="p-4 text-right font-mono font-bold">{{ formatRupiah(item.subtotal) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Totals & Payment Summary -->
          <div class="flex flex-col md:flex-row gap-6 justify-between items-start pt-6 border-t border-slate-100">
            <!-- QR & Barcodes -->
            <div class="flex flex-wrap gap-4 items-center bg-slate-50 p-4 rounded-2xl border border-slate-100 w-full md:w-auto">
              <!-- QR Code -->
              <div class="flex flex-col items-center">
                <img :src="dynamicQrUrl" class="w-24 h-24 bg-white p-1 rounded-lg border border-slate-200 shadow-sm" alt="QR Code Invoice" />
                <span class="text-[9px] font-bold text-slate-400 mt-1 font-mono">Scan QR Invoice</span>
              </div>
              <!-- Barcode -->
              <div class="flex flex-col items-center">
                <img :src="order.barcode_url" class="h-16 w-36 bg-white p-1 rounded-lg border border-slate-200 shadow-sm object-contain" alt="Barcode Invoice" />
                <span class="text-[9px] font-bold text-slate-400 mt-1.5 font-mono">Scan Barcode</span>
              </div>
            </div>

            <!-- Total Calculation -->
            <div class="w-full md:w-80 space-y-3 font-bold text-sm text-slate-600">
              <div class="flex justify-between">
                <span>Subtotal:</span>
                <span class="font-mono">{{ formatRupiah(order.total) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Diskon:</span>
                <span class="font-mono text-slate-400">Rp0</span>
              </div>
              <div class="flex justify-between text-base text-slate-900 border-t border-slate-100 pt-3">
                <span>Total Tagihan:</span>
                <span class="font-mono text-blue-700 text-lg font-black">{{ formatRupiah(order.total) }}</span>
              </div>

              <!-- Cash and Change Details -->
              <div v-if="order.payment_status === 'paid'" class="bg-emerald-50 text-emerald-800 p-3 rounded-xl border border-emerald-100 space-y-1.5 text-xs mt-4">
                <div class="flex justify-between">
                  <span>Nominal Dibayar:</span>
                  <span class="font-mono">{{ formatRupiah(order.amount_paid ?? order.total) }}</span>
                </div>
                <div class="flex justify-between font-extrabold border-t border-emerald-200/50 pt-1.5">
                  <span>Kembalian:</span>
                  <span class="font-mono">{{ formatRupiah(order.change_amount ?? 0) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons Footer -->
        <div class="bg-slate-50 px-6 py-5 md:px-8 border-t border-slate-200 flex flex-wrap gap-3 items-center justify-between">
          <button @click="goBack" class="px-5 py-2.5 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs uppercase tracking-wider transition-colors flex items-center gap-1.5">
            <Icon name="mdi:arrow-left" class="text-sm" />
            Kembali
          </button>

          <div class="flex flex-wrap gap-2">
            <!-- Download Struk PDF -->
            <button
              @click="downloadReceiptPdf"
              :disabled="order.payment_status !== 'paid' || downloadingReceipt"
              class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-extrabold rounded-xl text-xs uppercase tracking-wider transition-all flex items-center gap-1.5 shadow-sm"
            >
              <span v-if="downloadingReceipt" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <Icon v-else name="mdi:receipt-text-outline" class="text-sm" />
              Unduh Struk
            </button>

            <!-- Download Invoice PDF -->
            <button
              @click="downloadInvoicePdf"
              :disabled="downloadingInvoice"
              class="px-5 py-2.5 bg-[#2E5AA7] hover:bg-[#254b8c] disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-extrabold rounded-xl text-xs uppercase tracking-wider transition-colors flex items-center gap-1.5 shadow-sm"
            >
              <span v-if="downloadingInvoice" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <Icon v-else name="mdi:file-pdf-box" class="text-sm" />
              Unduh Invoice PDF
            </button>
          </div>
        </div>
      </div>

      <!-- Struk Digital Preview Card -->
      <div v-if="order.payment_status === 'paid'" id="struk-card" class="w-full bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
        <div class="bg-slate-800 text-white px-6 py-4 flex items-center justify-between">
          <div>
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold">Struk Digital</p>
            <h3 class="text-lg font-black font-mono mt-0.5">{{ order.invoice_number }}</h3>
          </div>
          <Icon name="mdi:receipt-text" class="text-3xl text-slate-500" />
        </div>
        <div class="p-6 md:p-8">
          <!-- Thermal Style Receipt Preview -->
          <div class="max-w-xs mx-auto font-mono text-[11px] text-black bg-white border border-slate-200 p-5 rounded-xl shadow-inner">
            <div class="text-center font-bold mb-3">
              <p>============================</p>
              <p class="text-sm my-1">BOOK STORE</p>
              <p>============================</p>
            </div>
            <div class="space-y-1 mb-3">
              <div class="flex justify-between">
                <span>No Invoice</span>
                <span class="font-bold">{{ order.invoice_number }}</span>
              </div>
              <div class="flex justify-between">
                <span>Tanggal</span>
                <span>{{ order.created_at }}</span>
              </div>
              <div class="flex justify-between">
                <span>Metode</span>
                <span>{{ order.payment_method_label }}</span>
              </div>
            </div>
            <div class="border-t border-dashed border-black my-2"></div>
            <div v-for="item in order.order_details" :key="item.id" class="mb-2">
              <p class="font-bold truncate">{{ item.book?.nama_buku || 'Buku Terhapus' }}</p>
              <div class="flex justify-between pl-2 text-slate-600">
                <span>{{ item.qty }} x {{ formatRupiah(item.harga) }}</span>
                <span>{{ formatRupiah(item.subtotal) }}</span>
              </div>
            </div>
            <div class="border-t border-dashed border-black my-2"></div>
            <div class="space-y-1">
              <div class="flex justify-between">
                <span>Subtotal</span>
                <span>{{ formatRupiah(order.total) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Diskon</span>
                <span>Rp0</span>
              </div>
              <div class="flex justify-between font-bold border-t border-black mt-1 pt-1">
                <span>TOTAL</span>
                <span>{{ formatRupiah(order.total) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Dibayar</span>
                <span>{{ formatRupiah(order.amount_paid ?? order.total) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Kembali</span>
                <span>{{ formatRupiah(order.change_amount ?? 0) }}</span>
              </div>
            </div>
            <div class="border-t border-black mt-2 pt-2 text-center">
              <p class="font-bold">Status: LUNAS</p>
              <p class="mt-1 text-slate-500">Terima kasih telah berbelanja</p>
              <p>============================</p>
            </div>
          </div>

          <!-- Download Struk Buttons -->
          <div class="flex flex-wrap gap-3 justify-center mt-6">
            <button
              @click="downloadReceiptPdf"
              :disabled="downloadingReceipt"
              class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-extrabold rounded-xl text-xs uppercase tracking-wider transition-all flex items-center gap-1.5 shadow-sm"
            >
              <span v-if="downloadingReceipt" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <Icon v-else name="mdi:download" class="text-sm" />
              Unduh Struk PDF
            </button>
            <button
              @click="printReceiptBrowser"
              class="px-5 py-2.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-extrabold rounded-xl text-xs uppercase tracking-wider transition-all flex items-center gap-1.5 shadow-sm"
            >
              <Icon name="mdi:printer" class="text-sm" />
              Cetak Struk
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
  layout: false
})

const route = useRoute()
const router = useRouter()
const api = useApi()
const authStore = useAuthStore()
const { $toast } = useNuxtApp() as any

const loading = ref(true)
const error = ref(false)
const errorMessage = ref('')
const order = ref<any>(null)
const downloadingInvoice = ref(false)
const downloadingReceipt = ref(false)

const invoiceNumber = route.params.invoice_number as string

// Generate QR URL dynamically from current origin so it works on any host/port
const dynamicQrUrl = computed(() => {
  if (!order.value) return ''
  const invoiceNum = order.value.invoice_number
  if (!invoiceNum) return ''
  const origin = typeof window !== 'undefined' ? window.location.origin : 'http://localhost:3000'
  const invoicePageUrl = `${origin}/invoice/${invoiceNum}`
  return `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(invoicePageUrl)}`
})

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(val)
}

const fetchInvoiceDetails = async () => {
  loading.value = true
  error.value = false
  try {
    const res = await api.get(`/orders/invoice/${invoiceNumber}`)
    order.value = res.data.data
  } catch (err: any) {
    error.value = true
    errorMessage.value = err.response?.data?.message || 'Gagal memuat rincian invoice.'
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  if (authStore.isAdmin) {
    router.push('/admin/kasir')
  } else {
    router.push('/user/riwayat')
  }
}

/** Download Invoice PDF (server-rendered, fully resolved) */
const downloadInvoicePdf = async () => {
  if (!order.value || downloadingInvoice.value) return
  downloadingInvoice.value = true
  try {
    const res = await api.get(`/orders/${order.value.id}/invoice`, { responseType: 'blob' })
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = `Invoice-${order.value.invoice_number}.pdf`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(link.href)
    $toast.fire({ icon: 'success', title: 'Invoice PDF berhasil diunduh.' })
  } catch (err) {
    $toast.fire({ icon: 'error', title: 'Gagal mengunduh Invoice PDF.' })
  } finally {
    downloadingInvoice.value = false
  }
}

/** Download Struk PDF (server-rendered thermal layout) */
const downloadReceiptPdf = async () => {
  if (!order.value || downloadingReceipt.value) return
  downloadingReceipt.value = true
  try {
    const res = await api.get(`/orders/${order.value.id}/receipt`, { responseType: 'blob' })
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = `Struk-${order.value.invoice_number}.pdf`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(link.href)
    $toast.fire({ icon: 'success', title: 'Struk PDF berhasil diunduh.' })
  } catch (err) {
    $toast.fire({ icon: 'error', title: 'Gagal mengunduh Struk PDF.' })
  } finally {
    downloadingReceipt.value = false
  }
}

/** Print struk via browser print dialog (popup window) */
const printReceiptBrowser = () => {
  if (!order.value || order.value.payment_status !== 'paid') return

  const itemsHtml = order.value.order_details.map((detail: any) => `
    <div class="item-name">${detail.book?.nama_buku || 'Buku Terhapus'}</div>
    <div class="item-detail">
      <span>${detail.qty} x ${formatRupiah(detail.harga)}</span>
      <span>${formatRupiah(detail.subtotal)}</span>
    </div>
  `).join('')

  const printWindow = window.open('', '_blank', 'width=300,height=600')
  if (!printWindow) return

  printWindow.document.write(`
    <html>
      <head>
        <title>Struk - ${order.value.invoice_number}</title>
        <style>
          @page { size: 58mm auto; margin: 0; }
          body { font-family: 'Courier New', monospace; font-size: 10px; color: #000; padding: 8px; width: 164px; margin: 0 auto; }
          .center { text-align: center; }
          .bold { font-weight: bold; }
          .divider { border-top: 1px dashed #000; margin: 4px 0; }
          .flex { display: flex; justify-content: space-between; }
          .item-name { font-weight: bold; }
          .item-detail { display: flex; justify-content: space-between; padding-left: 6px; margin-bottom: 3px; }
        </style>
      </head>
      <body>
        <div class="center bold">============================<br>BOOK STORE<br>============================</div>
        <div style="margin-top:4px;">
          <div class="flex"><span>No Invoice</span><span>${order.value.invoice_number}</span></div>
          <div class="flex"><span>Tanggal</span><span>${order.value.created_at}</span></div>
          <div class="flex"><span>Metode</span><span>${order.value.payment_method_label}</span></div>
        </div>
        <div class="divider"></div>
        ${itemsHtml}
        <div class="divider"></div>
        <div class="flex"><span>Subtotal</span><span>${formatRupiah(order.value.total)}</span></div>
        <div class="flex"><span>Diskon</span><span>Rp0</span></div>
        <div class="flex bold"><span>TOTAL</span><span>${formatRupiah(order.value.total)}</span></div>
        <div class="flex"><span>Dibayar</span><span>${formatRupiah(order.value.amount_paid ?? order.value.total)}</span></div>
        <div class="flex"><span>Kembali</span><span>${formatRupiah(order.value.change_amount ?? 0)}</span></div>
        <div class="divider"></div>
        <div class="center bold">Status: LUNAS</div>
        <div class="center" style="margin-top:4px;">Terima kasih telah berbelanja<br>============================</div>
        <script>window.onload = function() { window.print(); setTimeout(function() { window.close(); }, 500); }<\/script>
      </body>
    </html>
  `)
  printWindow.document.close()
}

onMounted(() => {
  fetchInvoiceDetails()
})
</script>
