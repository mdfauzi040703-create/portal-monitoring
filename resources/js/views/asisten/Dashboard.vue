<template>
  <div>
    <!-- Summary cards -->
    <div class="grid grid-cols-3 gap-3 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Total Proyek Dikirim</div>
        <div class="text-3xl font-semibold text-blue-600">{{ summary.total }}</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Menunggu Manager</div>
        <div class="text-3xl font-semibold text-amber-600">{{ summary.waiting }}</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Sudah Diassign / Selesai</div>
        <div class="text-3xl font-semibold text-green-600">{{ summary.done }}</div>
      </div>
    </div>

    <!-- Pie charts -->
    <div class="grid grid-cols-3 gap-4 mb-5">
      <!-- Pie total -->
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-sm font-medium mb-4">Total Gambar Saya</div>
        <canvas ref="pieTotal" width="180" height="180" class="mx-auto block"></canvas>
        <div class="mt-4 space-y-2">
          <div class="flex items-center gap-2 text-xs">
            <div class="w-3 h-3 rounded-full bg-green-500"></div>
            <span class="text-gray-500 flex-1">Selesai</span>
            <span class="font-medium">{{ summary.done }}</span>
          </div>
          <div class="flex items-center gap-2 text-xs">
            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
            <span class="text-gray-500 flex-1">Sedang Dikerjakan PIC</span>
            <span class="font-medium">{{ summary.running }}</span>
          </div>
          <div class="flex items-center gap-2 text-xs">
            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
            <span class="text-gray-500 flex-1">Menunggu Manager</span>
            <span class="font-medium">{{ summary.waiting }}</span>
          </div>
        </div>
      </div>

      <!-- Pie per project -->
      <div class="col-span-2 bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-sm font-medium mb-4">Per Project</div>
        <div v-if="projects.length === 0"
          class="flex flex-col items-center justify-center py-8 border border-dashed border-gray-200 rounded-xl text-gray-400">
          <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7h18M3 12h18M3 17h18"/></svg>
          <p class="text-sm">Belum ada proyek</p>
        </div>
        <div v-else class="grid grid-cols-3 gap-3">
          <div v-for="p in projects" :key="p.id"
            @click="openDetail(p)"
            class="border border-gray-200 rounded-xl p-3 flex flex-col items-center gap-2 cursor-pointer hover:border-blue-300 hover:shadow-sm transition">
            <div class="text-xs font-semibold text-center">{{ p.name }}</div>
            <canvas :ref="el => setPieRef(el, p.id)" width="90" height="90"></canvas>
            <div class="text-xs text-gray-500">{{ p.selesai_count }}/{{ p.total_count }} selesai</div>
            <div class="flex gap-1 flex-wrap justify-center">
              <span v-if="p.selesai_count" class="text-xs bg-green-50 text-green-700 border border-green-100 px-1.5 py-0.5 rounded-full">{{ p.selesai_count }} selesai</span>
              <span v-if="p.waiting_count" class="text-xs bg-amber-50 text-amber-700 border border-amber-100 px-1.5 py-0.5 rounded-full">{{ p.waiting_count }} menunggu</span>
              <span v-if="p.running_count" class="text-xs bg-blue-50 text-blue-700 border border-blue-100 px-1.5 py-0.5 rounded-full">{{ p.running_count }} berjalan</span>
            </div>
            <div class="text-xs text-gray-400">Klik untuk detail</div>
          </div>
        </div>
      </div>
    </div>


    <!-- Buka Semua Dokumen -->
    <div class="bg-white border border-gray-200 rounded-xl p-5 flex items-center justify-between mb-5">
      <div>
        <div class="text-sm font-medium">Lihat dan kelola semua dokumen</div>
        <div class="text-xs text-gray-400 mt-0.5">Riwayat lengkap, status pengiriman, dan filter dokumen tersedia di Semua Dokumen</div>
      </div>
      <router-link to="/asisten/dokumen"
        class="text-xs bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition whitespace-nowrap flex-shrink-0">
        Buka Semua Dokumen →
      </router-link>
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
            <span class="text-xs text-gray-500">Menunggu Manager:</span>
            <span class="text-xs font-semibold text-amber-600">{{ detailDocs.filter(d=>d.submit_status==='submitted').length }}</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
            <span class="text-xs text-gray-500">Sedang Dikerjakan:</span>
            <span class="text-xs font-semibold text-blue-600">{{ detailDocs.filter(d=>d.submit_status==='assigned').length }}</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-gray-300"></div>
            <span class="text-xs text-gray-500">Total:</span>
            <span class="text-xs font-semibold text-gray-700">{{ detailDocs.length }}</span>
          </div>
        </div>

        <!-- Tab filter -->
        <div class="px-6 py-2 border-b border-gray-100 flex gap-2 flex-shrink-0 flex-wrap">
          <button v-for="tab in detailTabs" :key="tab.value"
            @click="activeDetailTab = tab.value"
            :class="activeDetailTab === tab.value ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-500 hover:bg-gray-50'"
            class="text-xs px-3 py-1.5 rounded-lg transition">
            {{ tab.label }}
            <span class="ml-1 font-semibold">{{ detailTabCount(tab.value) }}</span>
          </button>
        </div>

        <!-- Tabel detail -->
        <div class="overflow-y-auto flex-1">
          <table class="w-full text-sm">
            <thead class="sticky top-0 bg-white">
              <tr class="border-b border-gray-100">
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Nomor Dokumen</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">PIC</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Deadline</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Return Actual</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Status Kirim</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="doc in filteredDetail" :key="doc.id"
                class="border-b border-gray-50 hover:bg-gray-50 transition">
                <td class="px-4 py-3 font-medium text-xs">{{ doc.nomor_dokumen }}</td>
                <td class="px-4 py-3 text-xs">
                  <span v-if="doc.pic" class="text-blue-600 font-medium">{{ doc.pic?.name }}</span>
                  <span v-else class="text-gray-300">Belum diassign</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                  {{ doc.review_deadline ? formatDate(doc.review_deadline) : '—' }}
                </td>
                <td class="px-4 py-3 text-xs" :class="doc.return_actual_date ? 'text-green-600 font-medium' : 'text-gray-300'">
                  {{ doc.return_actual_date ? formatDate(doc.return_actual_date) : '—' }}
                </td>
                <td class="px-4 py-3">
                  <span :class="submitStatusClass(doc.submit_status)" class="text-xs px-2 py-0.5 rounded-full border font-medium">
                    {{ submitStatusLabel(doc.submit_status) }}
                  </span>
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
import { useAuthStore } from '../../stores/auth'

