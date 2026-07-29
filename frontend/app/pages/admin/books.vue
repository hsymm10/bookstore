<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <h2 class="text-3xl font-black text-slate-800 uppercase tracking-wide">BOOKS</h2>
    </div>

    <!-- MAIN PANEL -->
    <div class="bg-[#f0f2f5] p-8 rounded-3xl border border-slate-300 shadow-sm min-h-[450px]">
      <!-- 1. VIEW INPUT FORM -->
      <div v-if="showForm" class="max-w-3xl mx-auto py-4">
        <h3 class="text-center text-2xl font-black text-slate-900 tracking-wider mb-8 uppercase font-sans">Input Data Buku</h3>
        
        <form @submit.prevent="submitForm" class="space-y-6 bg-white p-8 rounded-2xl border border-slate-300 shadow-sm">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
              <div class="flex items-center gap-3">
                <label class="w-32 text-sm font-bold text-slate-800">Nama Buku</label>
                <input v-model="modal.form.nama_buku" type="text" required class="flex-1 px-3 py-2 border-2 border-slate-200 text-sm" />
              </div>
              
              <div class="flex items-center gap-3">
                <label class="w-32 text-sm font-bold text-slate-800">Tanggal dan Tahun terbit</label>
                <input v-model="modal.form.tanggal_terbit" type="date" class="flex-1 px-3 py-2 border-2 border-slate-200 text-sm" />
              </div>

              <div class="flex items-center gap-3">
                <label class="w-32 text-sm font-bold text-slate-800">Stok</label>
                <input v-model.number="modal.form.stok" type="number" min="0" required class="flex-1 px-3 py-2 border-2 border-slate-200 text-sm" />
              </div>

              <div class="flex items-center gap-3">
                <label class="w-32 text-sm font-bold text-slate-800">Modal</label>
                <input v-model.number="modal.form.harga_modal" type="number" min="0" required class="flex-1 px-3 py-2 border-2 border-slate-200 text-sm" />
              </div>

              <div class="flex items-center gap-3">
                <label class="w-32 text-sm font-bold text-slate-800">Harga Jual</label>
                <input v-model.number="modal.form.harga_jual" type="number" min="0" required class="flex-1 px-3 py-2 border-2 border-slate-200 text-sm" />
              </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
              <div class="flex items-center gap-3">
                <label class="w-24 text-sm font-bold text-slate-800">Kategori</label>
                <select v-model="modal.form.category_id" required class="flex-1 px-3 py-2 border-2 border-slate-200 text-sm bg-white">
                  <option :value="null" disabled>-------</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nama }}</option>
                </select>
              </div>

              <div class="flex items-center gap-3">
                <label class="w-24 text-sm font-bold text-slate-800">Keuntungan</label>
                <input :value="formatRupiah(calculatedProfit)" type="text" readonly
                  class="flex-1 px-3 py-2 border-2 border-slate-200 text-sm bg-slate-50 cursor-not-allowed font-semibold"
                  :class="calculatedProfit < 0 ? 'text-red-500' : 'text-green-600'" />
              </div>

              <div class="flex items-start gap-3">
                <label class="w-24 text-sm font-bold text-slate-800 mt-2">Deskripsi</label>
                <textarea v-model="modal.form.deskripsi" rows="2" class="flex-1 px-3 py-2 border-2 border-slate-200 text-sm resize-none"></textarea>
              </div>

              <div class="flex items-center gap-3">
                <label class="w-24 text-sm font-bold text-slate-800">Gambar</label>
                <div class="flex-1 flex gap-2">
                  <label class="flex-1 border-2 border-slate-200 border-dashed flex items-center justify-center p-2 cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors text-xs font-bold text-blue-600">
                    <Icon name="mdi:upload" class="text-lg mr-1" />
                    UPLOAD
                    <input type="file" accept="image/*" @change="handleFileChange" class="hidden" />
                  </label>
                  <div v-if="modal.previewUrl" class="w-10 h-10 border border-slate-200 rounded overflow-hidden flex-shrink-0">
                    <img :src="modal.previewUrl" class="w-full h-full object-cover" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Action buttons -->
          <div class="flex flex-col gap-3 pt-4 border-t border-slate-200">
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white font-extrabold rounded-xl shadow-md active:scale-95 transition-all duration-200 text-center uppercase tracking-wider">
              Simpan
            </button>
            <button type="button" @click="closeForm" class="w-full py-2.5 border border-slate-400 rounded-xl text-slate-600 font-bold hover:bg-slate-50 transition-colors text-sm">
              Batal
            </button>
          </div>
        </form>
      </div>

      <!-- 2. VIEW GRID LIST -->
      <div v-else>
        <!-- Top bar: Plus sign and Search field -->
        <div class="flex items-center justify-between mb-8">
          <button @click="openCreateForm" class="w-12 h-12 bg-white hover:bg-slate-50 border border-slate-300 rounded-xl flex items-center justify-center shadow-sm active:scale-95 transition-all" title="Tambah Buku">
            <Icon name="mdi:plus" class="text-3xl text-slate-900" />
          </button>
          <!-- Search box -->
          <div class="relative max-w-xs w-full">
            <input v-model="filters.search" @input="debounceSearch" type="text" placeholder="Search"
              class="w-full px-4 py-2 border border-slate-300 rounded-xl text-sm bg-white placeholder-slate-400 focus:outline-none" />
          </div>
        </div>

        <!-- Books Grid -->
        <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
          <div v-for="i in 4" :key="i" class="bg-white p-4 rounded-2xl border border-slate-200 animate-pulse h-80">
            <div class="w-full h-44 bg-slate-100 rounded-xl mb-3" />
            <div class="h-4 bg-slate-100 rounded w-3/4 mb-2" />
            <div class="h-3 bg-slate-100 rounded w-1/2" />
          </div>
        </div>

        <div v-else-if="books.length === 0" class="text-center py-20 text-slate-400 font-semibold bg-white rounded-3xl border border-slate-300">
          Belum ada buku yang ditemukan.
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
          <div v-for="book in books" :key="book.id" class="bg-white p-4 rounded-3xl border border-slate-300 shadow-sm flex flex-col justify-between">
            <div>
              <!-- Cover image or fallback -->
              <div class="aspect-[3/4] rounded-2xl overflow-hidden mb-4 bg-slate-50 border border-slate-100">
                <img v-if="book.gambar_url" :src="book.gambar_url" :alt="book.nama_buku" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-300">
                  <Icon name="mdi:image-off-outline" class="text-5xl" />
                  <span class="text-xs font-semibold mt-2">No Cover</span>
                </div>
              </div>

              <!-- Metadata -->
              <div class="space-y-1 text-sm font-sans mb-4">
                <p class="font-bold text-slate-900 truncate">Nama Buku : {{ book.nama_buku }}</p>
                <p class="text-slate-600 font-bold">Kategori : {{ book.category?.nama || '-' }}</p>
                <p class="text-blue-600 font-black">Harga : {{ formatRupiah(book.harga_jual) }}</p>
                <p class="text-xs text-slate-400">Stok : {{ book.stok }} unit</p>
              </div>
            </div>

            <!-- Action buttons -->
            <div class="space-y-2">
              <button @click="openEditForm(book)" class="w-full py-2 bg-blue-100 hover:bg-blue-200 text-blue-800 font-extrabold text-xs rounded-xl transition-colors uppercase tracking-wider">
                UPDATE
              </button>
              <button @click="deleteBook(book)" class="w-full py-2 bg-red-100 hover:bg-red-200 text-red-800 font-extrabold text-xs rounded-xl transition-colors uppercase tracking-wider">
                DELETE
              </button>
            </div>
          </div>
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

    <!-- ─── Modal Form (Create/Edit) ────────────────────── -->
    <Transition name="fade">
      <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="card w-full max-w-2xl p-6 shadow-glass-lg animate-slide-up relative bg-white my-8">
          <!-- Close Button -->
          <button @click="closeModal" class="absolute top-4 right-4 transition-colors" style="color:#86C5FF">
            <Icon name="mdi:close" class="text-xl" />
          </button>

          <h3 class="text-lg font-bold mb-6" style="color:#17305c">
            {{ modal.editMode ? 'Edit Buku' : 'Tambah Buku Baru' }}
          </h3>

          <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Judul Buku -->
              <div class="md:col-span-2">
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Judul Buku</label>
                <input v-model="modal.form.nama_buku" type="text" placeholder="Masukkan judul buku" required
                  class="input-field" :disabled="modal.loading" />
              </div>

              <!-- Kategori -->
              <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Kategori</label>
                <select v-model="modal.form.category_id" required class="input-field" :disabled="modal.loading">
                  <option :value="null" disabled>Pilih Kategori</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.nama }}
                  </option>
                </select>
              </div>

              <!-- Stok -->
              <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Stok</label>
                <input v-model.number="modal.form.stok" type="number" min="0" placeholder="0" required
                  class="input-field" :disabled="modal.loading" />
              </div>

              <!-- Harga Modal -->
              <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Harga Modal (Rp)</label>
                <input 
                  :value="formatThousand(modal.form.harga_modal)" 
                  @input="e => modal.form.harga_modal = parseThousand((e.target as HTMLInputElement).value)" 
                  type="text" 
                  placeholder="0" 
                  required
                  class="input-field font-semibold" 
                  :disabled="modal.loading" 
                />
              </div>

              <!-- Harga Jual -->
              <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Harga Jual (Rp)</label>
                <input 
                  :value="formatThousand(modal.form.harga_jual)" 
                  @input="e => modal.form.harga_jual = parseThousand((e.target as HTMLInputElement).value)" 
                  type="text" 
                  placeholder="0" 
                  required
                  class="input-field font-semibold" 
                  :disabled="modal.loading" 
                />
              </div>

              <!-- Keuntungan (readonly display) -->
              <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Keuntungan</label>
                <input :value="formatRupiah(calculatedProfit)" type="text" readonly
                  class="input-field bg-slate-50 cursor-not-allowed font-bold"
                  :class="calculatedProfit < 0 ? 'text-red-500' : 'text-green-600'" />
              </div>

              <!-- Tanggal Terbit -->
              <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Tanggal Terbit</label>
                <input v-model="modal.form.tanggal_terbit" type="date"
                  class="input-field" :disabled="modal.loading" />
              </div>

              <!-- Tahun Terbit -->
              <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Tahun Terbit</label>
                <input v-model.number="modal.form.tahun_terbit" type="number" placeholder="Contoh: 2024"
                  class="input-field" :disabled="modal.loading" />
              </div>

              <!-- Deskripsi -->
              <div class="md:col-span-2">
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Deskripsi</label>
                <textarea v-model="modal.form.deskripsi" rows="3" placeholder="Masukkan deskripsi atau sinopsis buku..."
                  class="input-field resize-none" :disabled="modal.loading"></textarea>
              </div>

              <!-- Cover Image -->
              <div class="md:col-span-2">
                <label class="block text-xs font-bold uppercase tracking-wider mb-1.5" style="color:#2E5AA7">Gambar Cover (Max 2MB)</label>
                <div class="flex items-center gap-4">
                  <!-- Preview -->
                  <div class="w-16 h-20 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0" style="background:#f0f7ff; border:1px solid #daeeff">
                    <img v-if="modal.previewUrl" :src="modal.previewUrl" class="w-full h-full object-cover" />
                    <Icon v-else name="mdi:book-open-page-variant" class="text-2xl" style="color:#86C5FF" />
                  </div>
                  <!-- File Input -->
                  <input type="file" accept="image/*" @change="handleFileChange"
                    class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold cursor-pointer"
                    style="color:#86C5FF"
                    :disabled="modal.loading" />
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t" style="border-color:#daeeff">
              <button type="button" @click="closeModal" class="btn-secondary !py-2.5 !px-4 !text-sm" :disabled="modal.loading">
                Batal
              </button>
              <button type="submit" class="btn-primary !py-2.5 !px-4 !text-sm" :disabled="modal.loading">
                <span v-if="modal.loading" class="w-4 h-4 border-2 border-t-transparent rounded-full animate-spin" style="border-color:#0e1f3d;border-top-color:transparent"></span>
                <span v-else>{{ modal.editMode ? 'Simpan Perubahan' : 'Tambah Buku' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup lang="ts">

definePageMeta({ layout: 'admin', middleware: 'admin', ssr: false })

const api = useApi()
const { $swal, $toast } = useNuxtApp() as any

const books = ref<any[]>([])
const categories = ref<any[]>([])
const loading = ref(true)

const filters = ref({
  search: '',
  category_id: null as number | null
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0
})

const modal = ref({
  show: false,
  editMode: false,
  loading: false,
  id: null as number | null,
  previewUrl: null as string | null,
  file: null as File | null,
  form: {
    category_id: null as number | null,
    nama_buku: '',
    tanggal_terbit: '',
    tahun_terbit: null as number | null,
    stok: 0,
    harga_modal: 0,
    harga_jual: 0,
    deskripsi: ''
  }
})

let searchTimeout: any = null
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchBooks()
  }, 500)
}

