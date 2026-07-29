<template>
  <!-- Layout untuk halaman Auth (Login, Register, Forgot Password) -->
  <div class="min-h-screen bg-hero-gradient flex flex-col lg:flex-row relative overflow-hidden">
    <!-- Header Navigation at top-right -->
    <header class="absolute top-6 right-6 lg:right-12 z-50 flex items-center gap-6 text-xs md:text-sm font-extrabold uppercase tracking-widest text-white/80">
      <NuxtLink to="/login" class="hover:text-citrus-400 transition-colors">Login</NuxtLink>
      <NuxtLink to="/" class="hover:text-citrus-400 transition-colors">Our Story</NuxtLink>
      <NuxtLink to="/" class="hover:text-citrus-400 transition-colors">Blog</NuxtLink>
      <NuxtLink to="/" class="hover:text-citrus-400 transition-colors">Contact</NuxtLink>
    </header>

    <!-- LEFT SIDE: Mascot & Decorative Blobs (hidden on mobile, visible on lg) -->
    <div class="hidden lg:flex lg:w-1/2 relative flex-col items-center justify-center p-12">
      <!-- Glow blobs -->
      <div class="absolute top-10 left-10 w-92 h-92 bg-white/5 rounded-full blur-3xl" />
      <div class="absolute bottom-20 right-10 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl animate-pulse-slow" />
      
      <!-- Mascot image -->
      <div class="relative z-10 animate-float max-w-md">
        <img src="/login_mascot.png" alt="BookStore Mascot" class="w-full h-auto drop-shadow-glass-lg" />
      </div>

      <!-- Floating small circles -->
      <div v-for="i in 6" :key="i"
        class="absolute w-4 h-4 rounded-full bg-white/10 blur-[1px] animate-float"
        :style="`
          top: ${15 + i * 12}%;
          left: ${10 + i * 10}%;
          animation-delay: ${i * 0.4}s;
          animation-duration: ${5 + i}s;
        `"
      />
    </div>

    <!-- RIGHT SIDE: Auth Form Container -->
    <div class="flex-1 flex flex-col items-center justify-center p-6 md:p-12 relative z-10 lg:bg-[#0e1f3d]/10 lg:backdrop-blur-sm">
      <!-- App Brand Logo (visible above form) -->
      <div class="mb-8 flex flex-col items-center">
        <NuxtLink to="/" class="flex items-center gap-3 group">
          <div class="w-10 h-10 rounded-xl bg-citrus-gradient flex items-center justify-center shadow-citrus">
            <Icon name="mdi:book-open-page-variant" class="text-ocean-950 text-xl" />
          </div>
          <span class="text-3xl font-black text-white tracking-tight group-hover:text-citrus-300 transition-colors">
            BookStore
          </span>
        </NuxtLink>
      </div>

      <!-- Main Form Card wrapper -->
      <div class="w-full max-w-md">
        <slot />
      </div>
    </div>
  </div>
</template>

