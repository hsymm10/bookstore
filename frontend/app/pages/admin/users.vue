<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <h2 class="text-3xl font-black text-slate-800 uppercase tracking-wide">Pengguna</h2>
    </div>

    <!-- MAIN PANEL -->
    <div class="bg-[#f0f2f5] p-8 rounded-3xl border border-slate-300 shadow-sm min-h-[450px]">
      <!-- Search Input inside Panel -->
      <div class="flex items-center justify-between mb-8">
        <div class="relative max-w-xs w-full">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
            <Icon name="mdi:magnify" class="text-lg" />
          </span>
          <input v-model="search" @input="debounceSearch" type="text" placeholder="Search"
            class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-xl text-sm bg-white placeholder-slate-400 focus:outline-none" />
        </div>
      </div>

      <!-- Users Grid -->
      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        <div v-for="i in 4" :key="i" class="bg-white p-4 rounded-2xl border border-slate-200 animate-pulse h-64">
          <div class="w-full h-32 bg-slate-100 rounded-xl mb-3" />
          <div class="h-4 bg-slate-100 rounded w-3/4 mb-2" />
          <div class="h-3 bg-slate-100 rounded w-1/2" />
        </div>
      </div>

      <div v-else-if="users.length === 0" class="text-center py-20 text-slate-400 font-semibold bg-white rounded-3xl border border-slate-300">
        Belum ada pengguna yang ditemukan.
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        <div v-for="user in users" :key="user.id" class="bg-white p-4 rounded-3xl border border-slate-300 shadow-sm flex flex-col justify-between">
          <div>
            <!-- User image or fallback avatar -->
            <div class="aspect-square rounded-2xl overflow-hidden mb-4 bg-slate-50 border border-slate-100 flex items-center justify-center relative group">
              <!-- If there's an avatar/photo URL, we can display it. In bookstore project, users typically don't have avatar_url, but they have dynamic initials. Wait, in the screenshot they have photos! We can show initials in a styled way or use a nice default avatar placeholder. Let's use user initials or placeholder. -->
              <div class="w-full h-full bg-gradient-to-br from-[#cbe2ff] to-[#a5cfff] flex items-center justify-center text-primary-800 text-3xl font-black">
                {{ user.nama?.charAt(0)?.toUpperCase() || 'U' }}
              </div>
              <span class="absolute top-2 right-2 bg-black/60 text-white rounded-full p-1 text-xs cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity" title="Lihat Profil">
                <Icon name="mdi:eye-outline" />
              </span>
            </div>

            <!-- Metadata -->
            <div class="space-y-1 text-sm font-sans mb-4">
              <p class="font-bold text-slate-900 truncate">Nama : {{ user.nama }}</p>
              <p class="text-slate-600 font-medium truncate">Email : {{ user.email }}</p>
              <p class="text-xs text-slate-400 font-mono">No HP : {{ user.no_hp || '-' }}</p>
            </div>
          </div>

          <!-- Action buttons -->
          <div class="space-y-2">
            <button @click="openEditModal(user)" class="w-full py-2 bg-blue-100 hover:bg-blue-200 text-blue-800 font-extrabold text-xs rounded-xl transition-colors uppercase tracking-wider">
              UPDATE
            </button>
            <button @click="deleteUser(user)" class="w-full py-2 bg-red-100 hover:bg-red-200 text-red-800 font-extrabold text-xs rounded-xl transition-colors uppercase tracking-wider">
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

    <!-- ─── Modal Form (Edit User) ────────────────────── -->
    <Transition name="fade">
      <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="card w-full max-w-lg p-6 shadow-glass-lg animate-slide-up relative bg-white my-8">
          <!-- Close Button -->
          <button @click="closeModal" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors">
            <Icon name="mdi:close" class="text-xl" />
          </button>

          <h3 class="text-lg font-bold text-slate-800 mb-6">
            Edit Data Pengguna
          </h3>

          <form @submit.prevent="submitForm" class="space-y-4">
            <div class="space-y-4">
              <!-- Nama -->
              <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                <input v-model="modal.form.nama" type="text" required class="input-field" :disabled="modal.loading" />
              </div>

              <!-- Username -->
              <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Username</label>
                <input v-model="modal.form.username" type="text" required class="input-field" :disabled="modal.loading" />
              </div>

              <!-- Email -->
              <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Email</label>
                <input v-model="modal.form.email" type="email" required class="input-field" :disabled="modal.loading" />
              </div>

              <!-- Peran (Role) -->
              <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Peran (Role)</label>
                <select v-model="modal.form.role" required class="input-field" :disabled="modal.loading">
                  <option value="user">Customer</option>
                  <option value="admin">Admin</option>
                </select>
              </div>

              <!-- Nomor HP / Telepon -->
              <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Telepon / No HP</label>
                <input v-model="modal.form.no_hp" type="text" required class="input-field" :disabled="modal.loading" />
              </div>

              <!-- Alamat Lengkap (Opsional) -->
              <div>
                <label class="block text-slate-600 text-xs font-bold uppercase tracking-wider mb-1.5">Alamat Lengkap (Opsional)</label>
                <textarea v-model="modal.form.alamat" rows="3" class="input-field resize-none" :disabled="modal.loading"></textarea>
              </div>

              <!-- Password -->
              <div class="relative">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Password Baru (Opsional)</label>
                <div class="relative">
                  <input v-model="modal.form.password" :type="modal.showPassword ? 'text' : 'password'" placeholder="Kosongkan jika tidak ingin mengubah password" class="input-field !pr-10" :disabled="modal.loading" />
                  <button type="button" @click="modal.showPassword = !modal.showPassword" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                    <Icon :name="modal.showPassword ? 'mdi:eye-outline' : 'mdi:eye-off-outline'" class="text-lg" />
                  </button>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
              <button type="button" @click="closeModal" class="btn-secondary !py-2.5 !px-4 !text-sm" :disabled="modal.loading">
                Batal
              </button>
              <button type="submit" class="btn-primary !py-2.5 !px-4 !text-sm" :disabled="modal.loading">
                <span v-if="modal.loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <span v-else>Simpan Perubahan</span>
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

