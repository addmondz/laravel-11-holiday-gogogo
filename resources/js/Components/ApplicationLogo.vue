<template>
    <div class="w-full min-h-[200px] relative">
      <img
        :class="['w-full h-full object-contain select-none', loading ? 'opacity-0' : 'opacity-100']"
        @load="loading = false"
        src="/images/Logo-Rectangular-for-website-transparent.png"
        alt="Logo"
        draggable="false"
        class="overflow-hidden"
      />
      <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-gray-100">
        <!-- Loading spinner -->
        <span class="text-sm text-gray-500">&nbsp;</span>
      </div>
    </div>
  </template>

  <script setup>
  import { ref } from 'vue'

  const loading = ref(true)
  </script>
