<template>
  <span :class="cls" class="text-xs px-2 py-0.5 rounded-full border font-medium">
    {{ label }}
  </span>
</template>

<script setup>
const props = defineProps({ status: String })

const map = {
  alert:         { cls: 'bg-red-50 text-red-700 border-red-200',     label: 'Alert' },
  early_warning: { cls: 'bg-amber-50 text-amber-700 border-amber-200', label: 'H-1' },
  selesai:       { cls: 'bg-green-50 text-green-700 border-green-200', label: 'Selesai' },
  pending:       { cls: 'bg-gray-50 text-gray-600 border-gray-200',   label: 'Pending' },
}

const cls   = map[props.status]?.cls   || map.pending.cls
const label = map[props.status]?.label || 'Pending'
</script>