const fetchBooks = async () => {
  loading.value = true
  try {
    const res = await api.get('/books', {
      params: {
        search: filters.value.search,
        category_id: filters.value.category_id,
        page: pagination.value.current_page,
        per_page: pagination.value.per_page
      }
    })
    const data = res.data.data
    books.value = data.data
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

const changePage = (page: number) => {
  pagination.value.current_page = page
  fetchBooks()
}

const showForm = ref(false)

const calculatedProfitPercentage = computed(() => {
  const modal_cost = modal.value.form.harga_modal
  const sell_price = modal.value.form.harga_jual
  if (!modal_cost || !sell_price) return 0
  const profit = sell_price - modal_cost
  return ((profit / modal_cost) * 100).toFixed(0)
})

const calculatedProfit = computed(() => {
  const modal_cost = modal.value.form.harga_modal || 0
  const sell_price = modal.value.form.harga_jual || 0
  return sell_price - modal_cost
})

const openCreateForm = () => {
  modal.value = {
    show: true,
    editMode: false,
    loading: false,
    id: null,
    previewUrl: null,
    file: null,
    form: {
      category_id: null,
      nama_buku: '',
      tanggal_terbit: '',
      tahun_terbit: null,
      stok: 0,
      harga_modal: 0,
      harga_jual: 0,
      deskripsi: ''
    }
  }
  showForm.value = true
}

const formatDateForInput = (dateStr: string | null) => {
  if (!dateStr) return ''
  const parsed = Date.parse(dateStr)
  if (isNaN(parsed)) return ''
  const d = new Date(parsed)
  return d.toISOString().split('T')[0]
}

const openEditForm = (book: any) => {
  modal.value = {
    show: true,
    editMode: true,
    loading: false,
    id: book.id,
    previewUrl: book.gambar_url,
    file: null,
    form: {
      category_id: book.category_id,
      nama_buku: book.nama_buku,
      tanggal_terbit: formatDateForInput(book.tanggal_terbit),
      tahun_terbit: book.tahun_terbit,
      stok: book.stok,
      harga_modal: book.harga_modal,
      harga_jual: book.harga_jual,
      deskripsi: book.deskripsi || ''
    }
  }
  showForm.value = true
}

const closeModal = () => {
  modal.value.show = false
  showForm.value = false
  modal.value.form = {
    category_id: null,
    nama_buku: '',
    tanggal_terbit: '',
    tahun_terbit: null,
    stok: 0,
    harga_modal: 0,
    harga_jual: 0,
    deskripsi: ''
  }
  modal.value.previewUrl = null
  modal.value.file = null
  modal.value.id = null
  modal.value.editMode = false
}

const closeForm = closeModal

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    const file = target.files[0]
    modal.value.file = file
    modal.value.previewUrl = URL.createObjectURL(file)
  }
}

