<template>
  <div>
    <div class="text-base font-semibold mb-4">Daftar PIC</div>
    <div class="grid grid-cols-3 gap-4">
      <div v-for="pic in pics" :key="pic.id"
        class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-semibold">
            {{ initials(pic.name) }}
          </div>
          <div>
            <div class="font-medium text-gray-900">{{ pic.name }}</div>
            <div class="text-xs text-blue-600">PIC</div>
          </div>
        </div>
        <div class="space-y-2 text-xs text-gray-500">
          <div class="flex items-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            {{ pic.email }}
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            {{ pic.whatsapp || '—' }}
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-3 gap-2 text-center">
          <div>
            <div class="text-lg font-semibold text-gray-900">{{ picStats(pic.id).total }}</div>
            <div class="text-xs text-gray-400">Total</div>
          </div>
          <div>
            <div class="text-lg font-semibold text-amber-600">{{ picStats(pic.id).proses }}</div>
            <div class="text-xs text-gray-400">Proses</div>
          </div>
          <div>
            <div class="text-lg font-semibold text-green-600">{{ picStats(pic.id).selesai }}</div>
            <div class="text-xs text-gray-400">Selesai</div>
          </div>
        </div>
      </div>
      <div v-if="pics.length === 0"
        class="col-span-3 text-center py-10 text-gray-400 text-sm border border-dashed border-gray-200 rounded-xl">
        Belum ada PIC terdaftar
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../axios'

const pics = ref([])
const docs = ref([])

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

function picStats(picId) {
  const myDocs = docs.value.filter(d => d.pic_id === picId)
  return {
    total:   myDocs.length,
    proses:  myDocs.filter(d => !d.return_actual_date).length,
    selesai: myDocs.filter(d => d.return_actual_date).length,
  }
}

onMounted(async () => {
  const [uRes, dRes] = await Promise.all([api.get('/users'), api.get('/documents')])
  pics.value = uRes.data.filter(u => u.role === 'pic')
  docs.value = dRes.data
})
</script>