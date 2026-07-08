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

    <!-- Warning banner proyek menunggu assign -->
    <div v-if="summary.waiting > 0"
      class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-center gap-3">
      <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
      <div>
        <div class="text-sm font-medium text-amber-700">{{ summary.waiting }} proyek menunggu di-assign ke PIC</div>
        <div class="text-xs text-amber-600 mt-0.5">Segera buka menu Dokumen Masuk untuk assign.</div>
      </div>
      <!-- FIX 1: route yang benar -->
      <router-link to="/dokumen-masuk"
        class="ml-auto text-xs bg-amber-500 text-white px-3 py-1.5 rounded-lg hover:bg-amber-600 transition whitespace-nowrap flex-shrink-0">
        Lihat sekarang →
      </router-link>
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
        <div class="flex items-center justify-between mb-4">
          <div class="text-sm font-medium">Per Project</div>
          <button @click="showAddProject = true"
            class="flex items-center gap-1 text-xs border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">
            + Tambah Project
          </button>
        </div>

        <div v-if="projects.length === 0"
          class="flex flex-col items-center justify-center py-8 border border-dashed border-gray-200 rounded-xl text-gray-400">
          <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7h18M3 12h18M3 17h18"/></svg>
          <p class="text-sm">Belum ada project</p>
        </div>

        <div v-else class="grid grid-cols-3 gap-3">
          <div v-for="p in projects" :key="p.id"
            @click="openDetail(p)"
            class="border border-gray-200 rounded-xl p-3 flex flex-col items-center gap-2 cursor-pointer hover:border-blue-300 hover:shadow-sm transition relative group">
            <button @click.stop="deleteProject(p.id)"
              class="absolute top-2 right-2 w-6 h-6 rounded-full bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 transition items-center justify-center hidden group-hover:flex">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
            <div class="text-xs font-semibold text-center">{{ p.name }}</div>
            <canvas :ref="el => setPieRef(el, p.id)" width="90" height="90"></canvas>
            <div class="text-xs text-gray-500">{{ p.selesai_count }}/{{ p.total_count }} selesai</div>
            <div class="flex gap-1 flex-wrap justify-center">
              <span v-if="p.selesai_count" class="text-xs bg-green-50 text-green-700 border border-green-100 px-1.5 py-0.5 rounded-full">{{ p.selesai_count }} selesai</span>
              <span v-if="p.waiting_count" class="text-xs bg-amber-50 text-amber-700 border border-amber-100 px-1.5 py-0.5 rounded-full">{{ p.waiting_count }} menunggu</span>
              <span v-if="p.running_count" class="text-xs bg-blue-50 text-blue-700 border border-blue-100 px-1.5 py-0.5 rounded-full">{{ p.running_count }} berjalan</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Link ke Semua Dokumen -->
    <div class="bg-white border border-gray-200 rounded-xl p-5 flex items-center justify-between">
      <div>
        <div class="text-sm font-medium">Lihat dan kelola semua dokumen</div>
        <div class="text-xs text-gray-400 mt-0.5">Riwayat lengkap, status pengiriman, dan filter dokumen tersedia di Semua Dokumen</div>
      </div>
      <!-- FIX 2: route yang benar -->
      <router-link to="/dokumen"
        class="text-xs bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition whitespace-nowrap flex-shrink-0">
        Buka Semua Dokumen →
      </router-link>
    </div>

    <!-- Modal tambah project -->
    <div v-if="showAddProject" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[999]">
      <div class="bg-white rounded-2xl p-6 w-80 border border-gray-200 shadow-xl">
        <div class="text-base font-semibold mb-4">Tambah Project Baru</div>
        <input v-model="newProjectName" type="text" placeholder="Nama project"
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"/>
        <div class="flex gap-2 justify-end mt-4">
          <button @click="showAddProject = false" class="text-sm px-4 py-2 border border-gray-200 rounded-lg">Batal</button>
          <button @click="addProject" class="text-sm px-4 py-2 bg-gray-900 text-white rounded-lg">Tambah</button>
        </div>
      </div>
    </div>

    <!-- Modal konfirmasi hapus project -->
    <div v-if="showConfirm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[999]">
      <div class="bg-white rounded-2xl p-6 w-80 border border-gray-200 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </div>
          <div>
            <div class="text-sm font-semibold">Hapus Project</div>
            <div class="text-xs text-gray-500 mt-0.5">Semua dokumen di dalamnya juga akan terhapus.</div>
          </div>
        </div>
        <div class="flex gap-2 justify-end">
          <button @click="showConfirm = false" class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
          <button @click="confirmDeleteProject" class="text-sm px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Ya, Hapus</button>
        </div>
      </div>
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
            <span class="text-xs font-semibold text-green-600">{{ detailDocs.filter(d=>d.submit_status==='selesai').length }}</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
            <span class="text-xs text-gray-500">Menunggu Assign:</span>
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

    <!-- Toast -->
    <div v-if="toastMsg"
      class="fixed bottom-5 right-5 text-white text-sm px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 z-50"
      :class="toastType === 'error' ? 'bg-red-600' : 'bg-green-600'">
      {{ toastMsg }}
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
const showAddProject  = ref(false)
const showConfirm     = ref(false)
const showDetail      = ref(false)
const selectedProject = ref(null)
const detailDocs      = ref([])
const activeDetailTab = ref('semua')
const newProjectName  = ref('')
const deleteProjectId = ref(null)
const toastMsg        = ref('')
const toastType       = ref('success')
const pieTotal        = ref(null)
const pieRefs         = {}

