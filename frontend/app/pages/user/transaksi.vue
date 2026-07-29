<template>
  <div class="flex-1 flex flex-col relative overflow-hidden">

    <!-- ─── SPLIT LAYOUT ─────────────────────────────────────────────── -->
    <div class="flex flex-col xl:flex-row gap-6 xl:gap-8 flex-1 min-h-0 overflow-hidden">

      <!-- ══ CATALOG SECTION ══════════════════════════════════════════ -->
      <div class="flex-1 min-w-0 flex flex-col overflow-hidden">

        <!-- Header -->
        <div class="mb-5 flex items-center justify-between flex-shrink-0">
          <h2 class="text-xl sm:text-2xl font-black text-slate-900 uppercase tracking-wide">Silahkan Berbelanja</h2>
          <button
            v-if="!cartStore.isEmpty"
            @click="openMobileCartDrawer"
            class="xl:hidden relative flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-2xl text-xs font-black uppercase shadow-md active:scale-95 transition-all"
          >
            <Icon name="mdi:cart-outline" class="text-base" />
            <span>Keranjang ({{ cartStore.items.length }})</span>
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex-1 flex items-center justify-center py-20">
          <div class="flex flex-col items-center gap-3">
            <div class="w-12 h-12 border-4 border-[#2E5AA7] border-t-transparent rounded-full animate-spin" />
            <p class="text-sm font-semibold text-slate-500">Memuat katalog buku...</p>
          </div>
        </div>

        <!-- Empty -->
        <div v-else-if="books.length === 0" class="flex-1 flex flex-col items-center justify-center p-10 text-center bg-slate-50 rounded-2xl border border-slate-200">
          <Icon name="mdi:book-open-outline" class="text-6xl text-slate-300 mb-3" />
          <h3 class="text-base font-bold text-slate-600">Tidak ada buku tersedia</h3>
        </div>

        <!-- ── BOOK GRID ───────────────────────────────────────────── -->
        <div v-else class="flex-1 flex flex-col gap-6 overflow-y-auto pr-1" style="scrollbar-width: thin;">
          <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-2 2xl:grid-cols-3 gap-3 sm:gap-4">
            <div
              v-for="book in books" :key="book.id"
              class="bg-white border border-slate-200 rounded-2xl overflow-hidden flex flex-col shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group"
            >
              <!-- Cover — fixed uniform height -->
              <div class="relative w-full h-40 sm:h-48 flex-shrink-0 overflow-hidden bg-slate-100">
                <img v-if="book.gambar_url" :src="book.gambar_url"
                  class="w-full h-full object-cover transition-transform duration-500"
                  :class="book.stok <= 0 ? 'brightness-50' : 'group-hover:scale-105'" alt="" />
                <div v-else class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                  <Icon name="mdi:book-open-page-variant" class="text-4xl text-[#2E5AA7]/30" />
                </div>

                <div v-if="book.stok <= 0" class="absolute inset-0 flex items-center justify-center bg-black/40">
                  <div class="bg-red-600 px-4 py-1.5 rounded-xl"><p class="text-white font-black text-xs uppercase tracking-widest">HABIS</p></div>
                </div>

                <div class="absolute top-2 left-2">
                  <span class="bg-[#2E5AA7]/85 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase backdrop-blur-sm truncate max-w-[72px] block">
                    {{ book.category?.nama || 'Umum' }}
                  </span>
                </div>
                <button @click="openDetailModal(book)" class="absolute top-2 right-2 w-7 h-7 bg-white/90 rounded-full flex items-center justify-center text-slate-600 hover:text-blue-600 shadow transition-all opacity-0 group-hover:opacity-100">
                  <Icon name="mdi:magnify" class="text-sm" />
                </button>
              </div>

              <!-- Info -->
              <div class="p-3 flex flex-col flex-1 gap-2">
                <h3 class="font-black text-slate-800 text-xs sm:text-sm leading-snug line-clamp-2 min-h-[2.5em]">{{ book.nama_buku }}</h3>
                <div class="flex flex-col gap-1">
                  <span class="text-blue-700 font-black text-sm leading-none">{{ formatRupiah(book.harga_jual) }}</span>
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded-full self-start" :class="book.stok > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600'">
                    {{ book.stok > 0 ? `Stok: ${book.stok}` : 'Habis' }}
                  </span>
                </div>

                <!-- 3 Action Buttons -->
                <div class="flex flex-col gap-1.5 mt-auto pt-1">
                  <!-- Detail -->
                  <button @click="openDetailModal(book)"
                    class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-2 rounded-xl flex items-center justify-center gap-1.5 text-xs font-black uppercase transition-colors active:scale-95">
                    <Icon name="mdi:magnify" class="text-sm" /> Detail
                  </button>

                  <!-- Row: Keranjang + Beli -->
                  <div class="grid grid-cols-2 gap-1.5">
                    <!-- + Keranjang -->
                    <button
                      @click="addToCartOnly(book)"
                      :disabled="book.stok <= 0"
                      class="py-2 px-1 rounded-xl font-extrabold text-[10px] sm:text-xs uppercase transition-all active:scale-95 flex items-center justify-center gap-1"
                      :class="book.stok <= 0
                        ? 'bg-slate-200 text-slate-400 cursor-not-allowed'
                        : 'bg-slate-700 hover:bg-slate-800 text-white'"
                    >
                      <Icon name="mdi:cart-plus" class="text-sm flex-shrink-0" />
                      <span class="truncate">Keranjang</span>
                    </button>

                    <!-- Beli Sekarang -->
                    <button
                      @click="openBuyModal(book)"
                      :disabled="book.stok <= 0"
                      class="py-2 px-1 rounded-xl font-extrabold text-[10px] sm:text-xs uppercase transition-all active:scale-95 flex items-center justify-center gap-1"
                      :class="book.stok <= 0
                        ? 'bg-slate-200 text-slate-400 cursor-not-allowed'
                        : 'bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white shadow-md'"
                    >
                      <Icon name="mdi:flash" class="text-sm flex-shrink-0" />
                      <span class="truncate">Beli</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="flex justify-center gap-2 pb-4">
            <button v-for="p in totalPages" :key="p" @click="changePage(p)"
              class="w-9 h-9 rounded-full font-black text-xs transition-all active:scale-95 flex items-center justify-center"
              :class="currentPage === p ? 'bg-[#2E5AA7] text-white shadow-md' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'">
              {{ p }}
            </button>
          </div>
        </div>
      </div>

      <!-- ══ DESKTOP SIDEBAR CART (Marketplace-style) ═══════════════════ -->
      <div class="hidden xl:flex w-[360px] 2xl:w-96 flex-col self-start sticky top-6 flex-shrink-0">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
          <!-- Header -->
          <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 bg-white/20 rounded-xl flex items-center justify-center">
                <Icon name="mdi:cart-outline" class="text-white text-lg" />
              </div>
              <div>
                <h3 class="font-black text-white text-sm uppercase">Keranjang Belanja</h3>
                <p class="text-blue-200 text-xs font-semibold">{{ cartStore.items.length }} jenis buku</p>
              </div>
            </div>
          </div>

          <div class="p-4 flex flex-col gap-3">
            <!-- Empty -->
            <div v-if="cartStore.isEmpty" class="flex flex-col items-center justify-center py-8 text-center">
              <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mb-2">
                <Icon name="mdi:cart-off-outline" class="text-2xl text-slate-300" />
              </div>
              <p class="font-bold text-slate-500 text-xs">Keranjang kosong</p>
              <p class="text-[11px] text-slate-400 mt-1">Klik "+ Keranjang" untuk menambah buku, lalu centang buku yang ingin di-checkout.</p>
            </div>

            <!-- Cart Items with Checkboxes -->
            <div v-else class="space-y-2 max-h-64 overflow-y-auto pr-1" style="scrollbar-width: thin;">
              <!-- Select All row -->
              <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                  <input type="checkbox" :checked="cartStore.allSelected" @change="cartStore.toggleSelectAll()"
                    class="w-4 h-4 rounded accent-blue-600 cursor-pointer" />
                  <span class="text-xs font-black text-slate-600">Pilih Semua</span>
                </label>
                <button @click="cartStore.clearCart()" class="text-[10px] font-bold text-red-400 hover:text-red-600 transition-colors">Hapus Semua</button>
              </div>

              <!-- Each Item -->
              <div v-for="item in cartStore.items" :key="item.book_id"
                class="flex gap-2.5 p-2.5 rounded-xl border transition-colors"
                :class="item.selected ? 'bg-blue-50 border-blue-200' : 'bg-slate-50 border-slate-100'"
              >
                <!-- Checkbox -->
                <div class="flex items-start pt-0.5 flex-shrink-0">
                  <input type="checkbox" :checked="item.selected" @change="cartStore.toggleSelected(item.book_id)"
                    class="w-4 h-4 rounded accent-blue-600 cursor-pointer mt-1" />
                </div>

                <!-- Thumbnail -->
                <img v-if="item.gambar_url" :src="item.gambar_url" class="w-10 h-14 object-cover rounded-lg border border-slate-200 flex-shrink-0" alt="" />
                <div v-else class="w-10 h-14 bg-slate-200 rounded-lg flex items-center justify-center flex-shrink-0">
                  <Icon name="mdi:book-open-page-variant" class="text-base text-slate-400" />
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0 flex flex-col justify-between">
                  <h4 class="font-black text-slate-800 text-xs leading-tight line-clamp-2">{{ item.nama_buku }}</h4>
                  <p class="text-[10px] text-blue-600 font-bold">{{ formatRupiah(item.harga_jual) }}</p>
                  <div class="flex items-center justify-between mt-1">
                    <div class="flex items-center gap-0.5 bg-white rounded-lg border border-slate-200 p-0.5">
                      <button @click="cartStore.updateQty(item.book_id, item.qty - 1)"
                        class="w-5 h-5 flex items-center justify-center text-slate-500 hover:bg-red-50 hover:text-red-500 rounded-md transition-colors">
                        <Icon name="mdi:minus" class="text-[10px]" />
                      </button>
                      <span class="w-5 text-center text-xs font-black text-slate-700">{{ item.qty }}</span>
                      <button @click="cartStore.updateQty(item.book_id, item.qty + 1)" :disabled="item.qty >= item.stok"
                        class="w-5 h-5 flex items-center justify-center text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 rounded-md transition-colors disabled:opacity-30">
                        <Icon name="mdi:plus" class="text-[10px]" />
                      </button>
                    </div>
                    <span class="text-xs font-black" :class="item.selected ? 'text-blue-700' : 'text-slate-400'">{{ formatRupiah(item.subtotal) }}</span>
                  </div>
                </div>

                <!-- Delete -->
                <button @click="cartStore.removeItem(item.book_id)"
                  class="self-start w-5 h-5 flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-md transition-colors mt-0.5 flex-shrink-0">
                  <Icon name="mdi:close" class="text-xs" />
                </button>
              </div>
            </div>

            <!-- Summary & Checkout (only selected items) -->
            <div v-if="!cartStore.isEmpty" class="border-t border-slate-200 pt-3 space-y-2.5">
              <!-- Selected items count indicator -->
              <div class="flex items-center justify-between text-xs">
                <span class="text-slate-400 font-bold">
                  <span class="text-blue-600 font-black">{{ cartStore.selectedItems.length }}</span> dari {{ cartStore.items.length }} jenis dipilih
                </span>
                <span class="font-black text-blue-700">{{ formatRupiah(cartStore.selectedTotal) }}</span>
              </div>

              <div v-if="cartStore.noneSelected" class="flex items-center gap-1.5 p-2.5 bg-amber-50 border border-amber-200 rounded-xl">
                <Icon name="mdi:checkbox-marked-circle-outline" class="text-amber-500 text-sm flex-shrink-0" />
                <p class="text-[10px] font-bold text-amber-700">Centang buku yang ingin di-checkout</p>
              </div>

              <button
                @click="submitOrder"
                :disabled="submitting || cartStore.noneSelected"
                class="w-full py-3 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white rounded-2xl font-extrabold text-xs uppercase tracking-wider transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                <template v-else>
                  <Icon name="mdi:check-circle-outline" class="text-base" />
                  Checkout ({{ cartStore.selectedItems.length }} buku)
                </template>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── MOBILE FLOATING BAR ──────────────────────────────────── -->
    <Transition name="slide-up">
      <div v-if="!cartStore.isEmpty" class="xl:hidden fixed bottom-0 left-0 right-0 z-40">
        <div class="bg-white/95 backdrop-blur-xl border-t border-slate-200 shadow-2xl px-4 py-3">
          <div class="flex items-center gap-3">
            <button @click="openMobileCartDrawer" class="relative flex-shrink-0">
              <div class="w-12 h-12 bg-blue-50 border-2 border-blue-200 rounded-2xl flex items-center justify-center">
                <Icon name="mdi:cart-outline" class="text-blue-600 text-xl" />
              </div>
              <span class="absolute -top-1.5 -right-1.5 bg-blue-600 text-white font-black text-[10px] min-w-[18px] h-[18px] rounded-full flex items-center justify-center border-2 border-white px-0.5">
                {{ cartStore.items.length }}
              </span>
            </button>
            <div class="flex-1 min-w-0">
              <p class="text-[10px] uppercase font-black text-slate-400 tracking-wider leading-none">
                {{ cartStore.noneSelected ? 'Pilih item di keranjang' : `${cartStore.selectedItems.length} item dipilih` }}
              </p>
              <p class="text-base font-black leading-tight" :class="cartStore.noneSelected ? 'text-slate-400' : 'text-blue-700'">
                {{ formatRupiah(cartStore.selectedTotal) }}
              </p>
            </div>
            <button @click="openMobileCartDrawer"
              class="flex-shrink-0 flex items-center gap-2 bg-slate-700 hover:bg-slate-800 text-white px-4 py-3 rounded-2xl font-black text-xs uppercase active:scale-95 transition-all">
              <Icon name="mdi:cart-check" class="text-base" />
              Pilih
            </button>
            <button
              @click="submitOrder"
              :disabled="submitting || cartStore.noneSelected"
              class="flex-shrink-0 flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-800 text-white px-4 py-3 rounded-2xl font-black text-xs uppercase shadow-lg active:scale-95 transition-all disabled:opacity-50"
            >
              <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
              <template v-else><Icon name="mdi:flash" class="text-base" />Bayar</template>
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ─── MOBILE CART BOTTOM SHEET (Marketplace style) ──────────── -->
    <Transition name="sheet">
      <div v-if="mobileCartDrawerOpen" class="xl:hidden fixed inset-0 z-50 flex items-end" @click.self="closeMobileCartDrawer">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeMobileCartDrawer" />
        <div class="relative bg-white w-full rounded-t-3xl max-h-[90vh] flex flex-col shadow-2xl">
          <!-- Handle -->
          <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 bg-slate-300 rounded-full" />
          </div>

          <!-- Sheet Header -->
          <div class="flex items-center justify-between px-5 pb-3 pt-2 border-b border-slate-100">
            <div>
              <h3 class="font-black text-slate-800 text-base">Keranjang Belanja</h3>
              <p class="text-xs text-slate-400">Centang buku yang ingin di-checkout</p>
            </div>
            <button @click="closeMobileCartDrawer" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:bg-slate-100 rounded-xl">
              <Icon name="mdi:close" class="text-lg" />
            </button>
          </div>

          <!-- Select All -->
          <div class="flex items-center justify-between px-5 py-2.5 bg-slate-50 border-b border-slate-100">
            <label class="flex items-center gap-2.5 cursor-pointer">
              <input type="checkbox" :checked="cartStore.allSelected" @change="cartStore.toggleSelectAll()"
                class="w-5 h-5 rounded accent-blue-600 cursor-pointer" />
              <span class="text-sm font-black text-slate-700">Pilih Semua</span>
            </label>
            <button @click="cartStore.clearCart(); closeMobileCartDrawer()"
              class="text-xs font-bold text-red-400 hover:text-red-600">Hapus Semua</button>
          </div>

          <!-- Items -->
          <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3" style="scrollbar-width: none;">
            <div v-for="item in cartStore.items" :key="item.book_id"
              class="flex gap-3 p-3 rounded-2xl border-2 transition-all"
              :class="item.selected ? 'bg-blue-50 border-blue-300' : 'bg-white border-slate-100'"
            >
              <!-- Checkbox -->
              <div class="flex items-center flex-shrink-0 pt-1">
                <input type="checkbox" :checked="item.selected" @change="cartStore.toggleSelected(item.book_id)"
                  class="w-5 h-5 rounded accent-blue-600 cursor-pointer" />
              </div>

              <!-- Thumbnail -->
              <img v-if="item.gambar_url" :src="item.gambar_url" class="w-14 h-20 object-cover rounded-xl border border-slate-200 flex-shrink-0" alt="" />
              <div v-else class="w-14 h-20 bg-slate-200 rounded-xl flex items-center justify-center flex-shrink-0">
                <Icon name="mdi:book-open-page-variant" class="text-2xl text-slate-400" />
              </div>

              <!-- Info -->
              <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                <div>
                  <h4 class="font-black text-slate-800 text-sm leading-snug line-clamp-2">{{ item.nama_buku }}</h4>
                  <p class="text-xs text-blue-600 font-bold mt-1">{{ formatRupiah(item.harga_jual) }}</p>
                </div>
                <div class="flex items-center justify-between mt-2">
                  <div class="flex items-center gap-0.5 bg-white rounded-xl border border-slate-200 p-1">
                    <button @click="cartStore.updateQty(item.book_id, item.qty - 1)"
                      class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-red-50 hover:text-red-500 rounded-lg transition-colors active:scale-90">
                      <Icon name="mdi:minus" class="text-sm" />
                    </button>
                    <span class="w-8 text-center text-sm font-black text-slate-700">{{ item.qty }}</span>
                    <button @click="cartStore.updateQty(item.book_id, item.qty + 1)" :disabled="item.qty >= item.stok"
                      class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition-colors active:scale-90 disabled:opacity-30">
                      <Icon name="mdi:plus" class="text-sm" />
                    </button>
                  </div>
                  <span class="text-sm font-black" :class="item.selected ? 'text-blue-700' : 'text-slate-400'">{{ formatRupiah(item.subtotal) }}</span>
                </div>
              </div>

              <!-- Delete -->
              <button @click="cartStore.removeItem(item.book_id)"
                class="self-start w-8 h-8 flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-colors">
                <Icon name="mdi:trash-can-outline" class="text-base" />
              </button>
            </div>
          </div>

          <!-- Footer -->
          <div class="border-t border-slate-100 px-5 py-4 space-y-3 bg-white" style="padding-bottom: max(16px, env(safe-area-inset-bottom));">
            <div v-if="cartStore.noneSelected" class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-2xl">
              <Icon name="mdi:information-outline" class="text-amber-500 text-base flex-shrink-0" />
              <p class="text-xs font-bold text-amber-700">Centang buku yang ingin di-checkout terlebih dahulu</p>
            </div>
            <div v-else class="flex items-center justify-between">
              <div>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Pembayaran</p>
                <p class="text-xl font-black text-blue-700">{{ formatRupiah(cartStore.selectedTotal) }}</p>
              </div>
              <p class="text-xs text-slate-400"><span class="font-black text-blue-600">{{ cartStore.selectedItems.length }}</span> item dipilih</p>
            </div>
            <button
              @click="submitOrder"
              :disabled="submitting || cartStore.noneSelected"
              class="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-2xl font-extrabold text-sm uppercase tracking-wider shadow-lg active:scale-[0.98] flex items-center justify-center gap-2.5 disabled:opacity-50 transition-all"
            >
              <span v-if="submitting" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
              <template v-else>
                <Icon name="mdi:lock-check-outline" class="text-lg" />
                Checkout ({{ cartStore.selectedItems.length }} buku dipilih)
              </template>
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ─── BUY MODAL (Beli langsung 1 item) ───────────────────────── -->
    <Transition name="fade">
      <div v-if="buyModal.show" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-6 bg-slate-900/60 backdrop-blur-sm" @click.self="closeBuyModal">
        <div class="bg-white w-full sm:max-w-sm rounded-t-3xl sm:rounded-3xl overflow-hidden shadow-2xl">
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-5 text-white">
            <div class="flex items-start gap-3">
              <div class="w-12 h-14 rounded-xl overflow-hidden border-2 border-white/30 flex-shrink-0 bg-slate-300">
                <img v-if="buyModal.book?.gambar_url" :src="buyModal.book?.gambar_url" class="w-full h-full object-cover" alt="" />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <Icon name="mdi:book-open-page-variant" class="text-2xl text-white/50" />
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs text-blue-200 font-bold uppercase tracking-wider mb-0.5">Buku dipilih</p>
                <h3 class="font-black text-white text-sm leading-snug line-clamp-2">{{ buyModal.book?.nama_buku }}</h3>
                <p class="text-blue-100 font-black text-base mt-1">{{ formatRupiah(buyModal.book?.harga_jual ?? 0) }}</p>
              </div>
            </div>
          </div>

          <!-- Quantity -->
          <div class="px-5 pt-5 pb-3">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Jumlah Pembelian</p>
            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3">
              <button @click="buyModal.qty = Math.max(1, buyModal.qty - 1)"
                class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-slate-600 hover:bg-red-50 hover:text-red-500 shadow-sm transition-all active:scale-90">
                <Icon name="mdi:minus" class="text-base" />
              </button>
              <div class="text-center">
                <span class="text-2xl font-black text-slate-800">{{ buyModal.qty }}</span>
                <p class="text-[10px] text-slate-400 font-semibold mt-0.5">Sisa stok: {{ buyModal.book?.stok }}</p>
              </div>
              <button @click="buyModal.qty = Math.min(buyModal.book?.stok ?? 1, buyModal.qty + 1)"
                class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 shadow-sm transition-all active:scale-90">
                <Icon name="mdi:plus" class="text-base" />
              </button>
            </div>
            <div class="mt-2 flex justify-between text-xs text-slate-400 px-1">
              <span>Subtotal</span>
              <span class="font-black text-blue-600">{{ formatRupiah((buyModal.book?.harga_jual ?? 0) * buyModal.qty) }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="px-5 pb-5 flex flex-col gap-2">
            <button @click="directCheckout" :disabled="submitting"
              class="w-full py-3.5 bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white rounded-2xl font-extrabold text-sm uppercase tracking-wider transition-all active:scale-95 shadow-lg flex items-center justify-center gap-2 disabled:opacity-70">
              <span v-if="submitting" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
              <template v-else><Icon name="mdi:flash-circle" class="text-lg" />Langsung Checkout</template>
            </button>
            <button @click="addToCartFromModal"
              class="w-full py-3 bg-slate-100 hover:bg-slate-200 border-2 border-slate-200 text-slate-700 rounded-2xl font-bold text-sm uppercase tracking-wide transition-all active:scale-95 flex items-center justify-center gap-2">
              <Icon name="mdi:cart-arrow-down" class="text-lg" />
              Simpan ke Keranjang
            </button>
          </div>

          <button @click="closeBuyModal" class="w-full py-4 text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors border-t border-slate-100">
            Batal
          </button>
        </div>
      </div>
    </Transition>

    <!-- ─── DETAIL MODAL ─────────────────────────────────────────── -->
    <Transition name="fade">
      <div v-if="detailModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white w-full max-w-2xl rounded-3xl border border-slate-200 shadow-2xl relative my-4">
          <button @click="closeDetailModal" class="absolute top-3 right-3 w-9 h-9 flex items-center justify-center text-slate-400 hover:bg-slate-100 rounded-xl z-10 transition-colors">
            <Icon name="mdi:close" class="text-lg" />
          </button>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-0">
            <div class="p-4 sm:p-6">
              <div class="h-56 sm:aspect-[3/4] sm:h-auto rounded-2xl overflow-hidden bg-slate-100 relative border border-slate-200">
                <img v-if="detailModal.book?.gambar_url" :src="detailModal.book.gambar_url" class="w-full h-full object-cover" alt="Cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                  <Icon name="mdi:image-off-outline" class="text-5xl" />
                </div>
              </div>
            </div>
            <div class="p-4 sm:p-6 sm:pl-0 flex flex-col justify-between">
              <div class="space-y-4">
                <div>
                  <span class="text-xs font-bold text-blue-600 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-lg uppercase">
                    {{ detailModal.book?.category?.nama || 'Umum' }}
                  </span>
                  <h3 class="text-lg font-black text-slate-800 mt-2.5 leading-tight">{{ detailModal.book?.nama_buku }}</h3>
                </div>
                <div class="space-y-2 text-sm border-t border-slate-100 pt-3">
                  <div class="flex justify-between items-center">
                    <span class="text-slate-400">Harga</span>
                    <span class="font-black text-blue-600 text-base">{{ formatRupiah(detailModal.book?.harga_jual ?? 0) }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-slate-400">Stok</span>
                    <span class="font-bold" :class="detailModal.book?.stok > 0 ? 'text-emerald-600' : 'text-red-500'">
                      {{ detailModal.book?.stok > 0 ? `${detailModal.book?.stok} unit` : 'Habis' }}
                    </span>
                  </div>
                </div>
                <div class="border-t border-slate-100 pt-3">
                  <h4 class="font-bold text-slate-700 text-xs uppercase tracking-wider mb-1.5">Deskripsi</h4>
                  <p class="text-slate-500 text-xs leading-relaxed max-h-28 overflow-y-auto" style="scrollbar-width: thin;">
                    {{ detailModal.book?.deskripsi || 'Tidak ada deskripsi.' }}
                  </p>
                </div>
              </div>
              <div class="pt-4 flex flex-col gap-2 mt-4">
                <button @click="closeDetailModal(); openBuyModal(detailModal.book)" :disabled="detailModal.book?.stok <= 0"
                  class="flex items-center justify-center gap-1.5 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md active:scale-95 transition-all uppercase disabled:opacity-50 disabled:cursor-not-allowed">
                  <Icon name="mdi:flash" class="text-sm" />Beli Sekarang
                </button>
                <button @click="addToCartOnly(detailModal.book); closeDetailModal()" :disabled="detailModal.book?.stok <= 0"
                  class="flex items-center justify-center gap-1.5 px-4 py-3 bg-slate-100 hover:bg-slate-200 border-2 border-slate-200 text-slate-700 font-bold text-xs rounded-xl active:scale-95 transition-all uppercase disabled:opacity-50 disabled:cursor-not-allowed">
                  <Icon name="mdi:cart-plus" class="text-sm" />+ Keranjang
                </button>
                <button @click="closeDetailModal" class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-500 font-bold text-xs uppercase active:scale-95 transition-all">
                  Tutup
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ─── CHECKOUT SUCCESS MODAL ────────────────────────────────── -->
    <Transition name="fade">
      <div v-if="checkoutSuccessModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl relative my-6 overflow-hidden">
          <div class="bg-gradient-to-br from-emerald-400 to-emerald-600 p-6 text-center">
            <div class="w-16 h-16 bg-white/25 rounded-full flex items-center justify-center mx-auto mb-3">
              <Icon name="mdi:check-circle" class="text-5xl text-white" />
            </div>
            <h3 class="text-xl font-black text-white">Pesanan Berhasil!</h3>
            <p class="text-emerald-100 text-xs mt-1 font-semibold">Tunjukkan barcode ke kasir untuk pembayaran</p>
          </div>
          <div class="p-5 space-y-4">
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
            <div class="bg-white border-2 border-dashed border-slate-200 rounded-2xl p-4 flex flex-col items-center">
              <svg id="checkout-barcode-svg" class="w-full max-h-24" />
              <p class="font-mono font-black text-slate-700 tracking-widest text-sm mt-1.5">{{ checkoutSuccessModal.order?.kode_pesanan }}</p>
            </div>
            <div class="flex gap-2.5">
              <button @click="closeCheckoutSuccessModal" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl font-black text-xs uppercase tracking-wide active:scale-95 transition-all">
                Belanja Lagi
              </button>
              <NuxtLink to="/user/riwayat" @click="closeCheckoutSuccessModal" class="flex-1 py-3 bg-[#2E5AA7] text-white rounded-2xl font-black text-xs uppercase tracking-wide text-center active:scale-95 transition-all flex items-center justify-center gap-1.5">
                <Icon name="mdi:history" class="text-sm" />Riwayat
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

const api = useApi()
const cartStore = useCartStore()
const { $toast } = useNuxtApp() as any

// ── Data ─────────────────────────────────────────────────────────
const books = ref<any[]>([])
const loading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)

// silent=true: refresh tanpa loading spinner (background/periodic refresh)
const fetchBooks = async (page = 1, silent = false) => {
  if (!silent) loading.value = true
  try {
    const res = await api.get(`/books?page=${page}&per_page=6`)
    books.value = res.data.data.data
    currentPage.value = res.data.data.meta.current_page
    totalPages.value = res.data.data.meta.last_page
  } catch (e) { console.error(e) }
  finally { if (!silent) loading.value = false }
}
const changePage = (page: number) => fetchBooks(page)

// Refresh stok saat user kembali ke tab ini (setelah batalkan pesanan di riwayat)
const handleVisibilityChange = () => {
  if (document.visibilityState === 'visible') fetchBooks(currentPage.value, true)
}

// Refresh stok saat riwayat.vue membatalkan pesanan (cross-page event via localStorage)
const handleStorageEvent = (e: StorageEvent) => {
  if (e.key === 'bookstok_refresh') fetchBooks(currentPage.value, true)
}

let bookRefreshInterval: ReturnType<typeof setInterval> | null = null

onMounted(() => {
  fetchBooks()
  // Silent background refresh setiap 60 detik
  bookRefreshInterval = setInterval(() => fetchBooks(currentPage.value, true), 60_000)
  document.addEventListener('visibilitychange', handleVisibilityChange)
  window.addEventListener('storage', handleStorageEvent)
})

onUnmounted(() => {
  if (bookRefreshInterval) clearInterval(bookRefreshInterval)
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  window.removeEventListener('storage', handleStorageEvent)
})

// ── Cart Actions ──────────────────────────────────────────────────
// Langsung masuk keranjang, dipilih otomatis, TIDAK checkout
const addToCartOnly = (book: any) => {
  if (!book || book.stok <= 0) return
  const success = cartStore.addToCart({ id: book.id, nama_buku: book.nama_buku, harga_jual: book.harga_jual, gambar_url: book.gambar_url, stok: book.stok }, 1, true)
  if (success) {
    $toast.fire({ icon: 'success', title: `${book.nama_buku} masuk keranjang!`, timer: 1500 })
  } else {
    $toast.fire({ icon: 'error', title: 'Stok tidak cukup.' })
  }
}

// ── BUY MODAL ────────────────────────────────────────────────────
const buyModal = ref({ show: false, book: null as any, qty: 1 })
const openBuyModal = (book: any) => { buyModal.value = { show: true, book, qty: 1 } }
const closeBuyModal = () => { buyModal.value.show = false }

const addToCartFromModal = () => {
  const { book, qty } = buyModal.value
  const success = cartStore.addToCart({ id: book.id, nama_buku: book.nama_buku, harga_jual: book.harga_jual, gambar_url: book.gambar_url, stok: book.stok }, qty, true)
  closeBuyModal()
  if (success) {
    $toast.fire({ icon: 'success', title: `${book.nama_buku} masuk keranjang!`, timer: 1500 })
  } else {
    $toast.fire({ icon: 'error', title: 'Stok tidak cukup.' })
  }
}

const directCheckout = async () => {
  const { book, qty } = buyModal.value
  const success = cartStore.addToCart({ id: book.id, nama_buku: book.nama_buku, harga_jual: book.harga_jual, gambar_url: book.gambar_url, stok: book.stok }, qty, true)
  if (!success) { $toast.fire({ icon: 'error', title: 'Stok tidak cukup.' }); return }
  // Deselect all other items, only select this book
  cartStore.items.forEach(i => { i.selected = i.book_id === book.id })
  closeBuyModal()
  await submitOrder()
}

// ── Mobile Drawer ─────────────────────────────────────────────────
const mobileCartDrawerOpen = ref(false)
const openMobileCartDrawer = () => { mobileCartDrawerOpen.value = true }
const closeMobileCartDrawer = () => { mobileCartDrawerOpen.value = false }

// ── Checkout (only selected items) ───────────────────────────────
const submitting = ref(false)
const submitOrder = async () => {
  if (cartStore.noneSelected) return
  submitting.value = true
  try {
    const payload = {
      items: cartStore.selectedItems.map(i => ({ book_id: i.book_id, qty: i.qty }))
    }
    const res = await api.post('/orders', payload)
    const order = res.data.data
    mobileCartDrawerOpen.value = false
    cartStore.clearSelected()   // hapus hanya item yang di-checkout, sisanya tetap
    openCheckoutSuccessModal(order)
    $toast.fire({ icon: 'success', title: `Pesanan ${order.invoice_number} berhasil!`, timer: 2000 })
  } catch (err: any) {
    $toast.fire({ icon: 'error', title: err.response?.data?.message || 'Gagal mengirim pesanan.' })
  } finally { submitting.value = false }
}

// ── Success Modal ─────────────────────────────────────────────────
const checkoutSuccessModal = ref({ show: false, order: null as any })
const openCheckoutSuccessModal = (order: any) => {
  checkoutSuccessModal.value = { show: true, order }
  generateBarcode(order.kode_pesanan)
}
const closeCheckoutSuccessModal = () => { checkoutSuccessModal.value = { show: false, order: null } }
const generateBarcode = async (kode: string) => {
  await nextTick()
  await new Promise(r => setTimeout(r, 200))
  let attempts = 0
  const iv = setInterval(() => {
    attempts++
    const el = document.getElementById('checkout-barcode-svg')
    const JsB = (window as any).JsBarcode
    if (el && typeof JsB !== 'undefined') {
      try { JsB('#checkout-barcode-svg', kode, { format: 'CODE128', width: 2.5, height: 80, displayValue: false, margin: 8 }); clearInterval(iv) }
      catch (e) { console.error(e) }
    }
    if (attempts >= 15) clearInterval(iv)
  }, 200)
}

// ── Detail Modal ──────────────────────────────────────────────────
const detailModal = ref({ show: false, book: null as any })
const openDetailModal = (book: any) => { detailModal.value = { show: true, book } }
const closeDetailModal = () => { detailModal.value.show = false }

// ── Helpers ───────────────────────────────────────────────────────
const formatRupiah = (val: number) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val)
</script>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.3s cubic-bezier(0.4,0,0.2,1); }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); }
.sheet-enter-active, .sheet-leave-active { transition: opacity 0.25s ease; }
.sheet-enter-from, .sheet-leave-to { opacity: 0; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
