<template>
  <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
    :class="scrolled ? 'bg-white/95 backdrop-blur-xl shadow-card border-b border-slate-100' : 'bg-transparent'">
    <div class="section-container">
      <div class="flex items-center justify-between h-16">

        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center gap-2 group">
          <Icon name="mdi:book-open-page-variant" class="text-2xl transition-colors" :class="scrolled ? 'text-[#2E5AA7]' : 'text-white'" />
          <span class="text-xl font-black tracking-tight transition-colors"
            :class="scrolled ? 'text-primary-700' : 'text-white'">
            BookStore
          </span>
        </NuxtLink>

        <!-- Desktop Nav Links -->
        <div class="hidden md:flex items-center gap-1">
          <NavLink v-for="link in navLinks" :key="link.href" :to="link.href" :scrolled="scrolled">
            {{ link.label }}
          </NavLink>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-3">
          <!-- Chat (untuk user yang login) -->
          <NuxtLink v-if="authStore.isLoggedIn && authStore.isUser" to="/chat"
            class="relative p-2 rounded-xl transition-all"
            :class="scrolled ? 'text-slate-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
            <ChatBubbleLeftRightIcon class="w-5 h-5" />
          </NuxtLink>

          <!-- Cart (untuk user yang login) -->
          <NuxtLink v-if="authStore.isLoggedIn && authStore.isUser" to="/cart"
            class="relative p-2 rounded-xl transition-all"
            :class="scrolled ? 'text-slate-600 hover:text-primary-600 hover:bg-primary-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
            <ShoppingCartIcon class="w-5 h-5" />
            <span v-if="cartStore.totalItems > 0"
              class="absolute -top-1 -right-1 w-5 h-5 bg-primary-600 text-white text-xs rounded-full flex items-center justify-center font-bold">
              {{ cartStore.totalItems }}
            </span>
          </NuxtLink>

          <!-- Login / Dashboard button -->
          <template v-if="!authStore.isLoggedIn">
            <NuxtLink to="/login"
              class="btn-primary text-sm py-2 px-4"
              :class="!scrolled && 'bg-white/15 hover:bg-white/25 border border-white/30 shadow-none'">
              Login
            </NuxtLink>
          </template>
          <template v-else>
            <NuxtLink :to="authStore.isAdmin ? '/admin/dashboard' : '/books'"
              class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all"
              :class="scrolled ? 'bg-primary-50 text-primary-700 hover:bg-primary-100' : 'bg-white/15 text-white hover:bg-white/25'">
              <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-xs font-bold text-white">
                {{ authStore.currentUser?.nama?.charAt(0)?.toUpperCase() }}
              </div>
              {{ authStore.currentUser?.nama?.split(' ')[0] }}
            </NuxtLink>
          </template>

          <!-- Mobile Menu Toggle -->
          <button @click="mobileOpen = !mobileOpen"
            class="md:hidden p-2 rounded-xl transition-colors"
            :class="scrolled ? 'text-slate-600 hover:bg-slate-100' : 'text-white hover:bg-white/10'">
            <Bars3Icon v-if="!mobileOpen" class="w-5 h-5" />
            <XMarkIcon v-else class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <Transition name="slide-down">
      <div v-if="mobileOpen" class="md:hidden bg-white border-t border-slate-100 shadow-lg">
        <div class="section-container py-3 flex flex-col gap-1">
          <NuxtLink v-for="link in navLinks" :key="link.href" :to="link.href"
            @click="mobileOpen = false"
            class="px-4 py-2.5 rounded-xl text-slate-700 hover:text-primary-600 hover:bg-primary-50 font-medium transition-colors">
            {{ link.label }}
          </NuxtLink>
          <NuxtLink v-if="authStore.isLoggedIn && authStore.isUser" to="/chat"
            @click="mobileOpen = false"
            class="px-4 py-2.5 rounded-xl text-slate-700 hover:text-primary-600 hover:bg-primary-50 font-medium transition-colors">
            Chat CS
          </NuxtLink>
        </div>
      </div>
    </Transition>
  </nav>
</template>

<script setup lang="ts">
import { ShoppingCartIcon, Bars3Icon, XMarkIcon, ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline'
import { useWindowScroll } from '@vueuse/core'

const authStore = useAuthStore()
const cartStore = useCartStore()

const { y }      = useWindowScroll()
const scrolled   = computed(() => y.value > 50)
const mobileOpen = ref(false)

const navLinks = [
  { href: '/',        label: 'Home' },
  { href: '/books',   label: 'Buku' },
  { href: '/#about',  label: 'Our Story' },
  { href: '/#contact',label: 'Contact' },
]
</script>

<script lang="ts">
// NavLink inline sub-component
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
