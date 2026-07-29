<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <h2 class="text-3xl font-black text-slate-800 uppercase tracking-wide">Kasir / POS</h2>
    </div>

    <!-- MAIN PANEL -->
    <div class="bg-[#f0f2f5] p-8 rounded-3xl border border-slate-300 shadow-sm min-h-[450px]">
      
      <!-- STATE 1: VIEW INVOICE STRUK (After payment success) -->
      <div v-if="invoiceData" class="max-w-md mx-auto space-y-6">
        <div class="bg-white p-6 border border-slate-300 shadow-md font-mono text-[11px] text-black">
          <div class="text-center">
            <p class="font-bold">====================================</p>
            <p class="font-bold text-sm">BOOK STORE</p>
            <p class="font-bold">====================================</p>
          </div>
          
          <div class="mt-4 space-y-1">
            <p>No Invoice: {{ invoiceData.invoice_number }}</p>
            <p>Tanggal   : {{ invoiceData.created_at || formatDate(new Date()) }}</p>
          </div>

          <div class="border-t border-dashed border-black my-3"></div>

          <!-- Items List -->
          <div class="space-y-3">
            <div v-for="item in invoiceData.order_details || invoiceData.orderDetails" :key="item.id" class="space-y-0.5">
              <p class="font-bold">{{ item.book?.nama_buku || 'Buku Terhapus' }}</p>
              <div class="flex justify-between pl-2">
                <span>{{ item.qty }} x {{ formatRupiah(item.harga) }}</span>
                <span>{{ formatRupiah(item.subtotal) }}</span>
              </div>
            </div>
          </div>

          <div class="border-t border-dashed border-black my-3"></div>

          <!-- Financial Calculation -->
          <div class="space-y-1">
            <div class="flex justify-between">
              <span>Subtotal</span>
              <span>{{ formatRupiah(invoiceData.total) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Diskon</span>
              <span>Rp0</span>
            </div>
            <div class="flex justify-between font-bold">
              <span>Total</span>
              <span>{{ formatRupiah(invoiceData.total) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Bayar</span>
              <span>{{ formatRupiah(invoiceData.amount_paid) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Kembali</span>
              <span>{{ formatRupiah(invoiceData.change_amount) }}</span>
            </div>
          </div>

          <div class="border-t border-dashed border-black my-3"></div>

          <div class="text-center space-y-1">
            <p class="font-bold">Status : LUNAS</p>
            <p>Terima kasih telah berbelanja</p>
            <p class="font-bold">====================================</p>
          </div>
        </div>

        <!-- Cashier Actions after Payment -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col gap-3">
          <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider text-center border-b pb-2 mb-2">Aksi Kasir</h4>
          <div class="grid grid-cols-2 gap-2">
            <!-- Print Struk Thermal -->
            <button @click="printThermalReceipt(invoiceData)" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-1.5 transition-colors shadow-sm">
              <Icon name="mdi:printer" />
              Print Struk
            </button>

            <!-- Download Invoice PDF -->
            <button @click="downloadInvoiceDirect(invoiceData)" :disabled="downloading[invoiceData.id]" class="px-4 py-2.5 bg-[#2E5AA7] hover:bg-[#254b8c] disabled:opacity-60 text-white rounded-lg text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-1.5 transition-colors shadow-sm">
              <span v-if="downloading[invoiceData.id]" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <Icon v-else name="mdi:file-pdf-box" />
              Invoice PDF
            </button>

            <!-- Download Struk PDF -->
            <button @click="downloadReceiptPdf(invoiceData)" :disabled="downloadingReceipt[invoiceData.id]" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 disabled:opacity-60 text-white rounded-lg text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-1.5 transition-colors shadow-sm">
              <span v-if="downloadingReceipt[invoiceData.id]" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <Icon v-else name="mdi:receipt" />
              Struk PDF
            </button>

            <!-- Lihat Invoice Digital -->
            <NuxtLink :to="`/invoice/${invoiceData.invoice_number}`" target="_blank" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-lg text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-1.5 text-center transition-colors shadow-sm">
              <Icon name="mdi:link-variant" />
              Invoice Digital
            </NuxtLink>

            <!-- Reset Kasir -->
            <button @click="invoiceData = null; fetchOrders()" class="col-span-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-1.5 transition-colors shadow-sm">
              <Icon name="mdi:cash-register" />
              Transaksi Baru
            </button>
          </div>
        </div>
      </div>

      <!-- STATE 2: VIEW DETAIL & CONFIRM PAYMENT PANEL (Kode Pesanan : A021) -->
      <div v-else-if="selectedOrder" class="space-y-6">
        <!-- Back button -->
        <button @click="selectedOrder = null" class="flex items-center text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors uppercase tracking-wider">
          <Icon name="mdi:arrow-left" class="mr-1 text-sm" />
          Kembali ke Daftar
        </button>

        <div class="bg-white p-6 rounded-3xl border border-slate-300 shadow-sm">
          <!-- Step A: Order Summary (Pesanan anda view) -->
          <div v-if="paymentStep === 'summary'" class="space-y-6">
            <div class="flex items-center justify-between border-b border-slate-200 pb-4">
              <h3 class="text-lg font-black text-slate-800 uppercase font-sans">Pesanan Anda</h3>
              <span class="text-xs font-bold text-slate-500 bg-slate-100 border px-3 py-1.5 rounded-lg font-mono">Invoice: {{ selectedOrder.invoice_number }}</span>
            </div>

            <!-- Table items with solid black outline -->
            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
              <table class="w-full border-collapse">
                <thead>
                  <tr class="bg-slate-100 border-b border-slate-200 text-xs font-black text-slate-900 font-sans uppercase">
                    <th class="border-r border-slate-200 px-4 py-3 text-center">Kode Transaksi</th>
                    <th class="border-r border-slate-200 px-4 py-3 text-left">Judul Buku</th>
                    <th class="border-r border-slate-200 px-4 py-3 text-center">Tanggal Pembelian</th>
                    <th class="border-r border-slate-200 px-4 py-3 text-center">Jumlah Pesanan</th>
                    <th class="border-r border-slate-200 px-4 py-3 text-right">Harga Satuan</th>
                    <th class="px-4 py-3 text-right">Total Harga</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in selectedOrder.order_details" :key="item.id" class="border-b border-slate-200 last:border-b-0 text-xs font-bold text-slate-800">
                    <td class="border-r border-slate-200 px-4 py-3 text-center font-mono">{{ selectedOrder.kode_pesanan }}</td>
                    <td class="border-r border-slate-200 px-4 py-3 text-left">{{ item.book?.nama_buku || 'Buku Terhapus' }}</td>
                    <td class="border-r border-slate-200 px-4 py-3 text-center">{{ selectedOrder.created_at }}</td>
                    <td class="border-r border-slate-200 px-4 py-3 text-center">{{ item.qty }}</td>
                    <td class="border-r border-slate-200 px-4 py-3 text-right">{{ formatRupiah(item.harga) }}</td>
                    <td class="px-4 py-3 text-right">{{ formatRupiah(item.subtotal) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between pt-4 gap-4">
              <div class="text-xl font-black text-slate-900 font-sans">
                Total Harga : <span class="text-blue-600">{{ formatRupiah(selectedOrder.total) }}</span>
              </div>
              <button @click="paymentStep = 'pay'" class="px-8 py-3 bg-[#2E5AA7] hover:bg-[#254b8c] text-white font-extrabold rounded-xl shadow-md active:scale-95 transition-all duration-200 uppercase text-xs tracking-wider">
                KONFIRMASI
              </button>
            </div>
          </div>

          <!-- Step B: Cash payment entry -->
          <div v-else-if="paymentStep === 'pay'" class="space-y-6">
            <div class="bg-blue-100 border border-blue-300 p-4 rounded-xl">
              <h3 class="text-md font-black text-blue-900 font-sans text-center">Kode Pesanan : {{ selectedOrder.kode_pesanan }}</h3>
            </div>

            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
              <table class="w-full border-collapse">
                <thead>
                  <tr class="bg-slate-100 border-b border-slate-200 text-xs font-black text-slate-900 font-sans uppercase">
                    <th class="border-r border-slate-200 px-4 py-3 text-left">Judul Buku</th>
                    <th class="border-r border-slate-200 px-4 py-3 text-center">Tanggal Pembelian</th>
                    <th class="border-r border-slate-200 px-4 py-3 text-center">QTY</th>
                    <th class="border-r border-slate-200 px-4 py-3 text-right">Harga Satuan</th>
                    <th class="px-4 py-3 text-right">Total Harga</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in selectedOrder.order_details" :key="item.id" class="border-b border-slate-200 last:border-b-0 text-xs font-bold text-slate-800">
                    <td class="border-r border-slate-200 px-4 py-3 text-left">{{ item.book?.nama_buku || 'Buku Terhapus' }}</td>
                    <td class="border-r border-slate-200 px-4 py-3 text-center">{{ selectedOrder.created_at }}</td>
                    <td class="border-r border-slate-200 px-4 py-3 text-center">{{ item.qty }}</td>
                    <td class="border-r border-slate-200 px-4 py-3 text-right">{{ formatRupiah(item.harga) }}</td>
                    <td class="px-4 py-3 text-right">{{ formatRupiah(item.subtotal) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Cash & Change Input Form -->
            <form @submit.prevent="submitConfirmPayment" class="space-y-4 max-w-md mx-auto pt-6 border-t border-slate-200">
              <div class="flex items-center gap-4">
                <label class="w-32 text-sm font-black text-slate-800">Total Tagihan</label>
                <input :value="formatRupiah(selectedOrder.total)" type="text" readonly
                  class="flex-1 px-4 py-2.5 border border-slate-300 rounded-lg bg-slate-50 text-lg font-black text-slate-900 cursor-not-allowed" />
              </div>

              <div class="flex items-center gap-4">
                <label class="w-32 text-sm font-black text-slate-800">Nominal Dibayar</label>
                <input 
                  :value="formatThousand(cashInput)" 
                  @input="e => cashInput = parseThousand((e.target as HTMLInputElement).value)" 
                  type="text" 
                  required 
                  placeholder="0"
                  class="flex-1 px-4 py-2.5 border-2 border-slate-200 rounded-lg text-lg font-black text-slate-800" 
                  :disabled="confirmModal.loading" 
                />
              </div>

              <div class="flex items-center gap-4">
                <label class="w-32 text-sm font-black text-slate-800">Kembalian</label>
                <input :value="formatRupiah(Math.max(0, cashInput - selectedOrder.total))" type="text" readonly
                  class="flex-1 px-4 py-2.5 border border-slate-200 rounded-lg bg-slate-50 text-lg font-black text-emerald-700 cursor-not-allowed" />
              </div>

              <!-- Pesan Kurang / Validasi Realtime -->
              <div v-if="cashInput < selectedOrder.total" class="bg-red-50 text-red-600 px-4 py-2 rounded-xl text-center text-xs font-bold border border-red-200">
                Nominal pembayaran kurang.
              </div>

              <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="selectedOrder = null" class="px-5 py-2.5 border border-slate-400 rounded-lg text-slate-600 font-bold text-sm">
                  Batal
                </button>
                <button type="submit" class="px-8 py-2.5 bg-[#2E5AA7] hover:bg-[#254b8c] text-white font-extrabold rounded-lg shadow-md active:scale-95 transition-all duration-200 text-sm uppercase"
                  :disabled="confirmModal.loading || cashInput < selectedOrder.total">
                  <span v-if="confirmModal.loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                  <span v-else>Selesaikan Pembayaran</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- STATE 3: GENERAL ORDERS LIST -->
      <div v-else>
        <!-- Top Search Box -->
        <div class="flex items-center justify-between mb-8 gap-3 flex-wrap">
          <div class="relative max-w-xs w-full">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
              <Icon name="mdi:magnify" class="text-lg" />
            </span>
            <input v-model="filters.search" @input="debounceSearch" type="text" placeholder="Cari Kode / Invoice..."
              class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-xl text-sm bg-white placeholder-slate-400 focus:outline-none" />
          </div>

          <!-- Barcode Scan Button -->
          <button @click="openBarcodeScanner"
            class="flex items-center gap-2 px-5 py-2 bg-white border-2 border-dashed border-blue-400 hover:border-blue-600 hover:bg-blue-50 text-blue-700 font-black text-sm rounded-xl transition-all duration-200 uppercase tracking-wide shadow-sm">
            <Icon name="mdi:barcode-scan" class="text-xl" />
            Klik &amp; Scan Barcode...
          </button>

          <!-- Status Dropdown filter -->
          <div>
            <select v-model="filters.status" @change="fetchOrders"
              class="px-4 py-2 border border-slate-300 rounded-xl text-sm bg-white text-slate-700 focus:outline-none">
              <option :value="null">Semua Status</option>
              <option value="pending">Pending</option>
              <option value="diproses">Diproses</option>
              <option value="selesai">Selesai</option>
              <option value="batal">Batal</option>
            </select>
          </div>
        </div>

        <!-- Orders Table (Simple clean list) -->
        <div class="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-slate-100 border-b border-slate-200 text-xs font-black text-slate-900 font-sans uppercase">
                <th class="border-r border-slate-200 px-6 py-4 text-center">Invoice / Kode</th>
                <th class="border-r border-slate-200 px-6 py-4 text-left">Pembeli</th>
                <th class="border-r border-slate-200 px-6 py-4 text-right">Total</th>
                <th class="border-r border-slate-200 px-6 py-4 text-center">Status Bayar</th>
                <th class="border-r border-slate-200 px-6 py-4 text-center">Status Pesanan</th>
                <th class="border-r border-slate-200 px-6 py-4 text-center">Tanggal</th>
                <th class="px-6 py-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="7" class="text-center py-12">
                  <div class="flex justify-center items-center gap-2 text-slate-500 font-bold">
                    <span class="w-5 h-5 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></span>
                    <span>Memuat data...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="orders.length === 0">
                <td colspan="7" class="text-center py-12 text-slate-400 font-semibold">
                  Belum ada pesanan yang ditemukan.
                </td>
              </tr>
              <tr v-else v-for="order in orders" :key="order.id" class="border-b border-slate-200 last:border-b-0 text-sm font-bold text-slate-800">
                <td class="border-r border-slate-200 px-6 py-4 text-center font-mono text-primary-700">
                  <span class="block font-black">{{ order.invoice_number || '-' }}</span>
                  <span class="text-[10px] text-slate-400">Kode: #{{ order.kode_pesanan }}</span>
                </td>
                <td class="border-r border-slate-200 px-6 py-4 text-left">
                  <p class="font-bold text-slate-800">{{ order.user || '-' }}</p>
                  <p class="text-xs text-slate-400 font-normal mt-0.5">{{ order.user_email || '-' }}</p>
                </td>
                <td class="border-r border-slate-200 px-6 py-4 text-right text-blue-600 font-black">{{ formatRupiah(order.total) }}</td>
                <td class="border-r border-slate-200 px-6 py-4 text-center">
                  <span :class="order.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'" class="badge border py-1 px-2.5 rounded-full text-xs font-bold">
                    {{ order.payment_status_label }}
                  </span>
                </td>
                <td class="border-r border-slate-200 px-6 py-4 text-center">
                  <span :class="statusColor(order.status)" class="badge capitalize">
                    {{ order.status }}
                  </span>
                </td>
                <td class="border-r border-slate-200 px-6 py-4 text-center text-xs text-slate-400 font-semibold">{{ order.created_at }}</td>
                <td class="px-6 py-4 text-center">
                  <div class="flex items-center justify-center gap-3">
                    <!-- Confirm/View detailed payment -->
                    <button v-if="order.payment_status !== 'paid'" @click="triggerScannerForOrder(order.kode_pesanan)" class="px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-lg text-xs font-bold flex items-center gap-1 transition-all" title="Konfirmasi & Bayar">
                      <Icon name="mdi:cash-register" class="text-sm" />
                      Bayar
                    </button>
                    
                    <button v-else @click="showInvoiceStruk(order)" class="px-3 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 rounded-lg text-xs font-bold flex items-center gap-1 transition-all" title="Lihat Struk">
                      <Icon name="mdi:receipt" class="text-sm" />
                      Struk
                    </button>

                    <!-- Guarded: Direct PDF Download only if Lunas (paid) -->
                    <button :disabled="order.payment_status !== 'paid'" @click="downloadInvoiceDirect(order)"
                      class="p-1 hover:text-purple-600 disabled:opacity-30 disabled:cursor-not-allowed transition-colors" title="Invoice PDF">
                      <Icon name="mdi:file-pdf-box" class="text-2xl text-slate-700 hover:text-purple-600" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Bulat Hitam -->
        <div v-if="pagination.last_page > 1" class="mt-8 flex justify-end gap-2">
          <button
            v-for="page in pagination.last_page"
            :key="page"
            @click="changePage(page)"
            class="w-8 h-8 rounded-full font-black text-xs transition-all flex items-center justify-center"
            :class="pagination.current_page === page
              ? 'bg-black text-white'
              : 'bg-slate-200 text-slate-800 hover:bg-slate-300'"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Barcode Scanner Modal -->
  <Transition name="fade">
    <div v-if="barcodeModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm">
      <div class="bg-white w-full max-w-md rounded-3xl border border-slate-200 shadow-2xl overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-[#2E5AA7] to-blue-500 p-5 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <Icon name="mdi:barcode-scan" class="text-2xl text-white" />
            <div>
              <h3 class="text-base font-black text-white">Scan Barcode Pesanan</h3>
              <p class="text-blue-200 text-xs">Arahkan kamera ke barcode pada struk pesanan customer</p>
            </div>
          </div>
          <button @click="closeBarcodeScanner" class="text-white/70 hover:text-white transition-colors">
            <Icon name="mdi:close" class="text-xl" />
          </button>
        </div>

        <!-- Scan Area -->
        <div class="p-6 space-y-5">
          <!-- Manual input option -->
          <div class="space-y-2">
            <label class="text-xs font-black text-slate-500 uppercase tracking-wider">Input Kode Pesanan Manual</label>
            <div class="flex gap-2">
              <input
                v-model="barcodeModal.manualInput"
                @keyup.enter="processBarcodeScan"
                type="text"
                placeholder="Contoh: A014, A001..."
                class="flex-1 px-4 py-2.5 border-2 border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:border-blue-400 focus:outline-none uppercase tracking-wider"
                autofocus
                ref="barcodeInputRef"
              />
              <button
                @click="processBarcodeScan"
                :disabled="!barcodeModal.manualInput.trim()"
                class="px-4 py-2.5 bg-[#2E5AA7] hover:bg-[#254b8c] disabled:opacity-50 text-white font-black text-sm rounded-xl uppercase transition-colors flex items-center gap-1.5"
              >
                <Icon name="mdi:magnify" />
                Cari
              </button>
            </div>
          </div>

          <!-- Camera Scan Area -->
          <div class="border-2 border-dashed border-slate-200 rounded-2xl overflow-hidden bg-slate-50">
            <div v-if="!barcodeModal.cameraActive" class="flex flex-col items-center justify-center py-10 gap-3">
              <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center border-2 border-blue-100">
                <Icon name="mdi:camera" class="text-3xl text-blue-400" />
              </div>
              <p class="text-sm font-bold text-slate-500">Klik tombol di bawah untuk scan via kamera</p>
              <button
                @click="startCamera"
                class="px-5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl font-black text-xs uppercase tracking-wider transition-colors flex items-center gap-2"
              >
                <Icon name="mdi:barcode-scan" class="text-base" />
                Aktifkan Kamera
              </button>
            </div>

            <!-- Video feed -->
            <div v-else class="relative overflow-hidden bg-black rounded-2xl">
              <div id="scanner-reader" class="w-full min-h-[240px]"></div>
              <div class="absolute bottom-3 right-3 z-10">
                <button @click="stopCamera" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-md transition-colors">
                  Stop Kamera
                </button>
              </div>
            </div>
          </div>

          <!-- Error / Result message -->
          <div v-if="barcodeModal.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-bold text-center">
            <Icon name="mdi:alert-circle" class="text-base mr-1" />
            {{ barcodeModal.error }}
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 pb-6 flex justify-end">
          <button @click="closeBarcodeScanner" class="px-5 py-2 border border-slate-300 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-50 transition-colors">
            Batal
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: 'admin', ssr: false })

useHead({
  script: [
    { src: 'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js', defer: true }
  ]
})

const api = useApi()
const { $toast } = useNuxtApp() as any

const orders = ref<any[]>([])
const loading = ref(true)
const downloading = ref<Record<number, boolean>>({})

const filters = ref({
  search: '',
  status: null as string | null
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0
})

const confirmModal = ref({
  loading: false
})

const selectedOrder = ref<any | null>(null)
const paymentStep = ref<'summary' | 'pay'>('summary')
const cashInput = ref<number>(0)
const invoiceData = ref<any | null>(null)
const downloadingReceipt = ref<Record<number, boolean>>({})

// ── Barcode Scanner ────────────────────────────────────────────────
const barcodeModal = ref({
  show: false,
  cameraActive: false,
  manualInput: '',
  error: ''
})
const barcodeInputRef = ref<HTMLInputElement | null>(null)
let html5QrCode: any = null

const openBarcodeScanner = () => {
  barcodeModal.value = { show: true, cameraActive: false, manualInput: '', error: '' }
  nextTick(() => {
    barcodeInputRef.value?.focus()
  })
}

const closeBarcodeScanner = () => {
  stopCamera()
  barcodeModal.value = { show: false, cameraActive: false, manualInput: '', error: '' }
}

const startCamera = async () => {
  barcodeModal.value.error = ''
  barcodeModal.value.cameraActive = true
  
  nextTick(async () => {
    try {
      if (typeof (window as any).Html5Qrcode === 'undefined') {
        barcodeModal.value.error = 'Library scanner belum termuat. Silakan tunggu.'
        barcodeModal.value.cameraActive = false
        return
      }

      html5QrCode = new (window as any).Html5Qrcode("scanner-reader")
      
      const qrCodeSuccessCallback = (decodedText: string) => {
        // Langsung proses hasil scan - jangan update manualInput dulu biar tidak trigger ulang
        processBarcodeScan(decodedText)
      }

      const config = { 
        fps: 10, 
        qrbox: (width: number, height: number) => {
          return { width: Math.min(width * 0.8, 250), height: Math.min(height * 0.5, 120) }
        }
      }

      await html5QrCode.start(
        { facingMode: "environment" }, 
        config, 
        qrCodeSuccessCallback,
        (errorMessage: string) => {
          // Frame processing failure (silent)
        }
      )
    } catch (err: any) {
      console.error(err)
      barcodeModal.value.error = 'Tidak dapat mengakses kamera. Pastikan izin kamera telah diberikan.'
      barcodeModal.value.cameraActive = false
    }
  })
}

const stopCamera = async () => {
  if (html5QrCode && html5QrCode.isScanning) {
    try {
      await html5QrCode.stop()
    } catch (e) {
      console.error(e)
    }
  }
  barcodeModal.value.cameraActive = false
}

let isProcessingScan = false  // lock mencegah double-call dari kamera scanner

const processBarcodeScan = async (rawInput?: string) => {
  // rawInput dari kamera scanner, jika tidak ada pakai manualInput
  const kode = (rawInput ?? barcodeModal.value.manualInput).trim().toUpperCase()
  if (!kode || isProcessingScan) return

  isProcessingScan = true
  barcodeModal.value.error = ''

  // Update tampilan input dengan nilai yang sedang dicari
  if (!rawInput) barcodeModal.value.manualInput = kode

  try {
    // Cari pesanan berdasarkan kode_pesanan ATAU invoice_number (backend mendukung keduanya)
    const res = await api.get('/orders', {
      params: { search: kode, per_page: 5 }
    })
    const list: any[] = res.data.data.data ?? []

    // Cari match paling tepat: exact kode_pesanan atau invoice_number
    const found = list.find(
      (o: any) =>
        o.kode_pesanan?.toUpperCase() === kode ||
        o.invoice_number?.toUpperCase() === kode
    ) ?? list[0]  // fallback ke hasil pertama jika tidak ada exact match

    if (!found) {
      barcodeModal.value.error = `Pesanan "${kode}" tidak ditemukan.`
      return
    }

    // Stop kamera jika aktif sebelum pindah halaman
    await stopCamera()
    closeBarcodeScanner()

    if (found.payment_status === 'paid') {
      showInvoiceStruk(found)
    } else {
      startPaymentFlow(found)
    }
  } catch {
    barcodeModal.value.error = 'Gagal mencari pesanan. Periksa koneksi dan coba lagi.'
  } finally {
    isProcessingScan = false
  }
}

const triggerScannerForOrder = (kode: string) => {
  openBarcodeScanner()
  barcodeModal.value.manualInput = kode
}
// ──────────────────────────────────────────────────────────────────

const showInvoiceStruk = (order: any) => {
  invoiceData.value = order
}

const startPaymentFlow = (order: any) => {
  selectedOrder.value = order
  paymentStep.value = 'pay'
  cashInput.value = order.total
}

const submitConfirmPayment = async () => {
  if (!selectedOrder.value) return
  confirmModal.value.loading = true
  try {
    const res = await api.post(`/orders/${selectedOrder.value.id}/confirm`, {
      cash: cashInput.value
    })

    invoiceData.value = res.data.data
    selectedOrder.value = null
    $toast.fire({ icon: 'success', title: 'Pembayaran Lunas!' })
  } catch (error: any) {
    const msg = error.response?.data?.message || 'Gagal mengonfirmasi pembayaran.'
    $toast.fire({ icon: 'error', title: msg })
  } finally {
    confirmModal.value.loading = false
  }
}

const downloadInvoiceDirect = async (order: any) => {
  if (order.payment_status !== 'paid') {
    $toast.fire({ icon: 'warning', title: 'Invoice PDF hanya bisa diunduh setelah status lunas!' })
    return
  }

  downloading.value[order.id] = true
  try {
    const res = await api.get(`/orders/${order.id}/invoice`, { responseType: 'blob' })
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    const filename = order.invoice_number ?? order.kode_pesanan
    link.setAttribute('download', `Invoice-${filename}.pdf`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
    $toast.fire({ icon: 'success', title: 'Invoice PDF berhasil diunduh.' })
  } catch (error) {
    console.error(error)
    $toast.fire({ icon: 'error', title: 'Gagal mengunduh Invoice PDF.' })
  } finally {
    downloading.value[order.id] = false
  }
}

const downloadReceiptPdf = async (order: any) => {
  if (order.payment_status !== 'paid') {
    $toast.fire({ icon: 'warning', title: 'Struk hanya bisa diunduh setelah status lunas!' })
    return
  }

  downloadingReceipt.value[order.id] = true
  try {
    const res = await api.get(`/orders/${order.id}/receipt`, { responseType: 'blob' })
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    const filename = order.invoice_number ?? order.kode_pesanan
    link.setAttribute('download', `Struk-${filename}.pdf`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
    $toast.fire({ icon: 'success', title: 'Struk PDF berhasil diunduh.' })
  } catch (error) {
    console.error(error)
    $toast.fire({ icon: 'error', title: 'Gagal mengunduh Struk PDF.' })
  } finally {
    downloadingReceipt.value[order.id] = false
  }
}

const printThermalReceipt = (order: any) => {
  if (order.payment_status !== 'paid') {
    $toast.fire({ icon: 'warning', title: 'Cetak struk hanya dapat dilakukan setelah status lunas!' })
    return
  }

  const printWindow = window.open('', '_blank', 'width=350,height=600')
  if (!printWindow) return

  const itemsHtml = (order.order_details || order.orderDetails).map((detail: any) => `
    <div style="display:flex; justify-content:space-between; margin-bottom: 2px;">
      <span style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${detail.book?.nama_buku}</span>
      <span>${detail.qty} x ${formatRupiah(detail.harga)}</span>
    </div>
  `).join('')

  printWindow.document.write(`
    <html>
      <head>
        <title>Struk Pembelian - ${order.invoice_number}</title>
        <style>
          @page { size: 58mm auto; margin: 0; }
          body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            padding: 10px;
            width: 200px;
            margin: 0 auto;
          }
          .center { text-align: center; }
          .divider { border-top: 1px dashed #000; margin: 5px 0; }
          .double-divider { border-top: 2px double #000; margin: 5px 0; }
          .flex-between { display: flex; justify-content: space-between; }
          .bold { font-weight: bold; }
        </style>
      </head>
      <body>
        <div class="center bold">
          ====================================<br>
          BOOK STORE<br>
          ====================================
        </div>
        <div style="margin-top:5px;">
          No Invoice: ${order.invoice_number}<br>
          Tanggal   : ${order.created_at}
        </div>
        <div class="divider"></div>
        <div>
          ${itemsHtml}
        </div>
        <div class="divider"></div>
        <div class="flex-between">
          <span>Subtotal</span>
          <span>${formatRupiah(order.total)}</span>
        </div>
        <div class="flex-between">
          <span>Diskon</span>
          <span>Rp0</span>
        </div>
        <div class="flex-between bold">
          <span>Total</span>
          <span>${formatRupiah(order.total)}</span>
        </div>
        <div class="flex-between">
          <span>Bayar</span>
          <span>${formatRupiah(order.amount_paid)}</span>
        </div>
        <div class="flex-between">
          <span>Kembali</span>
          <span>${formatRupiah(order.change_amount)}</span>
        </div>
        <div class="divider"></div>
        <div class="center bold">
          Status : LUNAS
        </div>
        <div class="center" style="margin-top: 6px;">
          Terima kasih telah berbelanja
        </div>
        <div class="center">
          ====================================
        </div>
        <script>
          window.onload = function() {
            window.print();
            setTimeout(function() { window.close(); }, 500);
          }
        <\/script>
      </body>
    </html>
  `)
  printWindow.document.close()
}

const formatDate = (date: Date) => {
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  }).format(date)
}

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(val)
}

let searchTimeout: any = null
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchOrders()
  }, 500)
}

const fetchOrders = async () => {
  loading.value = true
  try {
    const res = await api.get('/orders', {
      params: {
        search: filters.value.search,
        status: filters.value.status,
        page: pagination.value.current_page,
        per_page: pagination.value.per_page
      }
    })
    const data = res.data.data
    orders.value = data.data
    pagination.value = {
      current_page: data.meta.current_page,
      last_page: data.meta.last_page,
      per_page: data.meta.per_page,
      total: data.meta.total,
      from: data.meta.from,
      to: data.meta.to
    }
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const formatThousand = (val: number | string | null) => {
  if (val === null || val === undefined || val === '') return ''
  const num = Number(String(val).replace(/\D/g, ''))
  if (isNaN(num)) return ''
  return new Intl.NumberFormat('id-ID').format(num)
}

const parseThousand = (val: string) => {
  const clean = val.replace(/\D/g, '')
  return clean ? Number(clean) : 0
}

const changePage = (page: number) => {
  pagination.value.current_page = page
  fetchOrders()
}

// Global keypress scanner detector
let barcodeBuffer = ''
let lastKeyTime = 0

const handleGlobalKeydown = (e: KeyboardEvent) => {
  const activeEl = document.activeElement
  if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT')) {
    if (activeEl !== barcodeInputRef.value) {
      return
    }
  }

  const currentTime = Date.now()
  if (currentTime - lastKeyTime > 50) {
    barcodeBuffer = ''
  }
  lastKeyTime = currentTime

  if (e.key === 'Enter') {
    if (barcodeBuffer.length >= 3) {
      processBarcodeScan(barcodeBuffer)
      barcodeBuffer = ''
      e.preventDefault()
    }
  } else if (e.key.length === 1) {
    barcodeBuffer += e.key
  }
}

onMounted(() => {
  fetchOrders()
  window.addEventListener('keydown', handleGlobalKeydown)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleGlobalKeydown)
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
