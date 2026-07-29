<script setup lang="ts">
definePageMeta({
  middleware: 'admin'
})
await navigateTo('/admin/dashboard')
</script>

<template>
  <div>Redirecting to dashboard...</div>
</template>