const users = ref<any[]>([])
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
  loading: false,
  id: null as number | null,
  form: {
    nama: '',
    username: '',
    email: '',
    no_hp: '',
    password: '',
    role: 'user',
    alamat: ''
  },
  showPassword: false
})

let searchTimeout: any = null
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchUsers()
  }, 500)
}

const fetchUsers = async () => {
  loading.value = true
  try {
    const res = await api.get('/users', {
      params: {
        search: search.value,
        page: pagination.value.current_page,
        per_page: pagination.value.per_page
      }
    })
    const data = res.data.data
    users.value = data.data
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
  fetchUsers()
}

const openEditModal = (user: any) => {
  modal.value = {
    show: true,
    loading: false,
    id: user.id,
    form: {
      nama: user.nama,
      username: user.username,
      email: user.email,
      no_hp: user.no_hp,
      password: '',
      role: user.role || 'user',
      alamat: user.alamat || ''
    },
    showPassword: false
  }
}

const closeModal = () => {
  modal.value.show = false
  modal.value.showPassword = false
  modal.value.id = null
  modal.value.form = {
    nama: '',
    username: '',
    email: '',
    no_hp: '',
    password: '',
    role: 'user',
    alamat: ''
  }
}

const submitForm = async () => {
  modal.value.loading = true
  
  const payload: Record<string, any> = {
    nama: modal.value.form.nama,
    username: modal.value.form.username,
    email: modal.value.form.email,
    no_hp: modal.value.form.no_hp,
    role: modal.value.form.role,
    alamat: modal.value.form.alamat
  }
  
  if (modal.value.form.password) {
    payload.password = modal.value.form.password
  }

  try {
    const res = await api.put(`/users/${modal.value.id}`, payload)
    $toast.fire({ icon: 'success', title: res.data.message || 'Data pengguna diperbarui.' })
    closeModal()
    fetchUsers()
  } catch (error: any) {
    const errors = error.response?.data?.errors
    let message = error.response?.data?.message || 'Gagal menyimpan data pengguna.'
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

const deleteUser = async (user: any) => {
  const confirm = await $swal.fire({
    title: 'Hapus Pengguna?',
    text: `Anda yakin ingin menghapus pengguna "${user.nama}"? Semua data riwayat pesanan pengguna ini mungkin akan terdampak.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  })

  if (!confirm.isConfirmed) return

  try {
    const res = await api.delete(`/users/${user.id}`)
    $toast.fire({ icon: 'success', title: res.data.message || 'Pengguna berhasil dihapus.' })
    fetchUsers()
  } catch (error: any) {
    const msg = error.response?.data?.message || 'Gagal menghapus pengguna.'
    $swal.fire({
      title: 'Gagal Hapus',
      text: msg,
      icon: 'error'
    })
  }
}

onMounted(() => {
  fetchUsers()
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
