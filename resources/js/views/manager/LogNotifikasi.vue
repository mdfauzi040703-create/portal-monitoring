<template>
  <div>
    <div class="text-base font-semibold mb-4">Log Notifikasi Terbaru</div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Hari ini</div>
        <div class="space-y-2">
          <div v-for="log in today" :key="log.id" :class="itemClass(log.notif_type)"
            class="flex items-start gap-3 p-3 rounded-xl border">
            <div :class="iconClass(log.notif_type)" class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-sm">
              {{ log.notif_type === 'early_warning' ? '⏰' : '🔔' }}
            </div>
            <div class="flex-1">
              <div class="text-sm font-medium">{{ log.notif_type === 'early_warning' ? 'Early Warning' : 'Alert' }} → {{ log.pic?.name }}</div>
              <div class="text-xs text-gray-500 mt-0.5">{{ log.document?.nomor_dokumen }} — via Email & WA</div>
            </div>
            <div class="text-xs text-gray-400 whitespace-nowrap">{{ formatTime(log.sent_at) }}</div>
          </div>
          <div v-if="today.length === 0" class="text-sm text-gray-400 text-center py-6">Belum ada notifikasi hari ini</div>
        </div>
      </div>
      <div>
        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Sebelumnya</div>
        <div class="space-y-2">
          <div v-for="log in previous" :key="log.id" :class="itemClass(log.notif_type)"
            class="flex items-start gap-3 p-3 rounded-xl border">
            <div :class="iconClass(log.notif_type)" class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-sm">
             <svg v-if="log.notif_type === 'early_warning'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
</svg>
<svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
</svg>
            </div>
            <div class="flex-1">
              <div class="text-sm font-medium">{{ log.notif_type === 'early_warning' ? 'Early Warning' : 'Alert' }} → {{ log.pic?.name }}</div>
              <div class="text-xs text-gray-500 mt-0.5">{{ log.document?.nomor_dokumen }}</div>
            </div>
            <div class="text-xs text-gray-400 whitespace-nowrap">{{ formatTime(log.sent_at) }}</div>
          </div>
          <div v-if="previous.length === 0" class="text-sm text-gray-400 text-center py-6">Belum ada log</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../axios'

const logs = ref([])

const today = computed(() => {
  const t = new Date().toDateString()
  return logs.value.filter(l => new Date(l.sent_at).toDateString() === t)
})

const previous = computed(() => {
  const t = new Date().toDateString()
  return logs.value.filter(l => new Date(l.sent_at).toDateString() !== t)
})

function formatTime(dt) {
  return new Date(dt).toLocaleString('id-ID', { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' })
}

function itemClass(type) {
  return type === 'early_warning'
    ? 'bg-amber-50 border-amber-200'
    : 'bg-red-50 border-red-200'
}

function iconClass(type) {
  return type === 'early_warning'
    ? 'bg-amber-100 text-amber-700'
    : 'bg-red-100 text-red-700'
}

onMounted(async () => {
  const res = await api.get('/notification-logs')
  logs.value = res.data.data
})
</script>

