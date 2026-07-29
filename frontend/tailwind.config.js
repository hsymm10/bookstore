/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './app/**/*.{vue,js,ts}',
    './components/**/*.{vue,js,ts}',
    './layouts/**/*.{vue,js,ts}',
    './pages/**/*.{vue,js,ts}',
    './plugins/**/*.{js,ts}',
    './composables/**/*.{js,ts}',
    './nuxt.config.{js,ts}',
    './error.vue',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
        inter: ['Inter', 'sans-serif'],
      },
      colors: {
        // ── Amalfi Coast Palette ─────────────────────────
        citrus:  {
          50:  '#fff9eb',
          100: '#fef0c7',
          200: '#fde28a',
          300: '#ffd04d',
          400: '#ffba1a',
          500: '#FFA62B',   // Citrus Zest (brand)
          600: '#e08500',
          700: '#b86400',
          800: '#964f00',
          900: '#7a4200',
        },
        ocean:   {
          50:  '#f0f7ff',
          100: '#daeeff',
          200: '#bddfff',
          300: '#86C5FF',   // Sea Breeze (accent)
          400: '#5aaff5',
          500: '#3498e6',
          600: '#2E5AA7',   // Amalfi Tile (primary)
          700: '#254b8e',
          800: '#1e3d75',
          900: '#17305c',
          950: '#0e1f3d',
        },
        cream:   {
          50:  '#fffef7',
          100: '#F8E6A0',   // Cream Gelato (background accent)
          200: '#f5d860',
          300: '#f0c820',
          400: '#d4ab00',
          500: '#b08e00',
        },
        // primary alias → ocean-600
        primary: {
          50:  '#f0f7ff',
          100: '#daeeff',
          200: '#bddfff',
          300: '#86C5FF',
          400: '#5aaff5',
          500: '#3498e6',
          600: '#2E5AA7',
          700: '#254b8e',
          800: '#1e3d75',
          900: '#17305c',
          950: '#0e1f3d',
        },
      },
      backgroundImage: {
        'gradient-radial':  'radial-gradient(var(--tw-gradient-stops))',
        'hero-gradient':    'linear-gradient(135deg, #0e1f3d 0%, #2E5AA7 55%, #1e3d75 100%)',
        'hero-gradient-v2': 'linear-gradient(160deg, #2E5AA7 0%, #FFA62B 100%)',
        'card-gradient':    'linear-gradient(135deg, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0.06) 100%)',
        'sidebar-gradient': 'linear-gradient(180deg, #0e1f3d 0%, #2E5AA7 60%, #17305c 100%)',
        'citrus-gradient':  'linear-gradient(135deg, #FFA62B 0%, #ffd04d 100%)',
        'cream-gradient':   'linear-gradient(135deg, #F8E6A0 0%, #fde28a 100%)',
      },
      boxShadow: {
        glass:       '0 8px 32px rgba(46,90,167,0.18)',
        'glass-lg':  '0 16px 48px rgba(46,90,167,0.25)',
        card:        '0 4px 24px rgba(46,90,167,0.10)',
        'card-hover':'0 8px 40px rgba(255,166,43,0.22)',
        sidebar:     '4px 0 24px rgba(14,31,61,0.20)',
        citrus:      '0 4px 20px rgba(255,166,43,0.35)',
      },
      borderRadius: {
        '2xl': '1rem',
        '3xl': '1.5rem',
        '4xl': '2rem',
      },
      animation: {
        'fade-in':    'fadeIn 0.5s ease-in-out',
        'slide-up':   'slideUp 0.5s ease-out',
        'slide-in':   'slideIn 0.4s ease-out',
        'pulse-slow': 'pulse 3s cubic-bezier(0.4,0,0.6,1) infinite',
        'float':      'float 6s ease-in-out infinite',
        'shimmer':    'shimmer 2s linear infinite',
      },
      keyframes: {
        fadeIn:  { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
        slideUp: { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
        slideIn: { '0%': { opacity: '0', transform: 'translateX(-20px)' }, '100%': { opacity: '1', transform: 'translateX(0)' } },
        float:   { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-12px)' } },
        shimmer: { '0%': { backgroundPosition: '-1000px 0' }, '100%': { backgroundPosition: '1000px 0' } },
      },
    },
  },
  plugins: [],
}