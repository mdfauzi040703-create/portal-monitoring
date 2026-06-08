<template>
  <transition name="slide-up">
    <div v-if="show"
      :class="{
        'bg-green-600': type === 'success',
        'bg-red-600':   type === 'error',
        'bg-amber-500': type === 'warning',
        'bg-blue-600':  type === 'info',
      }"
      class="fixed bottom-5 right-5 text-white text-sm px-4 py-3 rounded-xl shadow-lg z-[9999] flex items-center gap-2 min-w-48">
      <svg v-if="type === 'success'" class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
      </svg>
      <svg v-else-if="type === 'error'" class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
      <svg v-else-if="type === 'warning'" class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
      <svg v-else class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      {{ message }}
    </div>
  </transition>
</template>

<script setup>
defineProps({
  show:    { type: Boolean, default: false },
  message: { type: String,  default: '' },
  type:    { type: String,  default: 'success' },
})
</script>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active { transition: all .3s ease; }
.slide-up-enter-from { opacity: 0; transform: translateY(20px); }
.slide-up-leave-to  { opacity: 0; transform: translateY(20px); }
</style>