<template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
// Restore auth dari cookie saat app mount
const authStore = useAuthStore()
authStore.restoreFromCookie()
</script>
