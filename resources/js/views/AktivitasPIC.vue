<template>
  <div>
    <div class="text-base font-semibold mb-4">Aktivitas PIC</div>
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50">
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium w-10">#</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Nama PIC</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Project</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Nomor Dokumen</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Atasan</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Deadline</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Return Actual</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Status</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="(group, idx) in grouped" :key="group.pic_id">
            <tr v-for="(doc, di) in group.docs" :key="doc.id"
              :class="[
                di === 0 && idx % 2 === 0 ? 'bg-white' : '',
                di === 0 && idx % 2 !== 0 ? 'bg-gray-50/50' : '',
                di !== 0 && idx % 2 === 0 ? 'bg-white' : '',
                di !== 0 && idx % 2 !== 0 ? 'bg-gray-50/50' : '',
                'border-b border-gray-100 hover:bg-amber-50/30 transition'
              ]">
              <!-- No — hanya di baris pertama tiap PIC -->
              <td class="px-4 py-3 text-gray-400 text-xs" :rowspan="di === 0 ? group.docs.length : undefined"
                v-if="di === 0" :style="di === 0 ? 'vertical-align:top;padding-top:14px' : ''">
                {{ String(idx + 1).padStart(2,'0') }}
              </td>

              <!-- Nama PIC — hanya di baris pertama, rowspan -->
              <td v-if="di === 0" :rowspan="group.docs.length"
                class="px-4 py-3 border-r border-gray-100"
                style="vertical-align:top;padding-top:12px">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0"
                    :style="avatarStyle(group.pic_name)">
                    {{ initials(group.pic_name) }}
                  </div>
                  <div>
                    <div class="font-medium text-gray-900 text-sm">{{ group.pic_name }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ group.docs.length }} dokumen</div>
                  </div>
                </div>
              </td>

              <!-- Project -->
              <td class="px-4 py-3">
                <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full border border-blue-100">
                  {{ doc.project?.name }}
                </span>
              </td>

              <!-- Nomor Dokumen -->
              <td class="px-4 py-3 font-medium text-gray-900">{{ doc.nomor_dokumen }}</td>

              <!-- Atasan -->
              <td class="px-4 py-3 text-gray-500 text-xs">{{ doc.atasan?.name }}</td>

              <!-- Deadline -->
              <td class="px-4 py-3 font-medium text-xs" :class="deadlineColor(doc)">
                {{ formatDeadline(doc.review_deadline) }}
              </td>

              <!-- Return Actual -->
              <td class="px-4 py-3 text-xs" :class="doc.return_actual_date ? 'text-green-600 font-medium' : 'text-gray-300'">
                {{ doc.return_actual_date ? formatDeadline(doc.return_actual_date) : '—' }}
              </td>

              <!-- Status -->
              <td class="px-4 py-3">
                <StatusBadge :status="doc.status" />
              </td>
            </tr>

            <!-- Garis pemisah antar PIC -->
            <tr v-if="idx < grouped.length - 1">
              <td colspan="8" class="p-0">
                <div class="h-0.5 bg-gray-100"></div>
              </td>
            </tr>
          </template>

          <tr v-if="grouped.length === 0">
            <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">
              Tidak ada dokumen
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../axios'
import StatusBadge from '../components/StatusBadge.vue'

const docs = ref([])

// Kelompokkan dokumen per PIC
const grouped = computed(() => {
  const map = {}
  docs.value.forEach(doc => {
    const id = doc.pic_id
    if (!map[id]) {
      map[id] = {
        pic_id:   id,
        pic_name: doc.pic?.name || '—',
        docs:     []
      }
    }
    map[id].docs.push(doc)
  })
  return Object.values(map)
})

const avatarColors = [
  { bg: '#EBF2FB', color: '#1B5FA8' },
  { bg: '#FEF3DC', color: '#C8820A' },
  { bg: '#E8F5EE', color: '#2E7D52' },
  { bg: '#FDECEC', color: '#B83232' },
  { bg: '#F1EFF8', color: '#5E4EA0' },
]

function avatarStyle(name) {
  if (!name) return { background: '#F1EFF8', color: '#5E4EA0' }
  const idx = name.charCodeAt(0) % avatarColors.length
  return { background: avatarColors[idx].bg, color: avatarColors[idx].color }
}

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

function formatDeadline(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric'
  })
}

function deadlineColor(doc) {
  if (doc.status === 'alert')         return 'text-red-600'
  if (doc.status === 'early_warning') return 'text-amber-600'
  return 'text-gray-700'
}

onMounted(async () => {
  const res  = await api.get('/documents')
  docs.value = res.data
})
</script>