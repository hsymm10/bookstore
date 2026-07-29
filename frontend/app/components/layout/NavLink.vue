<template>
  <NuxtLink :to="to"
    class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200"
    :class="active ?
      (scrolled ? 'bg-primary-50 text-primary-700' : 'bg-white/10 text-white') :
      (scrolled ? 'text-slate-600 hover:text-primary-600 hover:bg-slate-50' : 'text-white/80 hover:text-white hover:bg-white/5')
    "
  >
    <slot />
  </NuxtLink>
</template>

<script setup lang="ts">
const props = defineProps<{
  to: string
  scrolled?: boolean
}>()

const route = useRoute()
const active = computed(() => {
  if (props.to === '/') return route.path === '/'
  return route.path.startsWith(props.to)
})
</script>
