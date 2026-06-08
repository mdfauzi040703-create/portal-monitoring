<template>
  <div class="min-h-screen bg-gray-50">
    <aside class="fixed top-0 left-0 h-full w-56 bg-white border-r border-gray-200 z-50 flex flex-col">
      <div class="px-5 py-5 border-b border-gray-100">
        <div class="text-lg font-bold text-gray-900">Portal<span class="text-amber-500">Monitor</span></div>
        <div class="text-xs text-gray-400 mt-0.5">Panel Atasan</div>
      </div>
      <nav class="flex-1 px-3 py-4 space-y-1">
        <router-link to="/atasan" exact exact-active-class="menu-active" active-class=""
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition w-full">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
          <span class="flex-1">Home</span>
        </router-link>
        <router-link to="/atasan/input-proyek" active-class="menu-active"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition w-full">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span class="flex-1">Input Proyek ke PIC</span>
        </router-link>
        <router-link to="/atasan/daftar-pic" active-class="menu-active"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition w-full">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span class="flex-1">Daftar PIC</span>
        </router-link>
        <router-link to="/atasan/log" active-class="menu-active"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition w-full">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
          <span class="flex-1">Log Notifikasi</span>
        </router-link>
        <router-link to="/atasan/dokumen" active-class="menu-active"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition w-full">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <span class="flex-1">Dokumen</span>
        </router-link>
      </nav>
      <div class="px-4 py-4 border-t border-gray-100">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-semibold">
            {{ initials(auth.user?.name) }}
          </div>
          <div>
            <div class="text-sm font-medium text-gray-900">{{ auth.user?.name }}</div>
            <div class="text-xs text-amber-600 font-medium">Atasan</div>
          </div>
        </div>
        <button @click="auth.logout()"
          class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-500 hover:bg-red-50 rounded-lg transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          Keluar
        </button>
      </div>
    </aside>
    <div class="ml-56">
      <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between sticky top-0 z-40">
        <div>
          <div class="text-sm font-semibold text-gray-900">{{ pageTitle }}</div>
          <div class="text-xs text-gray-400">{{ currentDate }}</div>
        </div>
        <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1 rounded-full font-medium">Atasan — {{ auth.user?.name }}</span>
      </header>
      <main class="p-6"><router-view /></main>
    </div>
    <ConfirmModal
  :show="confirmState.show.value"
  :title="confirmState.title.value"
  :message="confirmState.message.value"
  :confirm-text="confirmState.confirmText.value"
  :type="confirmState.type.value"
  @confirm="confirmState.onConfirm"
  @cancel="confirmState.onCancel"
/>
<ToastNotif
  :show="toastState.show.value"
  :message="toastState.message.value"
  :type="toastState.type.value"
/>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import ConfirmModal from '../../components/ConfirmModal.vue'
import ToastNotif   from '../../components/ToastNotif.vue'
import { useConfirm } from '../../composables/useConfirm'
import { useToast }   from '../../composables/useToast'
const confirmState = useConfirm()
const toastState   = useToast()
const auth  = useAuthStore()
const route = useRoute()
const titles = {
  '/atasan':               'Dashboard Home',
  '/atasan/input-proyek':  'Input Proyek ke PIC',
  '/atasan/daftar-pic':    'Daftar PIC',
  '/atasan/log':           'Log Notifikasi',
  '/atasan/dokumen':       'Dokumen',
}
const pageTitle   = computed(() => titles[route.path] || 'Portal Monitoring')
const currentDate = computed(() => new Date().toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' }))
function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}
</script>
<style scoped>
.menu-active { background-color:#FEF3DC; color:#C8820A; font-weight:500; }
</style>