<template>
  <div>
    <div class="text-base font-semibold mb-4">Log Notifikasi</div>

    <div v-if="loading" class="flex items-center justify-center py-16">
      <div class="flex items-center gap-3 text-gray-400">
        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
        <span class="text-sm">Memuat notifikasi...</span>
      </div>
    </div>

    <div v-else-if="logs.length === 0"
      class="flex flex-col items-center justify-center py-16 bg-white border border-dashed border-gray-200 rounded-2xl text-gray-400">
      <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
      </svg>
      <div class="text-sm font-medium">Belum ada notifikasi</div>
      <div class="text-xs mt-1 text-center px-8 text-gray-400">
        Notifikasi akan muncul saat PIC mendekati deadline proyek yang Anda input
      </div>
    </div>

    <div v-else class="grid grid-cols-2 gap-4">
      <!-- Hari ini -->
      <div>
        <div class="flex items-center gap-2 mb-3">
          <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Hari ini</div>
          <span v-if="today.length > 0" class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full font-medium">
            {{ today.length }} notif
          </span>
        </div>
        <div class="space-y-2">
          <div v-if="today.length === 0"
            class="text-sm text-gray-400 text-center py-6 bg-white border border-dashed border-gray-200 rounded-xl">
            Belum ada notifikasi hari ini
          </div>
          <div v-for="log in today" :key="log.id"
            :class="itemClass(log)"
            class="flex items-start gap-3 p-3 rounded-xl border">
            <div :class="iconClass(log)"
              class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
              <svg v-if="log.notif_type === 'early_warning'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-medium">
                  {{ log.notif_type === 'early_warning' ? 'Early Warning' : 'Alert' }}
                </span>
                <span :class="log.status === 'sent' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                  class="text-xs px-1.5 py-0.5 rounded-full">
                  {{ log.status === 'sent' ? 'Terkirim' : 'Gagal' }}
                </span>
              </div>
              <div class="text-xs font-medium text-gray-700 mt-0.5">{{ log.document?.nomor_dokumen }}</div>
              <div class="text-xs text-gray-500 mt-0.5">
                PIC: <span class="font-medium text-blue-600">{{ log.pic?.name }}</span>
              </div>
              <div v-if="log.document?.project?.name" class="mt-1">
                <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full border border-blue-100">
                  {{ log.document?.project?.name }}
                </span>
              </div>
            </div>
            <div class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0">{{ formatTime(log.sent_at) }}</div>
          </div>
        </div>
      </div>

      <!-- Sebelumnya -->
      <div>
        <div class="flex items-center gap-2 mb-3">
          <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Sebelumnya</div>
          <span v-if="previous.length > 0" class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full font-medium">
            {{ previous.length }} notif
          </span>
        </div>
        <div class="space-y-2 max-h-96 overflow-y-auto">
          <div v-if="previous.length === 0"
            class="text-sm text-gray-400 text-center py-6 bg-white border border-dashed border-gray-200 rounded-xl">
            Belum ada log sebelumnya
          </div>
          <div v-for="log in previous" :key="log.id"
            :class="itemClass(log)"
            class="flex items-start gap-3 p-3 rounded-xl border">
            <div :class="iconClass(log)"
              class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
              <svg v-if="log.notif_type === 'early_warning'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-medium">
                  {{ log.notif_type === 'early_warning' ? 'Early Warning' : 'Alert' }}
                </span>
                <span :class="log.status === 'sent' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                  class="text-xs px-1.5 py-0.5 rounded-full">
                  {{ log.status === 'sent' ? 'Terkirim' : 'Gagal' }}
                </span>
              </div>
              <div class="text-xs font-medium text-gray-700 mt-0.5">{{ log.document?.nomor_dokumen }}</div>
              <div class="text-xs text-gray-500 mt-0.5">
                PIC: <span class="font-medium text-blue-600">{{ log.pic?.name }}</span>
              </div>
              <div v-if="log.document?.project?.name" class="mt-1">
                <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full border border-blue-100">
                  {{ log.document?.project?.name }}
                </span>
              </div>
            </div>
            <div class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0">{{ formatTime(log.sent_at) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../axios'
import { useAuthStore } from '../../stores/auth'

const auth    = useAuthStore()
const logs    = ref([])
const loading = ref(false)

const today = computed(() => {
  const t = new Date().toDateString()
  return logs.value.filter(l => new Date(l.sent_at).toDateString() === t)
})

const previous = computed(() => {
  const t = new Date().toDateString()
  return logs.value.filter(l => new Date(l.sent_at).toDateString() !== t)
})

function formatTime(dt) {
  return new Date(dt).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

function itemClass(log) {
  if (log.status === 'failed') return 'bg-gray-50 border-gray-200'
  return log.notif_type === 'early_warning'
    ? 'bg-amber-50 border-amber-200'
    : 'bg-red-50 border-red-200'
}

function iconClass(log) {
  if (log.status === 'failed') return 'bg-gray-100 text-gray-500'
  return log.notif_type === 'early_warning'
    ? 'bg-amber-100 text-amber-700'
    : 'bg-red-100 text-red-700'
}

onMounted(async () => {
  loading.value = true
  try {
    // Ambil semua dokumen milik asisten ini
    const docRes  = await api.get('/documents')
    const myDocIds = docRes.data
      .filter(d => d.asisten_id === auth.user?.id)
      .map(d => d.id)

    if (myDocIds.length === 0) {
      logs.value = []
      return
    }

    // Ambil semua log lalu filter berdasarkan dokumen milik asisten
    const logRes  = await api.get('/notification-logs')
    logs.value = (logRes.data.data || []).filter(l =>
      myDocIds.includes(l.document_id)
    )
  } finally {
    loading.value = false
  }
})
</script>