<template>
  <div>
    <!-- Summary cards -->
    <div class="grid grid-cols-4 gap-3 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Total Proyek</div>
        <div class="text-3xl font-semibold text-gray-900">{{ summary.total }}</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Menunggu Assign</div>
        <div class="text-3xl font-semibold text-amber-600">{{ summary.waiting }}</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Sedang Berjalan</div>
        <div class="text-3xl font-semibold text-blue-600">{{ summary.running }}</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Selesai</div>
        <div class="text-3xl font-semibold text-green-600">{{ summary.done }}</div>
      </div>
    </div>

    <!-- Warning banner -->
    <div v-if="summary.waiting > 0"
      class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-center gap-3">
      <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
      <div>
        <div class="text-sm font-medium text-amber-700">{{ summary.waiting }} proyek menunggu di-assign ke PIC</div>
        <div class="text-xs text-amber-600 mt-0.5">Segera buka menu Proyek Masuk untuk assign.</div>
      </div>
      <router-link to="/manager/proyek-masuk"
        class="ml-auto text-xs bg-amber-500 text-white px-3 py-1.5 rounded-lg hover:bg-amber-600 transition whitespace-nowrap">
        Lihat sekarang →
      </router-link>
    </div>

    <!-- Pie charts -->
    <div class="grid grid-cols-3 gap-4 mb-5">
      <!-- Total keseluruhan -->
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-sm font-medium mb-4">Total Gambar</div>
        <canvas ref="pieTotal" width="180" height="180" class="mx-auto block"></canvas>
        <div class="mt-4 space-y-2">
          <div class="flex items-center gap-2 text-xs">
            <div class="w-3 h-3 rounded-full bg-green-500"></div>
            <span class="text-gray-500 flex-1">Selesai</span>
            <span class="font-medium">{{ summary.done }}</span>
          </div>
          <div class="flex items-center gap-2 text-xs">
            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
            <span class="text-gray-500 flex-1">Sedang Berjalan</span>
            <span class="font-medium">{{ summary.running }}</span>
          </div>
          <div class="flex items-center gap-2 text-xs">
            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
            <span class="text-gray-500 flex-1">Menunggu Assign</span>
            <span class="font-medium">{{ summary.waiting }}</span>
          </div>
          <div class="flex items-center gap-2 text-xs">
            <div class="w-3 h-3 rounded-full bg-red-500"></div>
            <span class="text-gray-500 flex-1">Alert</span>
            <span class="font-medium">{{ summary.alert }}</span>
          </div>
        </div>
      </div>

      <!-- Per project -->
      <div class="col-span-2 bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-sm font-medium mb-4">Per Project</div>
        <div v-if="projects.length === 0"
          class="flex flex-col items-center justify-center py-8 border border-dashed border-gray-200 rounded-xl text-gray-400">
          <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7h18M3 12h18M3 17h18"/></svg>
          <p class="text-sm">Tidak ada project</p>
        </div>
        <div v-else class="grid grid-cols-3 gap-3">
          <div v-for="p in projects" :key="p.id"
            @click="openDetail(p)"
            class="border border-gray-200 rounded-xl p-3 flex flex-col items-center gap-2 cursor-pointer hover:border-amber-300 hover:shadow-sm transition">
            <div class="text-xs font-semibold text-center">{{ p.name }}</div>
            <canvas :ref="el => setPieRef(el, p.id)" width="90" height="90"></canvas>
            <div class="text-xs text-gray-500">{{ p.selesai_count }}/{{ p.total_count }} selesai</div>
            <div class="flex gap-1 flex-wrap justify-center">
              <span v-if="p.selesai_count" class="text-xs bg-green-50 text-green-700 border border-green-100 px-1.5 py-0.5 rounded-full">{{ p.selesai_count }} selesai</span>
              <span v-if="p.warning_count" class="text-xs bg-amber-50 text-amber-700 border border-amber-100 px-1.5 py-0.5 rounded-full">{{ p.warning_count }} H-1</span>
              <span v-if="p.alert_count"   class="text-xs bg-red-50 text-red-700 border border-red-100 px-1.5 py-0.5 rounded-full">{{ p.alert_count }} alert</span>
            </div>
            <div class="text-xs text-gray-400 opacity-0 group-hover:opacity-100">Klik untuk detail</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel proyek berjalan -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <div class="text-sm font-medium">Semua Proyek Berjalan</div>
        <select v-model="filterStatus" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white focus:outline-none">
          <option value="">Semua</option>
          <option value="pending">Pending</option>
          <option value="early_warning">Early Warning</option>
          <option value="alert">Alert</option>
          <option value="selesai">Selesai</option>
        </select>
      </div>
      <div v-if="loadingTable" class="flex items-center justify-center py-10">
        <svg class="animate-spin w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
      </div>
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50">
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">#</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Nomor Dokumen</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Project</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Dari Asisten</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">PIC</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Deadline</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Return Actual</th>
            <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(doc, i) in filteredDocs" :key="doc.id"
            :class="doc.status === 'alert' ? 'bg-red-50/20' : doc.status === 'early_warning' ? 'bg-amber-50/20' : ''"
            class="border-b border-gray-50 hover:bg-gray-50 transition">
            <td class="px-4 py-3 text-gray-400 text-xs">{{ String(i+1).padStart(3,'0') }}</td>
            <td class="px-4 py-3 font-medium text-xs">{{ doc.nomor_dokumen }}</td>
            <td class="px-4 py-3">
              <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full border border-blue-100">
                {{ doc.project?.name }}
              </span>
            </td>
            <td class="px-4 py-3 text-xs text-gray-500">{{ doc.asisten?.name || '—' }}</td>
            <td class="px-4 py-3">
              <div v-if="doc.pic" class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-semibold">
                  {{ initials(doc.pic?.name) }}
                </div>
                <span class="text-xs">{{ doc.pic?.name }}</span>
              </div>
              <span v-else class="text-xs text-gray-300">Belum assign</span>
            </td>
            <td class="px-4 py-3 text-xs font-medium" :class="deadlineColor(doc)">
              {{ doc.review_deadline ? formatDate(doc.review_deadline) : '—' }}
            </td>
            <td class="px-4 py-3 text-xs" :class="doc.return_actual_date ? 'text-green-600 font-medium' : 'text-gray-300'">
              {{ doc.return_actual_date ? formatDate(doc.return_actual_date) : '—' }}
            </td>
            <td class="px-4 py-3"><StatusBadge :status="doc.status" /></td>
          </tr>
          <tr v-if="filteredDocs.length === 0">
            <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">Tidak ada proyek</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal detail project -->
    <div v-if="showDetail" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl border border-gray-200 shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
          <div>
            <div class="text-base font-semibold">{{ selectedProject?.name }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Detail dokumen dalam project ini</div>
          </div>
          <button @click="showDetail = false"
            class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Summary mini -->
        <div class="px-6 py-3 border-b border-gray-100 flex gap-4 flex-shrink-0 flex-wrap">
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-green-500"></div>
            <span class="text-xs text-gray-500">Selesai:</span>
            <span class="text-xs font-semibold text-green-600">{{ detailDocs.filter(d=>d.status==='selesai').length }}</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
            <span class="text-xs text-gray-500">Early Warning:</span>
            <span class="text-xs font-semibold text-amber-600">{{ detailDocs.filter(d=>d.status==='early_warning').length }}</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
            <span class="text-xs text-gray-500">Alert:</span>
            <span class="text-xs font-semibold text-red-600">{{ detailDocs.filter(d=>d.status==='alert').length }}</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-gray-300"></div>
            <span class="text-xs text-gray-500">Total:</span>
            <span class="text-xs font-semibold text-gray-700">{{ detailDocs.length }}</span>
          </div>
        </div>

        <!-- Tab filter -->
        <div class="px-6 py-2 border-b border-gray-100 flex gap-2 flex-shrink-0">
          <button v-for="tab in detailTabs" :key="tab.value"
            @click="activeDetailTab = tab.value"
            :class="activeDetailTab === tab.value ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-500 hover:bg-gray-50'"
            class="text-xs px-3 py-1.5 rounded-lg transition">
            {{ tab.label }}
            <span class="ml-1 font-semibold">{{ detailDocs.filter(d => tab.value === 'semua' || d.status === tab.value).length }}</span>
          </button>
        </div>

        <!-- Tabel -->
        <div class="overflow-y-auto flex-1">
          <table class="w-full text-sm">
            <thead class="sticky top-0 bg-white">
              <tr class="border-b border-gray-100">
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Nomor Dokumen</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Dari Asisten</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">PIC</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Deadline</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Return Actual</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="doc in filteredDetail" :key="doc.id"
                :class="doc.status === 'alert' ? 'bg-red-50/30' : doc.status === 'early_warning' ? 'bg-amber-50/30' : ''"
                class="border-b border-gray-50 hover:bg-gray-50 transition">
                <td class="px-4 py-3 font-medium text-xs">{{ doc.nomor_dokumen }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ doc.asisten?.name || '—' }}</td>
                <td class="px-4 py-3">
                  <div v-if="doc.pic" class="flex items-center gap-1.5">
                    <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-semibold">
                      {{ initials(doc.pic?.name) }}
                    </div>
                    <span class="text-xs">{{ doc.pic?.name }}</span>
                  </div>
                  <span v-else class="text-xs text-gray-300">—</span>
                </td>
                <td class="px-4 py-3 text-xs font-medium" :class="deadlineColor(doc)">
                  {{ doc.review_deadline ? formatDate(doc.review_deadline) : '—' }}
                </td>
                <td class="px-4 py-3 text-xs" :class="doc.return_actual_date ? 'text-green-600 font-medium' : 'text-gray-300'">
                  {{ doc.return_actual_date ? formatDate(doc.return_actual_date) : '—' }}
                </td>
                <td class="px-4 py-3"><StatusBadge :status="doc.status" /></td>
              </tr>
              <tr v-if="filteredDetail.length === 0">
                <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Tidak ada dokumen</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-100 flex justify-end flex-shrink-0">
          <button @click="showDetail = false"
            class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import api from '../../axios'
