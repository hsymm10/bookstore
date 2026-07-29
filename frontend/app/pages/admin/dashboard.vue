<template>
  <div class="space-y-6">

    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="page-title">Dashboard</h2>
        <p class="text-sm mt-1" style="color:#2E5AA7">Selamat datang, <strong>{{ authStore.currentUser?.nama }}</strong>!</p>
      </div>
      <div class="text-sm" style="color:#86C5FF">{{ today }}</div>
    </div>

    <!-- ─── Stat Cards ──────────────────────────────────── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
      <div v-for="stat in statCards" :key="stat.label" class="stat-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium" style="color:#2E5AA7">{{ stat.label }}</p>
            <p class="text-2xl font-black mt-2" style="color:#0e1f3d">{{ stat.value }}</p>
            <p class="text-xs mt-1" style="color:#86C5FF">{{ stat.sub }}</p>
          </div>
          <div class="w-14 h-14 rounded-2xl flex items-center justify-center" :style="{background: stat.bg}">
            <Icon :name="stat.icon" class="text-2xl" :style="{color: stat.iconColor}" />
          </div>
        </div>
        <!-- Progress bar -->
        <div class="mt-4 h-1.5 rounded-full overflow-hidden" style="background:#daeeff">
          <div class="h-full rounded-full transition-all duration-1000"
            :style="{width: stat.progress + '%', background: stat.barColor}" />
        </div>
      </div>
    </div>

    <!-- ─── Chart + Best Seller ────────────────────────── -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

      <!-- Grafik Penjualan -->
      <div class="xl:col-span-2 card p-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="font-bold" style="color:#17305c">Grafik Penjualan</h3>
            <p class="text-sm mt-0.5" style="color:#86C5FF">Pendapatan per bulan {{ new Date().getFullYear() }}</p>
          </div>
          <span class="badge-primary">{{ new Date().getFullYear() }}</span>
        </div>
        <div v-if="!loading" class="h-64">
          <LineChart :data="chartData" :options="chartOptions" />
        </div>
        <div v-else class="h-64 flex items-center justify-center">
          <div class="w-8 h-8 border-2 border-t-transparent rounded-full animate-spin" style="border-color:#FFA62B; border-top-color:transparent" />
        </div>
      </div>

      <!-- Best Seller -->
      <div class="card p-6">
        <h3 class="font-bold mb-4 flex items-center gap-2" style="color:#17305c">
          <Icon name="mdi:trophy-outline" class="text-citrus-500 text-xl" style="color:#FFA62B" />
          Best Seller
        </h3>
        <div class="space-y-3">
          <div v-if="loading">
            <div v-for="i in 4" :key="i" class="flex items-center gap-3 animate-pulse">
              <div class="w-10 h-10 rounded-xl flex-shrink-0" style="background:#daeeff" />
              <div class="flex-1">
                <div class="h-3 rounded mb-1.5" style="background:#daeeff" />
                <div class="h-2.5 rounded w-2/3" style="background:#daeeff" />
              </div>
            </div>
          </div>
          <div v-else v-for="(book, idx) in bestSeller" :key="book.id"
            class="flex items-center gap-3 p-2 rounded-xl transition-colors" style="hover:background:#F8E6A0">
            <div class="w-8 h-8 rounded-xl font-black text-sm flex items-center justify-center flex-shrink-0"
              :style="idx === 0 ? 'background:#fef0c7;color:#b86400' : idx === 1 ? 'background:#daeeff;color:#2E5AA7' : 'background:#f0f7ff;color:#2E5AA7'">
              {{ idx + 1 }}
            </div>
            <img v-if="book.gambar_url" :src="book.gambar_url" :alt="book.nama_buku"
              class="w-10 h-10 rounded-xl object-cover flex-shrink-0" style="border:1px solid #daeeff" />
            <div v-else class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#f0f7ff">
              <Icon name="mdi:book-open-page-variant" style="color:#2E5AA7" class="text-xl" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold truncate" style="color:#17305c">{{ book.nama_buku }}</p>
              <p class="text-xs" style="color:#86C5FF">{{ book.total_terjual }} terjual</p>
            </div>
          </div>
          <div v-if="!loading && bestSeller.length === 0"
            class="text-center py-8 text-sm" style="color:#86C5FF">
            Belum ada penjualan
          </div>
        </div>
      </div>
    </div>

    <!-- ─── Pesanan Terbaru ─────────────────────────────── -->
    <div class="card p-6">
      <div class="flex items-center justify-between mb-5">
        <h3 class="font-bold flex items-center gap-2" style="color:#17305c">
          <Icon name="mdi:receipt-text-outline" class="text-xl" style="color:#FFA62B" />
          Pesanan Terbaru
        </h3>
        <NuxtLink to="/admin/kasir" class="text-sm font-semibold transition-colors" style="color:#2E5AA7">
          Lihat Semua →
        </NuxtLink>
      </div>
      <div class="table-wrapper">
        <table class="table-base">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Pembeli</th>
              <th>Total</th>
              <th>Status</th>
              <th>Tanggal</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="5" class="text-center py-8" style="color:#86C5FF">Loading...</td>
            </tr>
            <tr v-else-if="recentOrders.length === 0">
              <td colspan="5" class="text-center py-8" style="color:#86C5FF">Belum ada pesanan</td>
            </tr>
            <tr v-else v-for="order in recentOrders" :key="order.kode_pesanan">
              <td class="font-mono font-bold" style="color:#2E5AA7">{{ order.kode_pesanan }}</td>
              <td style="color:#17305c">{{ order.user }}</td>
              <td class="font-semibold" style="color:#FFA62B">{{ formatRupiah(order.total) }}</td>
              <td>
                <span :class="statusColor(order.status)" class="badge capitalize">
                  {{ order.status }}
                </span>
              </td>
              <td class="text-xs" style="color:#86C5FF">{{ order.created_at }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Line as LineChart } from 'vue-chartjs'
