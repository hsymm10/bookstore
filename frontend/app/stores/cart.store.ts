import { defineStore } from 'pinia'

export interface CartItem {
  book_id:    number
  nama_buku:  string
  harga_jual: number
  gambar_url: string | null
  qty:        number
  subtotal:   number
  stok:       number
  selected:   boolean   // ← checkbox untuk pilih item saat checkout
}

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [] as CartItem[],
  }),

  getters: {
    // Semua item
    totalItems:    (state) => state.items.reduce((sum, i) => sum + i.qty, 0),
    totalHarga:    (state) => state.items.reduce((sum, i) => sum + i.subtotal, 0),
    isEmpty:       (state) => state.items.length === 0,

    // Hanya item yang di-checklist
    selectedItems: (state) => state.items.filter(i => i.selected),
    selectedTotal: (state) => state.items.filter(i => i.selected).reduce((sum, i) => sum + i.subtotal, 0),
    selectedCount: (state) => state.items.filter(i => i.selected).reduce((sum, i) => sum + i.qty, 0),
    allSelected:   (state) => state.items.length > 0 && state.items.every(i => i.selected),
    noneSelected:  (state) => state.items.every(i => !i.selected),
  },

  actions: {
    addToCart(
      book: { id: number; nama_buku: string; harga_jual: number; gambar_url: string | null; stok: number },
      qty = 1,
      autoSelect = true
    ) {
      const existing = this.items.find((i) => i.book_id === book.id)

      if (existing) {
        const newQty = existing.qty + qty
        if (newQty > book.stok) return false
        existing.qty      = newQty
        existing.subtotal = existing.harga_jual * newQty
        if (autoSelect) existing.selected = true
      } else {
        if (qty > book.stok) return false
        this.items.push({
          book_id:    book.id,
          nama_buku:  book.nama_buku,
          harga_jual: book.harga_jual,
          gambar_url: book.gambar_url,
          qty,
          subtotal:   book.harga_jual * qty,
          stok:       book.stok,
          selected:   autoSelect,
        })
      }
      return true
    },

    updateQty(bookId: number, qty: number) {
      const item = this.items.find((i) => i.book_id === bookId)
      if (!item) return
      if (qty <= 0) { this.removeItem(bookId); return }
      item.qty      = Math.min(qty, item.stok)
      item.subtotal = item.harga_jual * item.qty
    },

    toggleSelected(bookId: number) {
      const item = this.items.find(i => i.book_id === bookId)
      if (item) item.selected = !item.selected
    },

    toggleSelectAll() {
      const shouldSelect = !this.allSelected
      this.items.forEach(i => { i.selected = shouldSelect })
    },

    removeItem(bookId: number) {
      this.items = this.items.filter((i) => i.book_id !== bookId)
    },

    clearCart() {
      this.items = []
    },

    clearSelected() {
      this.items = this.items.filter(i => !i.selected)
    },
  },
})
