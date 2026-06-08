import { ref } from 'vue'

const show        = ref(false)
const title       = ref('')
const message     = ref('')
const confirmText = ref('')
const type        = ref('danger')
let   resolveFn   = null

export function useConfirm() {
  function openConfirm(options = {}) {
    title.value       = options.title       || 'Konfirmasi Hapus'
    message.value     = options.message     || 'Data yang dihapus tidak bisa dikembalikan.'
    confirmText.value = options.confirmText || 'Ya, Hapus'
    type.value        = options.type        || 'danger'
    show.value        = true

    return new Promise(resolve => {
      resolveFn = resolve
    })
  }

  function onConfirm() {
    show.value = false
    resolveFn && resolveFn(true)
  }

  function onCancel() {
    show.value = false
    resolveFn && resolveFn(false)
  }

  return { show, title, message, confirmText, type, openConfirm, onConfirm, onCancel }
}