import StatusBadge from '../../components/StatusBadge.vue'

const docs           = ref([])
const loadingTable   = ref(false)
const filterStatus   = ref('')
const showDetail     = ref(false)
const selectedProject = ref(null)
const detailDocs     = ref([])
const activeDetailTab = ref('semua')
const pieTotal       = ref(null)
const pieRefs        = {}
const projects       = ref([])

const detailTabs = [
  { label: 'Semua',         value: 'semua' },
  { label: 'Selesai',       value: 'selesai' },
  { label: 'Early Warning', value: 'early_warning' },
  { label: 'Alert',         value: 'alert' },
  { label: 'Pending',       value: 'pending' },
]

const summary = computed(() => ({
  total:   docs.value.length,
  waiting: docs.value.filter(d => d.submit_status === 'submitted').length,
  running: docs.value.filter(d => d.submit_status === 'assigned').length,
  done:    docs.value.filter(d => d.submit_status === 'selesai').length,
  alert:   docs.value.filter(d => d.status === 'alert').length,
}))

const filteredDocs = computed(() => {
  if (!filterStatus.value) return docs.value
  return docs.value.filter(d => d.status === filterStatus.value)
})

const filteredDetail = computed(() => {
  if (activeDetailTab.value === 'semua') return detailDocs.value
  return detailDocs.value.filter(d => d.status === activeDetailTab.value)
})