import {
  Chart as ChartJS, CategoryScale, LinearScale,
  PointElement, LineElement, Title, Tooltip, Legend, Filler,
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler)

definePageMeta({ layout: 'admin', middleware: 'admin', ssr: false })

const authStore = useAuthStore()
const $api      = useApi()

const loading      = ref(true)
const statCards    = ref<any[]>([])
const chartData    = ref<any>({})
const bestSeller   = ref<any[]>([])
const recentOrders = ref<any[]>([])

const today = new Intl.DateTimeFormat('id-ID', {
  weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
}).format(new Date())

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: (ctx: any) => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw),
      },
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: '#f1f5f9' },
      ticks: {
        callback: (v: any) => 'Rp ' + new Intl.NumberFormat('id-ID').format(v),
        font: { size: 10 },
      },
    },
    x: { grid: { display: false } },
  },
}

onMounted(async () => {
  try {
    const res  = await $api.get('/dashboard')
    const data = res.data.data

    const s = data.statistik
    statCards.value = [
      { label: 'Total User',       value: s.total_user,                    icon: 'mdi:account-group-outline', bg: '#f0f7ff',  iconColor: '#2E5AA7', barColor: '#2E5AA7', progress: 70, sub: 'User terdaftar' },
      { label: 'Total Buku',       value: s.total_buku,                    icon: 'mdi:bookshelf',             bg: '#fef0c7',  iconColor: '#b86400', barColor: '#FFA62B', progress: 55, sub: 'Koleksi tersedia' },
      { label: 'Total Pesanan',    value: s.total_pesanan,                 icon: 'mdi:cart-outline',          bg: '#ecfdf5',  iconColor: '#059669', barColor: '#10b981', progress: 40, sub: 'Semua transaksi' },
      { label: 'Total Pendapatan', value: formatRupiah(s.total_pendapatan), icon: 'mdi:cash-multiple',        bg: '#F8E6A0',  iconColor: '#b86400', barColor: '#FFA62B', progress: 85, sub: 'Pesanan selesai' },
    ]

    const g = data.grafik_penjualan
    chartData.value = {
      labels: g.labels,
      datasets: [{
        data: g.pendapatan,
        borderColor: '#FFA62B',
        backgroundColor: 'rgba(255,166,43,0.08)',
        borderWidth: 2.5,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#2E5AA7',
        pointRadius: 4,
        pointHoverRadius: 6,
      }],
    }

    bestSeller.value   = data.best_seller
    recentOrders.value = data.pesanan_terbaru
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})
</script>
