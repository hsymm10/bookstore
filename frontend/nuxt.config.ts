// https://nuxt.com/docs/api/configuration/nuxt-config
import { defineNuxtConfig } from 'nuxt/config'

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  // ─── KRITIS: Matikan SSR sepenuhnya ─────────────────────────────────────────
  // Aplikasi ini adalah SPA yang menggunakan Laravel Sanctum token-based auth.
  // SSR menyebabkan Pinia store di-reset setiap request (server tidak punya
  // browser cookie → store null → middleware menganggap user belum login →
  // redirect paksa ke halaman yang salah). Mode SPA adalah solusi yang benar.
  ssr: false,

  // Modules
  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
    '@vueuse/nuxt',
    '@nuxt/icon'
  ],

  // Component Auto-imports
  components: [
    { path: '~/components/layout', pathPrefix: false },
    { path: '~/components/book', pathPrefix: false },
    { path: '~/components', pathPrefix: false },
  ],

  // Runtime Config (accessible in app)
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api',
    },
  },

  // Auto imports for stores
  imports: {
    dirs: ['app/stores'],
  },

  // CSS
  css: ['~/assets/css/main.css'],

  // App Head
  app: {
    head: {
      title: 'BookStore — Toko Buku Terpercaya',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'BookStore - Temukan buku favoritmu dengan harga terbaik' },
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
        },
      ],
    },
  },
})
