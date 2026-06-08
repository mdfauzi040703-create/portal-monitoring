<template>
  <div>
    <!-- Greeting -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-5 mb-5 text-white">
      <div class="text-lg font-semibold">Halo, {{ auth.user?.name }}! 👋</div>
      <div class="text-blue-100 text-sm mt-1">{{ currentDate }}</div>
      <div class="flex gap-3 mt-4">
        <div class="bg-white/20 rounded-xl px-4 py-2 text-center">
          <div class="text-2xl font-bold">{{ summary.total }}</div>
          <div class="text-xs text-blue-100">Total Proyek</div>
        </div>
        <div class="bg-white/20 rounded-xl px-4 py-2 text-center">
          <div class="text-2xl font-bold">{{ summary.proses }}</div>
          <div class="text-xs text-blue-100">Belum Selesai</div>
        </div>
        <div class="bg-white/20 rounded-xl px-4 py-2 text-center">
          <div class="text-2xl font-bold">{{ summary.selesai }}</div>
          <div class="text-xs text-blue-100">Selesai</div>
        </div>
      </div>
    </div>

    <!-- Warning cards -->
    <div v-if="warnings.length > 0" class="mb-5">
      <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Perhatian — Segera Diselesaikan</div>
      <div class="space-y-2">
        <div v-for="doc in warnings" :key="doc.id"
          :class="doc.status === 'alert' ? 'bg-red-50 border-red-200' : 'bg-amber-50 border-amber-200'"
          class="flex items-center gap-3 p-3 border rounded-xl">
          <div :class="doc.status === 'alert' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600'"
            class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <div class="flex-1">
            <div class="text-sm font-medium">{{ doc.nomor_dokumen }}</div>
            <div class="text-xs text-gray-500">{{ doc.project?.name }} — Deadline: {{ formatDate(doc.review_deadline) }}</div>
          </div>
          <StatusBadge :status="doc.status" />
        </div>
      </div>
    </div>

    <!-- Semua proyek -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100">
        <div class="text-sm font-medium">Semua Proyek Saya</div>
      </div>
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50">
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">#</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Nomor Dokumen</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Project</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Deadline</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Return Actual</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(doc, i) in myDocs" :key="doc.id"
            class="border-b border-gray-50 hover:bg-gray-50 transition">
            <td class="px-4 py-3 text-gray-400 text-xs">{{ String(i+1).padStart(3,'0') }}</td>
            <td class="px-4 py-3 font-medium">{{ doc.nomor_dokumen }}</td>
            <td class="px-4 py-3">
              <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full border border-blue-100">
                {{ doc.project?.name }}
              </span>
            </td>
            <td class="px-4 py-3 text-xs font-medium" :class="deadlineColor(doc)">{{ formatDate(doc.review_deadline) }}</td>
            <td class="px-4 py-3 text-xs" :class="doc.return_actual_date ? 'text-green-600 font-medium' : 'text-gray-300'">
              {{ doc.return_actual_date ? formatDate(doc.return_actual_date) : '—' }}
            </td>
            <td class="px-4 py-3"><StatusBadge :status="doc.status" /></td>
          </tr>
          <tr v-if="myDocs.length === 0">
            <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">
              Anda tidak memiliki proyek hari ini
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../axios'
import StatusBadge from '../../components/StatusBadge.vue'
import { useAuthStore } from '../../stores/auth'

const auth   = useAuthStore()
const myDocs = ref([])

const summary = computed(() => ({
  total:   myDocs.value.length,
  proses:  myDocs.value.filter(d => !d.return_actual_date).length,
  selesai: myDocs.value.filter(d => d.return_actual_date).length,
}))

const warnings = computed(() =>
  myDocs.value.filter(d => d.status === 'alert' || d.status === 'early_warning')
)

const currentDate = computed(() =>
  new Date().toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' })
)

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
}

function deadlineColor(doc) {
  if (doc.status === 'alert')         return 'text-red-600'
  if (doc.status === 'early_warning') return 'text-amber-600'
  return 'text-gray-700'
}

onMounted(async () => {
  const res = await api.get('/documents')
  myDocs.value = res.data.filter(d => d.pic_id === auth.user?.id)
})
</script>