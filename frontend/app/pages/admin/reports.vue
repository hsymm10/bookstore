<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <h2 class="text-3xl font-black text-slate-800 uppercase tracking-wide">Laporan Penjualan</h2>
    </div>

    <!-- MAIN PANEL -->
    <div class="bg-[#f0f2f5] p-6 md:p-8 rounded-3xl border border-slate-300 shadow-sm min-h-[450px] space-y-6">

      <!-- Toolbar: Filters -->
      <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-4">
        <!-- Filter Type Tabs -->
        <div class="flex flex-wrap gap-2">
          <button
            v-for="f in filterOptions"
            :key="f.value"
            @click="setFilter(f.value)"
            class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all"
            :class="filters.filter === f.value
              ? 'bg-[#2E5AA7] text-white shadow-md'
              : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
          >
            {{ f.label }}
          </button>
        </div>

        <!-- Custom Date Range Inputs -->
        <div v-if="filters.filter === 'custom'" class="flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-500 uppercase">Dari:</label>
            <input v-model="filters.start_date" type="date" @change="fetchReport"
              class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>
          <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-500 uppercase">Sampai:</label>
            <input v-model="filters.end_date" type="date" @change="fetchReport"
              class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
          </div>
        </div>

        <!-- Search -->
        <div class="relative max-w-xs w-full">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
            <Icon name="mdi:magnify" class="text-lg" />
          </span>
          <input v-model="search" @input="debounceSearch" type="text" placeholder="Cari pembeli atau buku..."
            class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-xl text-sm bg-white placeholder-slate-400 focus:outline-none" />
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
          <p class="text-xs uppercase font-bold text-slate-400 tracking-wider">Total Transaksi</p>
          <p class="text-3xl font-black text-slate-800 mt-2">{{ summary.total_transaksi }}</p>
          <p class="text-xs text-slate-400 mt-1">pesanan selesai</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
          <p class="text-xs uppercase font-bold text-slate-400 tracking-wider">Total Buku Terjual</p>
          <p class="text-3xl font-black text-slate-800 mt-2">{{ summary.total_buku_terjual }}</p>
          <p class="text-xs text-slate-400 mt-1">eksemplar buku</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
          <p class="text-xs uppercase font-bold text-slate-400 tracking-wider">Total Pendapatan</p>
          <p class="text-2xl font-black text-[#2E5AA7] mt-2">{{ formatRupiah(summary.total_pendapatan) }}</p>
          <p class="text-xs text-slate-400 mt-1">dari transaksi lunas</p>
        </div>
      </div>

      <!-- Report Table -->
      <div class="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm">
        <table class="w-full border-collapse text-xs">
          <thead>
            <tr class="bg-slate-100 border-b border-slate-200 text-xs font-black text-slate-900 font-sans uppercase">
              <th class="border-r border-slate-200 px-4 py-3 text-center">No Invoice</th>
              <th class="border-r border-slate-200 px-4 py-3 text-left">Nama Pembeli</th>
              <th class="border-r border-slate-200 px-4 py-3 text-left">Nama Buku</th>
              <th class="border-r border-slate-200 px-4 py-3 text-center">Qty</th>
              <th class="border-r border-slate-200 px-4 py-3 text-right">Subtotal</th>
              <th class="border-r border-slate-200 px-4 py-3 text-center">Metode</th>
              <th class="border-r border-slate-200 px-4 py-3 text-center">Status</th>
              <th class="px-4 py-3 text-center">Tanggal</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="8" class="text-center py-12">
                <div class="flex justify-center items-center gap-2 text-slate-500 font-bold">
                  <span class="w-5 h-5 border-2 border-[#2E5AA7] border-t-transparent rounded-full animate-spin"></span>
                  <span>Menyusun laporan...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="rows.length === 0">
              <td colspan="8" class="text-center py-12 text-slate-400 font-semibold">
                Tidak ada transaksi lunas pada filter yang dipilih.
              </td>
            </tr>
            <template v-else>
              <tr v-for="(row, idx) in rows" :key="idx"
                class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors font-medium text-slate-700">
                <td class="border-r border-slate-100 px-4 py-3 text-center font-mono text-[#2E5AA7] font-bold">
                  {{ row.invoice_number }}
                </td>
                <td class="border-r border-slate-100 px-4 py-3 font-bold text-slate-800">
                  {{ row.buyer_name }}
                </td>
                <td class="border-r border-slate-100 px-4 py-3">{{ row.book_name }}</td>
                <td class="border-r border-slate-100 px-4 py-3 text-center font-mono">{{ row.qty }}</td>
                <td class="border-r border-slate-100 px-4 py-3 text-right font-mono font-bold text-slate-900">
                  {{ formatRupiah(row.subtotal) }}
                </td>
                <td class="border-r border-slate-100 px-4 py-3 text-center">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase">
                    {{ row.payment_method }}
                  </span>
                </td>
                <td class="border-r border-slate-100 px-4 py-3 text-center">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">
                    {{ row.payment_status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center text-slate-500">{{ row.tanggal }}</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Action Footer: Download buttons & Pagination -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Download Buttons -->
        <div class="flex flex-wrap gap-3">
          <button
            @click="exportReport('pdf')"
            :disabled="exporting.pdf || loading"
            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-extrabold rounded-xl shadow-md active:scale-95 transition-all duration-200 uppercase text-xs tracking-wider flex items-center gap-1.5"
          >
            <span v-if="exporting.pdf" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <Icon v-else name="mdi:file-pdf-box" class="text-base" />
            Download PDF
          </button>
          <button
            @click="exportReport('excel')"
            :disabled="exporting.excel || loading"
            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-extrabold rounded-xl shadow-md active:scale-95 transition-all duration-200 uppercase text-xs tracking-wider flex items-center gap-1.5"
          >
            <span v-if="exporting.excel" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <Icon v-else name="mdi:microsoft-excel" class="text-base" />
            Download Excel
          </button>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="flex gap-2 flex-wrap">
          <button
            v-for="page in pagination.last_page"
            :key="page"
            @click="changePage(page)"
            class="w-8 h-8 rounded-full font-black text-xs transition-all flex items-center justify-center"
            :class="pagination.current_page === page
              ? 'bg-[#2E5AA7] text-white'
              : 'bg-slate-200 text-slate-800 hover:bg-slate-300'"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: 'admin', ssr: false })

const api = useApi()
const { $toast } = useNuxtApp() as any

// Flat rows for the table
interface ReportRow {
  invoice_number: string
  buyer_name: string
  book_name: string
  qty: number
  subtotal: number
  payment_method: string
  payment_status: string
  tanggal: string
}

const rows = ref<ReportRow[]>([])
const loading = ref(true)
const exporting = ref({ pdf: false, excel: false })
const search = ref('')

const filterOptions = [
  { label: 'Hari Ini', value: 'hari_ini' },
  { label: 'Minggu Ini', value: 'minggu_ini' },
  { label: 'Bulan Ini', value: 'bulan_ini' },
  { label: 'Tahun Ini', value: 'tahun_ini' },
  { label: 'Custom', value: 'custom' },
]

const today = new Date().toISOString().split('T')[0]

const filters = ref({
  filter: 'bulan_ini' as string,
  start_date: today,
  end_date: today,
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
})

const summary = ref({
  total_transaksi: 0,
  total_buku_terjual: 0,
  total_pendapatan: 0,
})

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(val ?? 0)
}

const setFilter = (value: string) => {
  filters.value.filter = value
  pagination.value.current_page = 1
  fetchReport()
}

const changePage = (page: number) => {
  pagination.value.current_page = page
  fetchReport()
}

let searchTimeout: any = null
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchReport()
  }, 500)
}

