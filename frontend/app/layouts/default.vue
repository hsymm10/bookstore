<template>
  <!-- Layout untuk Landing Page (Home, Our Story, dll.) -->
  <div class="min-h-screen flex flex-col">
    <TheNavbar />
    <main class="flex-1">
      <slot />
    </main>
    <TheFooter />
  </div>
</template>

<script setup lang="ts">
</script>