function setPieRef(el, id) {
  if (el) pieRefs[id] = el
}

function drawPie(canvas, selesai, running, waiting, alert, total) {
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  const cx  = canvas.width / 2
  const cy  = canvas.height / 2
  const r   = cx - 6
  ctx.clearRect(0, 0, canvas.width, canvas.height)
  if (total === 0) {
    ctx.fillStyle = '#D1D5DB'
    ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI*2); ctx.fill()
    ctx.fillStyle = '#9CA3AF'; ctx.font = '10px sans-serif'; ctx.textAlign = 'center'
    ctx.fillText('Kosong', cx, cy+4)
    return
  }
  const data = [
    { v: selesai, c: '#22C55E' },
    { v: running, c: '#3B82F6' },
    { v: waiting, c: '#F59E0B' },
    { v: alert,   c: '#EF4444' },
  ]
  let start = -Math.PI / 2
  data.forEach(d => {
    if (!d.v) return
    const angle = (d.v / total) * Math.PI * 2
    ctx.fillStyle = d.c
    ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, r, start, start+angle); ctx.closePath(); ctx.fill()
    const mid = start + angle / 2
    if (d.v/total > 0.1) {
      ctx.fillStyle = '#fff'; ctx.font = 'bold 10px sans-serif'; ctx.textAlign = 'center'
      ctx.fillText(Math.round(d.v/total*100)+'%', cx+28*Math.cos(mid), cy+4+28*Math.sin(mid))
    }
    start += angle
  })
}

function buildProjects() {
  const map = {}
  docs.value.forEach(doc => {
    const pid  = doc.project_id
    const name = doc.project?.name || '—'
    if (!map[pid]) map[pid] = { id: pid, name, total_count:0, selesai_count:0, running_count:0, warning_count:0, alert_count:0 }
    map[pid].total_count++
    if (doc.status === 'selesai')       map[pid].selesai_count++
    if (doc.submit_status === 'assigned' && doc.status !== 'selesai') map[pid].running_count++
    if (doc.status === 'early_warning') map[pid].warning_count++
    if (doc.status === 'alert')         map[pid].alert_count++
  })
  projects.value = Object.values(map)
}

async function drawProjectPies() {
  await nextTick()
  projects.value.forEach(p => {
    setTimeout(() => {
      const canvas = pieRefs[p.id]
      if (!canvas) return
      drawPie(canvas, p.selesai_count, p.running_count, 0, p.alert_count, p.total_count)
    }, 50)
  })
}

async function openDetail(project) {
  selectedProject.value = project
  activeDetailTab.value = 'semua'
  showDetail.value      = true
  const res = await api.get('/documents', { params: { project_id: project.id } })
  detailDocs.value = res.data
}

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

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
  loadingTable.value = true
  try {
    const res  = await api.get('/documents')
    docs.value = res.data
    buildProjects()

    await nextTick()
    const s = summary.value
    drawPie(pieTotal.value, s.done, s.running, s.waiting, s.alert, s.total)
    drawProjectPies()
  } finally {
    loadingTable.value = false
  }
})
</script>