const submitForm = async () => {
  modal.value.loading = true
  
  const formData = new FormData()
  formData.append('category_id', String(modal.value.form.category_id))
  formData.append('nama_buku', modal.value.form.nama_buku)
  if (modal.value.form.tanggal_terbit) {
    formData.append('tanggal_terbit', modal.value.form.tanggal_terbit)
  }
  if (modal.value.form.tahun_terbit) {
    formData.append('tahun_terbit', String(modal.value.form.tahun_terbit))
  }
  formData.append('stok', String(modal.value.form.stok))
  formData.append('harga_modal', String(modal.value.form.harga_modal))
  formData.append('harga_jual', String(modal.value.form.harga_jual))
  formData.append('deskripsi', modal.value.form.deskripsi)
  
  if (modal.value.file) {
    formData.append('gambar', modal.value.file)
  }

  try {
    if (modal.value.editMode) {
      const res = await api.post(`/books/${modal.value.id}`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      $toast.fire({ icon: 'success', title: res.data.message || 'Buku diperbarui.' })
    } else {
      const res = await api.post('/books', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      $toast.fire({ icon: 'success', title: res.data.message || 'Buku ditambahkan.' })
    }
    closeForm()
    fetchBooks()
  } catch (error: any) {
    const errors = error.response?.data?.errors
    let message = error.response?.data?.message || 'Gagal menyimpan buku.'
    if (errors) {
      const firstKey = Object.keys(errors)[0]
      if (errors[firstKey] && errors[firstKey].length > 0) {
        message = errors[firstKey][0]
      }
    }
    $toast.fire({ icon: 'error', title: message })
  } finally {
    modal.value.loading = false
  }
}

const deleteBook = async (book: any) => {
  if (book.stok > 0) {
    await $swal.fire({
      title: 'Buku Tidak Dapat Dihapus',
      text: 'Buku tidak dapat dihapus karena stok masih tersedia. Silakan kosongkan stok terlebih dahulu.',
      icon: 'error',
      confirmButtonText: 'OK'
    })
    return
  }

  const confirm = await $swal.fire({
    title: 'Apakah Anda yakin ingin menghapus buku ini?',
    text: 'Data yang dihapus tidak dapat dikembalikan.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya',
    cancelButtonText: 'Tidak'
  })

  if (!confirm.isConfirmed) return

  try {
    const res = await api.delete(`/books/${book.id}`)
    $toast.fire({ icon: 'success', title: res.data.message || 'Buku dihapus.' })
    fetchBooks()
  } catch (error: any) {
    const msg = error.response?.data?.message || 'Gagal menghapus buku.'
    $swal.fire({
      title: 'Gagal Hapus',
      text: msg,
      icon: 'error'
    })
  }
}

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(val)
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

onMounted(async () => {
  fetchBooks()
  try {
    const catsRes = await api.get('/categories/all')
    categories.value = catsRes.data.data
  } catch (e) {
    console.error(e)
  }
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
