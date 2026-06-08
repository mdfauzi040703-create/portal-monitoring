import { defineStore } from 'pinia'
import { ref } from 'vue'
import { documentApi } from '@/services/api'

export const useDocumentStore = defineStore('document', () => {
  const documents = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function fetchAll(params = {}) {
    loading.value = true
    error.value = null
    try {
      const res = await documentApi.list(params)
      documents.value = res.data.data ?? res.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Gagal memuat dokumen'
    } finally {
      loading.value = false
    }
  }

  async function create(data) {
    const res = await documentApi.create(data)
    documents.value.unshift(res.data.data ?? res.data)
    return res.data
  }

  async function update(id, data) {
    const res = await documentApi.update(id, data)
    const updated = res.data.data ?? res.data
    const idx = documents.value.findIndex(d => d.id === id)
    if (idx !== -1) documents.value[idx] = updated
    return updated
  }

  async function remove(id) {
    await documentApi.delete(id)
    documents.value = documents.value.filter(d => d.id !== id)
  }

  return { documents, loading, error, fetchAll, create, update, remove }
})