const buildParams = (): Record<string, any> => {
  const params: Record<string, any> = {
    filter: filters.value.filter,
    search: search.value || undefined,
    page: pagination.value.current_page,
    per_page: pagination.value.per_page,
  }
  if (filters.value.filter === 'custom') {
    params.start_date = filters.value.start_date
    params.end_date = filters.value.end_date
  }
  return params
}

const fetchReport = async () => {
  loading.value = true
  rows.value = []

  try {
    const res = await api.get('/reports', { params: buildParams() })
    const data = res.data.data

    // orders wrapped with OrderResource: { data: [...], meta: {...} }
    const ordersWrapped = data.orders ?? {}
    const orders = ordersWrapped.data ?? []

    // Flatten orders into item rows for the table
    const flat: ReportRow[] = []
    orders.forEach((order: any) => {
      const details = order.order_details ?? order.orderDetails ?? []
      // order.user from OrderResource is always a plain name string
      const buyerName = typeof order.user === 'string'
        ? (order.user || '-')
        : (order.user?.nama ?? '-')
      details.forEach((detail: any) => {
        flat.push({
          invoice_number: order.invoice_number ?? order.kode_pesanan,
          buyer_name: buyerName,
          book_name: detail.book?.nama_buku ?? 'Buku Terhapus',
          qty: detail.qty,
          subtotal: detail.subtotal,
          payment_method: order.payment_method === 'cash' ? 'Tunai' : 'Transfer',
          payment_status: order.payment_status === 'paid' ? 'Lunas' : 'Belum Lunas',
          tanggal: order.created_at_date ?? order.created_at ?? '-',
        })
      })
    })
    rows.value = flat

    // Summary
    summary.value = {
      total_transaksi:    data.total_transaksi    ?? 0,
      total_buku_terjual: data.total_buku_terjual ?? 0,
      total_pendapatan:   data.total_pendapatan   ?? 0,
    }

    // Pagination — OrderResource wraps with meta
    const meta = ordersWrapped.meta ?? {}
    pagination.value = {
      current_page: meta.current_page ?? ordersWrapped.current_page ?? 1,
      last_page:    meta.last_page    ?? ordersWrapped.last_page    ?? 1,
      per_page:     meta.per_page     ?? ordersWrapped.per_page     ?? 20,
      total:        meta.total        ?? ordersWrapped.total        ?? 0,
    }
  } catch (error) {
    console.error('Gagal mengambil laporan:', error)
    $toast.fire({ icon: 'error', title: 'Gagal memuat laporan.' })
  } finally {
    loading.value = false
  }
}

const exportReport = async (format: 'pdf' | 'excel') => {
  exporting.value[format] = true

  const params = buildParams()
  delete params.page
  delete params.per_page
  delete params.search

  try {
    const path = format === 'pdf' ? '/reports/export/pdf' : '/reports/export/excel'
    const res = await api.get(path, {
      params,
      responseType: 'blob',
    })

    const fileType = format === 'pdf'
      ? 'application/pdf'
      : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    const ext = format === 'pdf' ? 'pdf' : 'xlsx'
    const filterLabel = filters.value.filter

    const blob = new Blob([res.data], { type: fileType })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `laporan-penjualan-${filterLabel}.${ext}`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    $toast.fire({ icon: 'success', title: `Laporan ${format.toUpperCase()} berhasil diunduh.` })
  } catch (error) {
    console.error(error)
    $toast.fire({ icon: 'error', title: 'Gagal mengunduh laporan.' })
  } finally {
    exporting.value[format] = false
  }
}

onMounted(() => {
  fetchReport()
})
</script>