const auth            = useAuthStore()
const myDocs          = ref([])
const projects        = ref([])
const showDetail      = ref(false)
const selectedProject = ref(null)
const detailDocs      = ref([])
const activeDetailTab = ref('semua')
const pieTotal        = ref(null)
const pieRefs         = {}

const detailTabs = [
  { label: 'Semua',            value: 'semua' },
  { label: 'Selesai',          value: 'selesai' },
  { label: 'Sedang Dikerjakan', value: 'assigned' },
  { label: 'Menunggu Manager', value: 'submitted' },
]

const summary = computed(() => ({
  total:   myDocs.value.length,
  waiting: myDocs.value.filter(d => d.submit_status === 'submitted' || d.submit_status === 'draft').length,
  running: myDocs.value.filter(d => d.submit_status === 'assigned').length,
  done:    myDocs.value.filter(d => d.submit_status === 'selesai').length,
}))

const filteredDetail = computed(() => {
  if (activeDetailTab.value === 'semua') return detailDocs.value
  if (activeDetailTab.value === 'assigned') return detailDocs.value.filter(d => d.submit_status === 'assigned')
  if (activeDetailTab.value === 'submitted') return detailDocs.value.filter(d => d.submit_status === 'submitted')
  return detailDocs.value.filter(d => d.status === activeDetailTab.value)
})

function detailTabCount(val) {
  if (val === 'semua')    return detailDocs.value.length
  if (val === 'assigned') return detailDocs.value.filter(d => d.submit_status === 'assigned').length
  if (val === 'submitted') return detailDocs.value.filter(d => d.submit_status === 'submitted').length
  return detailDocs.value.filter(d => d.status === val).length
}

function setPieRef(el, id) {
  if (el) pieRefs[id] = el
}

function drawPie(canvas, selesai, running, waiting, total) {
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
    { v: Math.max(0, total - selesai - running - waiting), c: '#E5E7EB' },
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
  myDocs.value.forEach(doc => {
    const pid  = doc.project_id
    const name = doc.project?.name || '—'
    if (!map[pid]) map[pid] = { id: pid, name, total_count:0, selesai_count:0, running_count:0, waiting_count:0 }
    map[pid].total_count++
    if (doc.submit_status === 'selesai')   map[pid].selesai_count++
    if (doc.submit_status === 'assigned')  map[pid].running_count++
    if (doc.submit_status === 'submitted' || doc.submit_status === 'draft') map[pid].waiting_count++
  })
  projects.value = Object.values(map)
}

async function drawProjectPies() {
  await nextTick()
  projects.value.forEach(p => {
    setTimeout(() => {
      const canvas = pieRefs[p.id]
      if (!canvas) return
      drawPie(canvas, p.selesai_count, p.running_count, p.waiting_count, p.total_count)
    }, 50)
  })
}

async function openDetail(project) {
  selectedProject.value = project
  activeDetailTab.value = 'semua'
  showDetail.value      = true
  const res = await api.get('/documents')
  detailDocs.value = res.data.filter(d => d.project_id === project.id)
}

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
}

function formatDateTime(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' })
}

function submitStatusLabel(s) {
  return { draft:'Draft', submitted:'Menunggu Manager', assigned:'Sedang Dikerjakan PIC', selesai:'Selesai' }[s] || s
}

function submitStatusClass(s) {
  return {
    draft:    'bg-gray-50 text-gray-500 border-gray-200',
    submitted:'bg-amber-50 text-amber-700 border-amber-200',
    assigned: 'bg-blue-50 text-blue-700 border-blue-200',
    selesai:  'bg-green-50 text-green-700 border-green-200',
  }[s] || ''
}

onMounted(async () => {
  const res    = await api.get('/documents')
  myDocs.value = res.data
  buildProjects()

  await nextTick()
  const s = summary.value
  drawPie(pieTotal.value, s.done, s.running, s.waiting, s.total)
  drawProjectPies()
})
</script>