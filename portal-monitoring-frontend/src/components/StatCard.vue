<template>
  <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-2xl">{{ icon }}</span>
      <span :class="badgeClass" class="text-xs font-medium px-2 py-0.5 rounded-full">{{ label }}</span>
    </div>
    <p :class="valueClass" class="text-3xl font-bold">{{ value }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: String,
  value: [Number, String],
  icon: String,
  color: { type: String, default: 'blue' },
})

const colorMap = {
  blue:   { badge: 'bg-blue-100 text-blue-700',   value: 'text-blue-700' },
  yellow: { badge: 'bg-yellow-100 text-yellow-700', value: 'text-yellow-600' },
  red:    { badge: 'bg-red-100 text-red-700',      value: 'text-red-600' },
  green:  { badge: 'bg-green-100 text-green-700',  value: 'text-green-600' },
}

const badgeClass = computed(() => colorMap[props.color]?.badge ?? colorMap.blue.badge)
const valueClass = computed(() => colorMap[props.color]?.value ?? colorMap.blue.value)
</script>
