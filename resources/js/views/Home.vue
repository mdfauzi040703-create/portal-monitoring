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

        <div v-if="projects.length === 0" class="flex flex-col items-center justify-center py-10 border border-dashed border-gray-200 rounded-xl text-gray-400">
          <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7h18M3 12h18M3 17h18"/></svg>
          <p class="text-sm">Tidak ada project</p>
        </div>

        <div v-else class="grid grid-cols-3 gap-3">
          <div v-for="p in projects" :key="p.id"
            class="border border-gray-200 rounded-xl p-3 flex flex-col items-center gap-2">
            <div class="text-xs font-semibold text-center">{{ p.name }}</div>
            <canvas :ref="el => setPieRef(el, p.id)" width="90" height="90"></canvas>
            <div class="text-xs text-gray-500">{{ p.selesai_count }}/{{ p.documents_count }} selesai</div>
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
          <button @click="showModal = false"
            class="text-sm px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Batal</button>
          <button @click="addProject"
            class="text-sm px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-700">Tambah</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import api from '../axios'

const summary    = ref({ total: 0, earlyWarning: 0, alert: 0, selesai: 0 })
const projects   = ref([])
const showModal  = ref(false)
const newProject = ref({ name: '', description: '' })
const pieTotal   = ref(null)
const pieRefs    = {}

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
    start += angle
  })
}

async function loadDashboard() {
  const res = await api.get('/dashboard')
  summary.value  = res.data.summary
  projects.value = res.data.projects

  await nextTick()

  const s = summary.value
  drawPie(pieTotal.value, s.selesai, s.earlyWarning, s.alert, s.total)

  projects.value.forEach(p => {
    drawPie(pieRefs[p.id], p.selesai_count, p.early_warning_count, p.alert_count, p.documents_count)
  })
}

async function addProject() {
  if (!newProject.value.name) return
  await api.post('/projects', newProject.value)
  newProject.value = { name: '', description: '' }
  showModal.value  = false
  loadDashboard()
}

onMounted(loadDashboard)
</script>