<template>
  <div v-if="show" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[9999]">
    <div class="bg-white rounded-2xl p-6 w-80 border border-gray-200 shadow-xl">
      <div class="flex items-center gap-3 mb-3">
        <div :class="type === 'danger' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600'"
          class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0">
          <svg v-if="type === 'danger'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
        </div>
        <div>
          <div class="text-sm font-semibold text-gray-900">{{ title }}</div>
          <div class="text-xs text-gray-500 mt-0.5">{{ message }}</div>
        </div>
      </div>
      <div class="flex gap-2 justify-end mt-4">
        <button @click="cancel"
          class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
          Batal
        </button>
        <button @click="confirm"
          :class="type === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-amber-500 hover:bg-amber-600'"
          class="text-sm px-4 py-2 text-white rounded-lg transition">
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show:        { type: Boolean, default: false },
  title:       { type: String,  default: 'Konfirmasi' },
  message:     { type: String,  default: 'Apakah anda yakin?' },
  confirmText: { type: String,  default: 'Ya, Lanjutkan' },
  type:        { type: String,  default: 'danger' },
})
const emit = defineEmits(['confirm','cancel'])
const confirm = () => emit('confirm')
const cancel  = () => emit('cancel')
</script>