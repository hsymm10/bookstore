<template>
  <footer class="bg-gradient-to-br from-slate-900 via-primary-950 to-slate-900 text-slate-300">
    <div class="section-container py-16">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-10">

        <!-- Brand -->
        <div class="md:col-span-2">
          <div class="flex items-center gap-2 mb-4">
            <Icon name="mdi:book-open-page-variant" class="text-3xl text-citrus-400" />
            <span class="text-2xl font-black text-white tracking-tight">BookStore</span>
          </div>
          <p class="text-slate-400 text-sm leading-relaxed max-w-xs">
            Toko buku terpercaya dengan koleksi lengkap dari berbagai genre. Temukan buku favoritmu dengan harga terbaik.
          </p>
          <!-- Social -->
          <div class="flex gap-3 mt-6">
            <a v-for="s in socials" :key="s.label" :href="s.href"
              class="w-9 h-9 rounded-xl bg-white/10 hover:bg-primary-600 flex items-center justify-center text-white transition-all duration-200 hover:scale-110">
              <Icon :name="s.icon" class="text-lg" />
            </a>
          </div>
        </div>

        <!-- Links -->
        <div>
          <h3 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Navigasi</h3>
          <ul class="space-y-2">
            <li v-for="link in navLinks" :key="link.href">
              <NuxtLink :to="link.href"
                class="text-slate-400 hover:text-primary-400 text-sm transition-colors">
                {{ link.label }}
              </NuxtLink>
            </li>
          </ul>
        </div>

        <!-- Contact -->
        <div>
          <h3 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Kontak</h3>
          <ul class="space-y-2 text-sm text-slate-400">
            <li class="flex items-center gap-2"><Icon name="mdi:map-marker" class="text-[#86C5FF]" /> Jakarta, Indonesia</li>
            <li class="flex items-center gap-2"><Icon name="mdi:email" class="text-[#86C5FF]" /> info@bookstore.com</li>
            <li class="flex items-center gap-2"><Icon name="mdi:phone" class="text-[#86C5FF]" /> +62 812-3456-7890</li>
          </ul>
        </div>
      </div>

      <div class="border-t border-white/10 mt-12 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-slate-500 text-sm">
          © {{ new Date().getFullYear() }} BookStore. All rights reserved.
        </p>
        <p class="text-slate-500 text-sm flex items-center gap-1">Made with <Icon name="mdi:heart" class="text-rose-500" /> for book lovers</p>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
const navLinks = [
  { href: '/',       label: 'Home' },
  { href: '/books',  label: 'Koleksi Buku' },
  { href: '/#about', label: 'Our Story' },
  { href: '/login',  label: 'Login' },
]

const socials = [
  { label: 'Instagram', href: '#', icon: 'mdi:instagram' },
  { label: 'Twitter',   href: '#', icon: 'mdi:twitter' },
  { label: 'Facebook',  href: '#', icon: 'mdi:facebook' },
]
</script>
