<script setup lang="ts">
const authStore = useAuthStore()
authStore.restoreFromCookie()

if (authStore.isAdmin) {
  await navigateTo('/admin/books')
} else if (authStore.isUser) {
  await navigateTo('/user/transaksi')
} else {
  await navigateTo('/login')
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center font-sans">
    <div class="flex flex-col items-center gap-3">
      <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
      <p class="text-sm font-semibold text-slate-500">Mengarahkan...</p>
    </div>
  </div>
</template>
