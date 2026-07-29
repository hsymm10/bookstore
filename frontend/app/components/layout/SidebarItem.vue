<template>
  <NuxtLink :to="item.href"
    class="sidebar-item"
    :class="{ 'active': route.path === item.href || (item.href !== '/admin/dashboard' && route.path.startsWith(item.href)) }">
    <Icon :name="item.icon" class="w-5 h-5 flex-shrink-0" />
    <span>{{ item.label }}</span>
  </NuxtLink>
</template>

<script setup lang="ts">
defineProps<{ item: { href: string; label: string; icon: string } }>()
const route = useRoute()
</script>