const detailTabs = [
  { label: 'Semua',             value: 'semua' },
  { label: 'Selesai',           value: 'selesai' },
  { label: 'Sedang Dikerjakan', value: 'assigned' },
  { label: 'Menunggu Assign',   value: 'submitted' },
]

const summary = computed(() => ({
  total:   myDocs.value.length,
  waiting: myDocs.value.filter(d => d.submit_status === 'submitted' || d.submit_status === 'draft').length,
  running: myDocs.value.filter(d => d.submit_status === 'assigned').length,
  done:    myDocs.value.filter(d => d.submit_status === 'selesai').length,
  alert:   myDocs.value.filter(d => d.status === 'alert').length,
}))

const filteredDetail = computed(() => {
  if (activeDetailTab.value === 'semua')     return detailDocs.value
  if (activeDetailTab.value === 'assigned')  return detailDocs.value.filter(d => d.submit_status === 'assigned')
  if (activeDetailTab.value === 'submitted') return detailDocs.value.filter(d => d.submit_status === 'submitted')
  return detailDocs.value.filter(d => d.status === activeDetailTab.value)
})

function detailTabCount(val) {
  if (val === 'semua')     return detailDocs.value.length
  if (val === 'assigned')  return detailDocs.value.filter(d => d.submit_status === 'assigned').length
  if (val === 'submitted') return detailDocs.value.filter(d => d.submit_status === 'submitted').length
  return detailDocs.value.filter(d => d.status === val).length
}

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
    { v: Math.max(0, total - selesai - running - waiting - alert), c: '#E5E7EB' },
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
    if (!map[pid]) map[pid] = { id: pid, name, total_count:0, selesai_count:0, running_count:0, waiting_count:0, alert_count:0 }
    map[pid].total_count++
    if (doc.submit_status === 'selesai')                                     map[pid].selesai_count++
    if (doc.submit_status === 'assigned')                                    map[pid].running_count++
    if (doc.submit_status === 'submitted' || doc.submit_status === 'draft')  map[pid].waiting_count++
    if (doc.status === 'alert')                                              map[pid].alert_count++
  })
  projects.value = Object.values(map)
}

async function drawProjectPies() {
  await nextTick()
  projects.value.forEach(p => {
    setTimeout(() => {
      const canvas = pieRefs[p.id]
      if (!canvas) return
      drawPie(canvas, p.selesai_count, p.running_count, p.waiting_count, p.alert_count, p.total_count)
    }, 50)
  })
}

async function openDetail(project) {
  selectedProject.value = project
  activeDetailTab.value = 'semua'
  showDetail.value      = true
  const res = await api.get('/documents')
  // FIX 3: hapus filter asisten_id supaya semua dokumen per project muncul
  detailDocs.value = res.data.filter(d => d.project_id === project.id)
}

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
}

function submitStatusLabel(s) {
  return { draft:'Draft', submitted:'Menunggu Assign', assigned:'Sedang Dikerjakan PIC', selesai:'Selesai' }[s] || s
}

function submitStatusClass(s) {
  return {
    draft:     'bg-gray-50 text-gray-500 border-gray-200',
    submitted: 'bg-amber-50 text-amber-700 border-amber-200',
    assigned:  'bg-blue-50 text-blue-700 border-blue-200',
    selesai:   'bg-green-50 text-green-700 border-green-200',
  }[s] || ''
}

function showToast(msg, type = 'success') {
  toastMsg.value  = msg
  toastType.value = type
  setTimeout(() => toastMsg.value = '', 3000)
}

function deleteProject(id) {
  deleteProjectId.value = id
  showConfirm.value     = true
}

async function confirmDeleteProject() {
  showConfirm.value = false
  try {
    await api.delete(`/projects/${deleteProjectId.value}`)
    showToast('Project berhasil dihapus!')
    loadDashboard()
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal menghapus project', 'error')
  }
}

async function addProject() {
  if (!newProjectName.value) return
  await api.post('/projects', { name: newProjectName.value })
  newProjectName.value = ''
  showAddProject.value = false
  showToast('Project berhasil ditambahkan!')
  loadDashboard()
}

async function loadDashboard() {
  const res    = await api.get('/documents')
  // FIX 3: hapus filter asisten_id supaya semua dokumen tampil di dashboard
  myDocs.value = res.data
  buildProjects()

  await nextTick()
  const s = summary.value
  drawPie(pieTotal.value, s.done, s.running, s.waiting, s.alert, s.total)
  drawProjectPies()
}

onMounted(loadDashboard)
</script>