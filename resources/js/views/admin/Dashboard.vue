<template>
  <div>
    <!-- Summary cards -->
    <div class="grid grid-cols-4 gap-3 mb-5">
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Total Dokumen</div>
        <div class="text-3xl font-semibold text-blue-600">{{ summary.total }}</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Early Warning</div>
        <div class="text-3xl font-semibold text-amber-600">{{ summary.earlyWarning }}</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Alert</div>
        <div class="text-3xl font-semibold text-red-600">{{ summary.alert }}</div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Selesai</div>
        <div class="text-3xl font-semibold text-green-600">{{ summary.selesai }}</div>
      </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
      <!-- Pie chart total -->
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="text-sm font-medium mb-4">Total Gambar</div>
        <canvas ref="pieTotal" width="200" height="200" class="mx-auto block"></canvas>
        <div class="mt-4 space-y-2">
          <div class="flex items-center gap-2 text-xs">
            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
            <span class="text-gray-500 flex-1">Selesai</span>
            <span class="font-medium">{{ summary.selesai }}</span>
          </div>
          <div class="flex items-center gap-2 text-xs">
            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
            <span class="text-gray-500 flex-1">Early Warning</span>
            <span class="font-medium">{{ summary.earlyWarning }}</span>
          </div>
          <div class="flex items-center gap-2 text-xs">
            <div class="w-3 h-3 rounded-full bg-red-500"></div>
            <span class="text-gray-500 flex-1">Alert</span>
            <span class="font-medium">{{ summary.alert }}</span>
          </div>
        </div>
      </div>

      <!-- Projects -->
      <div class="col-span-2 bg-white border border-gray-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-4">
          <div class="text-sm font-medium">Per Project</div>
          <button @click="showModal = true"
            class="flex items-center gap-1 text-xs border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">
            + Tambah Project
          </button>
        </div>

        <div v-if="projects.length === 0"
          class="flex flex-col items-center justify-center py-10 border border-dashed border-gray-200 rounded-xl text-gray-400">
          <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7h18M3 12h18M3 17h18"/></svg>
          <p class="text-sm">Tidak ada project</p>
        </div>

        <div v-else class="grid grid-cols-3 gap-3">
          <div v-for="p in projects" :key="p.id"
            class="border border-gray-200 rounded-xl p-3 flex flex-col items-center gap-2 relative group cursor-pointer hover:border-amber-300 hover:shadow-sm transition"
            @click="openDetail(p)">
            <!-- Tombol hapus -->
            <button @click.stop="deleteProject(p.id)"
              class="absolute top-2 right-2 w-6 h-6 rounded-full bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 transition items-center justify-center hidden group-hover:flex">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
            <div class="text-xs font-semibold text-center">{{ p.name }}</div>
            <canvas :ref="el => setPieRef(el, p.id)" width="90" height="90"></canvas>
            <div class="text-xs text-gray-500">{{ p.selesai_count }}/{{ p.documents_count }} selesai</div>
            <div class="flex gap-1 flex-wrap justify-center">
              <span v-if="p.selesai_count" class="text-xs bg-blue-50 text-blue-700 border border-blue-100 px-1.5 py-0.5 rounded-full">{{ p.selesai_count }} selesai</span>
              <span v-if="p.early_warning_count" class="text-xs bg-amber-50 text-amber-700 border border-amber-100 px-1.5 py-0.5 rounded-full">{{ p.early_warning_count }} H-1</span>
              <span v-if="p.alert_count" class="text-xs bg-red-50 text-red-700 border border-red-100 px-1.5 py-0.5 rounded-full">{{ p.alert_count }} alert</span>
            </div>
            <!-- Hint klik -->
            <div class="text-xs text-gray-400 hidden group-hover:block">Klik untuk detail</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal tambah project -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-80 border border-gray-200">
        <div class="text-base font-semibold mb-4">Tambah Project Baru</div>
        <label class="block text-xs text-gray-500 mb-1">Nama project</label>
        <input v-model="newProject.name" type="text" placeholder="cth. Proyek G"
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-amber-400" />
        <label class="block text-xs text-gray-500 mb-1">Deskripsi (opsional)</label>
        <input v-model="newProject.description" type="text" placeholder="Deskripsi project"
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-amber-400" />
        <div class="flex gap-2 justify-end">
          <button @click="showModal = false" class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
          <button @click="addProject" class="text-sm px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-700">Tambah</button>
        </div>
      </div>
    </div>

    <!-- Modal Detail Project -->
    <div v-if="showDetail" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl border border-gray-200 shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col">
        <!-- Header -->
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
        <div class="px-6 py-3 border-b border-gray-100 flex gap-4 flex-shrink-0">
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-blue-500"></div>
            <span class="text-xs text-gray-500">Selesai:</span>
            <span class="text-xs font-semibold text-blue-600">{{ detailDocs.filter(d => d.status === 'selesai').length }}</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
            <span class="text-xs text-gray-500">Early Warning:</span>
            <span class="text-xs font-semibold text-amber-600">{{ detailDocs.filter(d => d.status === 'early_warning').length }}</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
            <span class="text-xs text-gray-500">Alert:</span>
            <span class="text-xs font-semibold text-red-600">{{ detailDocs.filter(d => d.status === 'alert').length }}</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-gray-300"></div>
            <span class="text-xs text-gray-500">Total:</span>
            <span class="text-xs font-semibold text-gray-700">{{ detailDocs.length }}</span>
          </div>
        </div>

        <!-- Filter tab -->
        <div class="px-6 py-2 border-b border-gray-100 flex gap-2 flex-shrink-0">
          <button v-for="tab in tabs" :key="tab.value"
            @click="activeTab = tab.value"
            :class="activeTab === tab.value
              ? 'bg-gray-900 text-white'
              : 'border border-gray-200 text-gray-500 hover:bg-gray-50'"
            class="text-xs px-3 py-1.5 rounded-lg transition">
            {{ tab.label }}
            <span class="ml-1 font-semibold">{{ tabCount(tab.value) }}</span>
          </button>
        </div>

        <!-- Tabel dokumen -->
        <div class="overflow-y-auto flex-1">
          <div v-if="loadingDetail" class="flex items-center justify-center py-10">
            <svg class="animate-spin w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
          </div>
          <table v-else class="w-full text-sm">
            <thead class="sticky top-0 bg-white">
              <tr class="border-b border-gray-100">
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Nomor Dokumen</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">Dari Atasan</th>
                <th class="text-left px-4 py-3 text-xs text-gray-500 font-medium">PIC Dituju</th>
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
                <td class="px-4 py-3">
                  <div class="flex items-center gap-1.5">
                    <div class="w-5 h-5 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-semibold flex-shrink-0">
                      {{ initials(doc.atasan?.name) }}
                    </div>
                    <span class="text-xs">{{ doc.atasan?.name }}</span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-1.5">
                    <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-semibold flex-shrink-0">
                      {{ initials(doc.pic?.name) }}
                    </div>
                    <span class="text-xs">{{ doc.pic?.name }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-xs font-medium" :class="deadlineColor(doc)">
                  {{ formatDate(doc.review_deadline) }}
                </td>
                <td class="px-4 py-3 text-xs" :class="doc.return_actual_date ? 'text-green-600 font-medium' : 'text-gray-300'">
                  {{ doc.return_actual_date ? formatDate(doc.return_actual_date) : '—' }}
                </td>
                <td class="px-4 py-3"><StatusBadge :status="doc.status" /></td>
              </tr>
              <tr v-if="filteredDetail.length === 0">
                <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">
                  Tidak ada dokumen dengan status ini
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Footer -->
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
import { useConfirm } from '../../composables/useConfirm'
import { useToast }   from '../../composables/useToast'

const { openConfirm } = useConfirm()
const { showToast }   = useToast()

const summary        = ref({ total: 0, earlyWarning: 0, alert: 0, selesai: 0 })
const projects       = ref([])
const showModal      = ref(false)
const showDetail     = ref(false)
const loadingDetail  = ref(false)
const selectedProject = ref(null)
const detailDocs     = ref([])
const activeTab      = ref('semua')
const newProject     = ref({ name: '', description: '' })
const pieTotal       = ref(null)
const pieRefs        = {}

const tabs = [
  { label: 'Semua',         value: 'semua' },
  { label: 'Selesai',       value: 'selesai' },
  { label: 'Early Warning', value: 'early_warning' },
  { label: 'Alert',         value: 'alert' },
  { label: 'Pending',       value: 'pending' },
]

const filteredDetail = computed(() => {
  if (activeTab.value === 'semua') return detailDocs.value
  return detailDocs.value.filter(d => d.status === activeTab.value)
})

function tabCount(val) {
  if (val === 'semua') return detailDocs.value.length
  return detailDocs.value.filter(d => d.status === val).length
}

function setPieRef(el, id) {
  if (el) pieRefs[id] = el
}

function drawPie(canvas, done, warn, alert, total) {
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  const cx  = canvas.width / 2
  const cy  = canvas.height / 2
  const r   = cx - 6
  ctx.clearRect(0, 0, canvas.width, canvas.height)
  if (total === 0) {
    ctx.fillStyle = '#D1D5DB'
    ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI * 2); ctx.fill()
    ctx.fillStyle = '#9CA3AF'; ctx.font = '10px sans-serif'; ctx.textAlign = 'center'
    ctx.fillText('Kosong', cx, cy + 4)
    return
  }
  const data = [
    { v: done,  c: '#3B82F6' },
    { v: warn,  c: '#F59E0B' },
    { v: alert, c: '#EF4444' },
    { v: total - done - warn - alert, c: '#E5E7EB' },
  ]
  let start = -Math.PI / 2
  data.forEach(d => {
    if (!d.v) return
    const angle = (d.v / total) * Math.PI * 2
    ctx.fillStyle = d.c
    ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, r, start, start + angle); ctx.closePath(); ctx.fill()
    const mid = start + angle / 2
    if (d.v / total > 0.1) {
      ctx.fillStyle = '#fff'; ctx.font = 'bold 10px sans-serif'; ctx.textAlign = 'center'
      ctx.fillText(Math.round(d.v / total * 100) + '%', cx + 28 * Math.cos(mid), cy + 4 + 28 * Math.sin(mid))
    }
    start += angle
  })
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

