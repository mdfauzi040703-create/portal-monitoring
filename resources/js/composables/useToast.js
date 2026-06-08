import { ref } from 'vue'

const show    = ref(false)
const message = ref('')
const type    = ref('success')
let timer     = null

export function useToast() {
  function showToast(msg, t = 'success') {
    message.value = msg
    type.value    = t
    show.value    = true
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => show.value = false, 3000)
  }

  return { show, message, type, showToast }
}