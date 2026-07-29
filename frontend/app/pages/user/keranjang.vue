<template>
  <div class="flex-1 flex flex-col gap-5">

    <!-- ─── HEADER ─────────────────────────────────────────────── -->
    <div class="flex items-center justify-between flex-shrink-0">
      <div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 uppercase tracking-wide">Keranjang Belanja</h2>
        <p class="text-xs text-slate-400 font-semibold mt-0.5">Centang buku yang ingin di-checkout</p>
      </div>
      <NuxtLink to="/user/transaksi"
        class="flex items-center gap-1.5 px-4 py-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 rounded-xl text-xs font-black uppercase tracking-wide transition-colors">
        <Icon name="mdi:plus" class="text-sm" /> Tambah Buku
      </NuxtLink>
    </div>

    <!-- ─── EMPTY STATE ─────────────────────────────────────────── -->
    <div v-if="cartStore.isEmpty" class="flex-1 flex flex-col items-center justify-center py-16 text-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50">
      <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-4">
        <Icon name="mdi:cart-outline" class="text-4xl text-slate-300" />
      </div>
      <h3 class="text-base font-black text-slate-600">Keranjang Masih Kosong</h3>
      <p class="text-sm text-slate-400 mt-1 max-w-xs">Pergi ke Transaksi dan klik "+ Keranjang" untuk menambahkan buku.</p>
      <NuxtLink to="/user/transaksi"
        class="mt-5 px-6 py-2.5 bg-[#2E5AA7] hover:bg-[#254b8c] text-white rounded-xl text-sm font-extrabold uppercase tracking-wide transition-colors shadow-md">
        Mulai Belanja
      </NuxtLink>
    </div>

    <!-- ─── CART + SUMMARY (marketplace layout) ────────────────── -->
    <div v-else class="flex flex-col xl:flex-row gap-5 flex-1 min-h-0">

      <!-- Left: Item List -->
      <div class="flex-1 min-w-0 flex flex-col gap-3">
        <!-- Select All Bar -->
        <div class="flex items-center justify-between bg-white border border-slate-200 rounded-2xl px-4 py-3 shadow-sm">
          <label class="flex items-center gap-3 cursor-pointer select-none">
            <input
              type="checkbox"
              :checked="cartStore.allSelected"
              :indeterminate="!cartStore.allSelected && !cartStore.noneSelected"
              @change="cartStore.toggleSelectAll()"
              class="w-5 h-5 rounded accent-blue-600 cursor-pointer"
            />
            <span class="text-sm font-black text-slate-700">Pilih Semua ({{ cartStore.items.length }} buku)</span>
          </label>
          <button
            @click="cartStore.clearCart()"
            class="flex items-center gap-1 text-xs font-bold text-red-400 hover:text-red-600 hover:bg-red-50 px-2.5 py-1.5 rounded-lg transition-colors"
          >
            <Icon name="mdi:trash-can-outline" class="text-sm" />
            Hapus Semua
          </button>
        </div>

        <!-- Item Cards -->
        <div class="space-y-3 overflow-y-auto flex-1" style="scrollbar-width: thin;">
          <div
            v-for="item in cartStore.items"
            :key="item.book_id"
            class="flex gap-3 p-3 sm:p-4 bg-white border-2 rounded-2xl transition-all shadow-sm hover:shadow-md cursor-pointer"
            :class="item.selected ? 'border-blue-300 bg-blue-50/50' : 'border-slate-200'"
            @click="cartStore.toggleSelected(item.book_id)"
          >
            <!-- Checkbox -->
            <div class="flex items-center flex-shrink-0 pt-1" @click.stop>
              <input
                type="checkbox"
                :checked="item.selected"
                @change="cartStore.toggleSelected(item.book_id)"
                class="w-5 h-5 rounded accent-blue-600 cursor-pointer"
              />
            </div>

            <!-- Book cover -->
            <div class="w-16 h-20 sm:w-20 sm:h-28 flex-shrink-0 rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
              <img v-if="item.gambar_url" :src="item.gambar_url" class="w-full h-full object-cover" alt="" />
              <div v-else class="w-full h-full flex items-center justify-center">
                <Icon name="mdi:book-open-page-variant" class="text-2xl text-slate-300" />
              </div>
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0 flex flex-col justify-between">
              <div>
                <h3 class="font-black text-slate-800 text-sm sm:text-base leading-snug line-clamp-2">{{ item.nama_buku }}</h3>
                <p class="text-xs text-blue-600 font-bold mt-1">{{ formatRupiah(item.harga_jual) }} / buku</p>
              </div>

              <div class="flex items-center justify-between mt-3 flex-wrap gap-2">
                <!-- Qty controls -->
                <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-xl p-1 shadow-sm" @click.stop>
                  <button
                    @click="cartStore.updateQty(item.book_id, item.qty - 1)"
                    class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-red-50 hover:text-red-500 rounded-lg transition-colors active:scale-90"
                  >
                    <Icon name="mdi:minus" class="text-sm" />
                  </button>
                  <span class="w-8 text-center text-sm font-black text-slate-700">{{ item.qty }}</span>
                  <button
                    @click="cartStore.updateQty(item.book_id, item.qty + 1)"
                    :disabled="item.qty >= item.stok"
                    class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition-colors active:scale-90 disabled:opacity-30 disabled:cursor-not-allowed"
                  >
                    <Icon name="mdi:plus" class="text-sm" />
                  </button>
                </div>

                <!-- Subtotal -->
                <div class="text-right">
                  <p class="text-[10px] text-slate-400 font-semibold">Subtotal</p>
                  <p class="text-sm font-black" :class="item.selected ? 'text-blue-700' : 'text-slate-400'">
                    {{ formatRupiah(item.subtotal) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Delete -->
            <div @click.stop>
              <button
                @click="cartStore.removeItem(item.book_id)"
                class="w-8 h-8 flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-colors self-start mt-0.5"
              >
                <Icon name="mdi:trash-can-outline" class="text-base" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Order Summary -->
      <div class="w-full xl:w-80 flex-shrink-0 xl:self-start xl:sticky xl:top-6">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
          <!-- Summary Header -->
          <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-4">
            <h3 class="font-black text-white text-sm uppercase tracking-wide">Ringkasan Pesanan</h3>
            <p class="text-blue-200 text-xs font-semibold mt-0.5">
              <span class="text-white font-black">{{ cartStore.selectedItems.length }}</span> dari {{ cartStore.items.length }} buku dipilih
            </p>
          </div>

          <div class="p-4 space-y-4">
            <!-- Selected items preview -->
            <div v-if="cartStore.selectedItems.length > 0" class="space-y-2">
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Item yang akan dicheckout</p>
              <div v-for="item in cartStore.selectedItems" :key="item.book_id"
                class="flex justify-between items-center text-xs">
                <span class="text-slate-600 font-semibold truncate max-w-[150px]">{{ item.nama_buku }} (x{{ item.qty }})</span>
                <span class="text-slate-800 font-black flex-shrink-0 ml-2">{{ formatRupiah(item.subtotal) }}</span>
              </div>
            </div>

            <!-- Warning if none selected -->
            <div v-if="cartStore.noneSelected" class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl">
              <Icon name="mdi:information-outline" class="text-amber-500 text-base flex-shrink-0 mt-0.5" />
              <p class="text-xs font-bold text-amber-700 leading-snug">Centang buku yang ingin di-checkout terlebih dahulu</p>
            </div>

            <!-- Price breakdown -->
            <div class="border-t border-slate-100 pt-3 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-slate-500 font-semibold">Subtotal</span>
                <span class="font-black text-slate-800">{{ formatRupiah(cartStore.selectedTotal) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-slate-500 font-semibold">Metode Bayar</span>
                <span class="font-black text-slate-800 uppercase text-xs">Cash</span>
              </div>
              <div class="h-px bg-slate-200" />
              <div class="flex justify-between">
                <span class="font-black text-slate-800 text-sm">Total</span>
                <span class="font-black text-blue-700 text-lg">{{ formatRupiah(cartStore.selectedTotal) }}</span>
              </div>
            </div>

            <!-- Checkout Button -->
            <button
              @click="submitOrder"
              :disabled="submitting || cartStore.noneSelected"
              class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white rounded-2xl font-extrabold text-sm uppercase tracking-wider transition-all shadow-lg shadow-blue-500/30 active:scale-95 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="submitting" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
              <template v-else>
                <Icon name="mdi:lock-check-outline" class="text-lg" />
                Checkout ({{ cartStore.selectedItems.length }} buku)
              </template>
            </button>

            <p class="text-center text-[10px] text-slate-400 font-semibold">
              Setelah checkout, Anda akan mendapatkan barcode untuk diserahkan ke kasir.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── CHECKOUT SUCCESS MODAL ──────────────────────────────── -->
    <Transition name="fade">
      <div v-if="checkoutSuccessModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl relative my-6 overflow-hidden">
          <!-- Success header -->
          <div class="bg-gradient-to-br from-emerald-400 to-emerald-600 p-6 text-center">
            <div class="w-16 h-16 bg-white/25 rounded-full flex items-center justify-center mx-auto mb-3">
              <Icon name="mdi:check-circle" class="text-5xl text-white" />
            </div>
            <h3 class="text-xl font-black text-white">Pesanan Berhasil!</h3>
            <p class="text-emerald-100 text-xs mt-1 font-semibold">Tunjukkan barcode ke kasir untuk pembayaran</p>
          </div>
          <div class="p-5 space-y-4">
            <!-- Order details -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-2.5">
              <div class="flex justify-between items-center">
                <span class="text-slate-500 font-bold text-xs uppercase tracking-wider">Invoice</span>
                <span class="font-black text-slate-800 font-mono text-xs">{{ checkoutSuccessModal.order?.invoice_number }}</span>
              </div>
              <div class="h-px bg-slate-200" />
              <div class="flex justify-between items-center">
                <span class="text-slate-500 font-bold text-xs uppercase tracking-wider">Kode Pesanan</span>
                <span class="font-black text-slate-800 font-mono text-xs">{{ checkoutSuccessModal.order?.kode_pesanan }}</span>
              </div>
              <div class="h-px bg-slate-200" />
              <div class="flex justify-between items-center">
                <span class="text-slate-500 font-bold text-xs uppercase tracking-wider">Total</span>
                <span class="font-black text-blue-600 text-base">{{ formatRupiah(checkoutSuccessModal.order?.total || 0) }}</span>
              </div>
            </div>
            <!-- Barcode -->
            <div class="bg-white border-2 border-dashed border-slate-200 rounded-2xl p-4 flex flex-col items-center">
              <svg id="keranjang-barcode-svg" class="w-full max-h-24" />
              <p class="font-mono font-black text-slate-700 tracking-widest text-sm mt-1.5">{{ checkoutSuccessModal.order?.kode_pesanan }}</p>
            </div>
            <!-- Actions -->
            <div class="flex gap-2.5">
              <button @click="closeSuccessModal"
                class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl font-black text-xs uppercase tracking-wide active:scale-95 transition-all">
                Belanja Lagi
              </button>
              <NuxtLink to="/user/riwayat" @click="closeSuccessModal"
                class="flex-1 py-3 bg-[#2E5AA7] text-white rounded-2xl font-black text-xs uppercase tracking-wide text-center active:scale-95 transition-all flex items-center justify-center gap-1.5">
                <Icon name="mdi:history" class="text-sm" /> Riwayat
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup lang="ts">
useHead({
  script: [{ src: 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js', defer: true }]
})
definePageMeta({ layout: 'user', middleware: 'auth' })

const cartStore = useCartStore()
const api = useApi()
const { $toast } = useNuxtApp() as any

const submitting = ref(false)
const checkoutSuccessModal = ref({ show: false, order: null as any })

const formatRupiah = (val: number) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val)

const generateBarcode = async (kode: string) => {
  await nextTick()
  await new Promise(r => setTimeout(r, 200))
  let attempts = 0
  const iv = setInterval(() => {
    attempts++
    const el = document.getElementById('keranjang-barcode-svg')
    const JsB = (window as any).JsBarcode
    if (el && typeof JsB !== 'undefined') {
      try {
        JsB('#keranjang-barcode-svg', kode, {
          format: 'CODE128', width: 3, height: 100, displayValue: false, margin: 10
        })
        clearInterval(iv)
      } catch (e) { console.error(e) }
    }
    if (attempts >= 15) clearInterval(iv)
  }, 200)
}

const submitOrder = async () => {
  if (cartStore.noneSelected) return
  submitting.value = true
  try {
    const payload = {
      items: cartStore.selectedItems.map(i => ({ book_id: i.book_id, qty: i.qty }))
    }
    const res = await api.post('/orders', payload)
    const order = res.data.data
    cartStore.clearSelected()   // hanya hapus item yang di-checkout
    checkoutSuccessModal.value = { show: true, order }
    generateBarcode(order.kode_pesanan)
    $toast.fire({ icon: 'success', title: `Pesanan ${order.invoice_number} berhasil dibuat!` })
  } catch (error: any) {
    $toast.fire({ icon: 'error', title: error.response?.data?.message || 'Gagal mengirim pesanan.' })
  } finally { submitting.value = false }
}

const closeSuccessModal = () => { checkoutSuccessModal.value = { show: false, order: null } }
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