async function openDetail(project) {
  selectedProject.value = project
  activeTab.value       = 'semua'
  showDetail.value      = true
  loadingDetail.value   = true
  try {
    const res = await api.get('/documents', { params: { project_id: project.id } })
    detailDocs.value = res.data
  } finally {
    loadingDetail.value = false
  }
}

async function loadDashboard() {
  const res = await api.get('/dashboard')
  summary.value  = res.data.summary
  projects.value = res.data.projects

  await nextTick()

  const s = summary.value
  drawPie(pieTotal.value, s.selesai, s.earlyWarning, s.alert, s.total)

  projects.value.forEach(p => {
    setTimeout(() => {
      drawPie(pieRefs[p.id], p.selesai_count, p.early_warning_count, p.alert_count, p.documents_count)
    }, 50)
  })
}

async function addProject() {
  if (!newProject.value.name) return
  await api.post('/projects', newProject.value)
  newProject.value = { name: '', description: '' }
  showModal.value  = false
  loadDashboard()
}

async function deleteProject(id) {
  const ok = await openConfirm({
    title:       'Hapus Project',
    message:     'Semua dokumen dalam project ini juga akan terhapus.',
    confirmText: 'Ya, Hapus',
    type:        'danger'
  })
  if (!ok) return
  try {
    await api.delete(`/projects/${id}`)
    showToast('Project berhasil dihapus!')
    loadDashboard()
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal menghapus project', 'error')
  }
}

onMounted(loadDashboard)
</script>