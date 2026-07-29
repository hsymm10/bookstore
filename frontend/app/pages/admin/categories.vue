<template>
  <div class="space-y-6">
    <!-- Page Header (already handled by admin layout, but we customize title here if needed) -->
    <div class="flex items-center justify-between">
      <h2 class="text-3xl font-black text-slate-800 uppercase tracking-wide">Kategori</h2>
    </div>

    <!-- MAIN PANEL -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
      <!-- 1. VIEW INPUT FORM -->
      <div v-if="showForm" class="max-w-xl mx-auto py-8">
        <h3 class="text-center text-2xl font-black text-slate-900 tracking-wider mb-8 uppercase font-sans">Input Kategori</h3>
        
        <form @submit.prevent="submitForm" class="space-y-6 bg-white p-8 rounded-2xl border border-slate-300 shadow-sm">
          <div class="flex flex-col sm:flex-row items-center gap-4">
            <label class="w-28 text-slate-800 text-lg font-black font-sans">Kategori</label>
            <input v-model="modal.form.nama" type="text" placeholder="Masukkan kategori" required
              class="flex-1 px-4 py-3 border-2 border-slate-200 focus:outline-none focus:ring-0 text-slate-800 font-medium" 
              :disabled="modal.loading" />
          </div>

          <div class="flex items-center justify-end gap-3 pt-4">
            <button type="button" @click="closeForm" class="px-5 py-2 border border-slate-400 rounded-lg text-slate-600 font-semibold" :disabled="modal.loading">
              Batal
            </button>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-400 to-blue-600 hover:from-blue-500 hover:to-blue-700 text-white font-extrabold rounded-lg shadow-md active:scale-95 transition-all duration-200" :disabled="modal.loading">
              <span v-if="modal.loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <span v-else>SIMPAN</span>
            </button>
          </div>
        </form>
      </div>

      <!-- 2. VIEW TABLE LIST -->
      <div v-else>
        <!-- Top Toolbar: Plus sign on left -->
        <div class="flex items-center justify-between mb-6">
          <button @click="openCreateForm" class="w-12 h-12 bg-white hover:bg-slate-50 border border-slate-300 rounded-xl flex items-center justify-center shadow-sm active:scale-95 transition-all" title="Tambah Kategori">
            <Icon name="mdi:plus" class="text-3xl text-slate-900" />
          </button>
          <!-- Search field -->
          <div class="relative max-w-xs w-full">
            <input v-model="search" @input="debounceSearch" type="text" placeholder="Cari nama kategori..."
              class="w-full px-4 py-2 border border-slate-300 rounded-xl text-sm bg-white placeholder-slate-400 focus:outline-none" />
          </div>
        </div>

        <!-- Table Container with custom black border styles -->
        <div class="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-slate-100 border-b border-slate-200">
                <th class="border-r border-slate-200 px-6 py-4 text-center font-black text-slate-900 tracking-wider font-sans uppercase text-sm">KATEGORI</th>
                <th class="px-6 py-4 text-center font-black text-slate-900 tracking-wider font-sans uppercase text-sm w-44">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="2" class="text-center py-12">
                  <div class="flex justify-center items-center gap-2 text-slate-500 font-bold">
                    <span class="w-5 h-5 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></span>
                    <span>Memuat data...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="categories.length === 0" class="border-b border-slate-200">
                <td colspan="2" class="text-center py-12 text-slate-400 font-semibold">
                  Belum ada kategori yang ditemukan.
                </td>
              </tr>
              <tr v-else v-for="cat in categories" :key="cat.id" class="border-b border-slate-200 last:border-b-0">
                <td class="border-r border-slate-200 px-6 py-4 text-center font-bold text-slate-800 text-sm">
                  {{ cat.nama }}
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="flex items-center justify-center gap-4">
                    <button @click="openEditForm(cat)" class="p-1.5 hover:text-blue-600 transition-colors" title="Edit">
                      <Icon name="mdi:pencil-outline" class="text-2xl text-slate-700 hover:text-blue-600" />
                    </button>
                    <button @click="deleteCategory(cat)" class="p-1.5 hover:text-red-600 transition-colors" title="Hapus">
                      <Icon name="mdi:trash-can-outline" class="text-2xl text-slate-700 hover:text-red-600" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Bulat Hitam di Kanan Bawah -->
        <div v-if="pagination.last_page > 1" class="mt-6 flex justify-end gap-2">
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
</template>

<script setup lang="ts">


definePageMeta({ layout: 'admin', middleware: 'admin', ssr: false })

const api = useApi()
const { $swal, $toast } = useNuxtApp() as any

const categories = ref<any[]>([])
const search = ref('')
const loading = ref(true)

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
  form: {
    nama: ''
  }
})

let searchTimeout: any = null
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchCategories()
  }, 500)
}

const fetchCategories = async () => {
  loading.value = true
  try {
    const res = await api.get('/categories', {
      params: {
        search: search.value,
        page: pagination.value.current_page,
        per_page: pagination.value.per_page
      }
    })
    const data = res.data.data
    categories.value = data.data
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
  fetchCategories()
}

const showForm = ref(false)

const openCreateForm = () => {
  modal.value = {
    show: true,
    editMode: false,
    loading: false,
    id: null,
    form: { nama: '' }
  }
  showForm.value = true
}

const openEditForm = (category: any) => {
  modal.value = {
    show: true,
    editMode: true,
    loading: false,
    id: category.id,
    form: { nama: category.nama }
  }
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
  modal.value.show = false
}

const submitForm = async () => {
  modal.value.loading = true
  try {
    if (modal.value.editMode) {
      const res = await api.put(`/categories/${modal.value.id}`, modal.value.form)
      $toast.fire({ icon: 'success', title: res.data.message || 'Kategori diperbarui.' })
    } else {
      const res = await api.post('/categories', modal.value.form)
      $toast.fire({ icon: 'success', title: res.data.message || 'Kategori ditambahkan.' })
    }
    closeForm()
    fetchCategories()
  } catch (error: any) {
    const msg = error.response?.data?.message || 'Gagal menyimpan kategori.'
    $toast.fire({ icon: 'error', title: msg })
  } finally {
    modal.value.loading = false
  }
}

const deleteCategory = async (category: any) => {
  const confirm = await $swal.fire({
    title: 'Hapus Kategori?',
    text: `Anda yakin ingin menghapus kategori "${category.nama}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  })

  if (!confirm.isConfirmed) return

  try {
    const res = await api.delete(`/categories/${category.id}`)
    $toast.fire({ icon: 'success', title: res.data.message || 'Kategori dihapus.' })
    fetchCategories()
  } catch (error: any) {
    const msg = error.response?.data?.message || 'Gagal menghapus kategori.'
    $swal.fire({
      title: 'Gagal Hapus',
      text: msg,
      icon: 'error'
    })
  }
}

onMounted(() => {
  fetchCategories()
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
