<script setup>
import { ref, watch } from 'vue'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import { es } from 'date-fns/locale/es'

const props = defineProps({
  modelValue: [String, Number, Date, Array],
  type: { type: String, default: 'date' },
  placeholder: { type: String, default: '' },
  clearable: { type: Boolean, default: false },
  range: { type: Boolean, default: false },
  minDate: [Date, String],
  maxDate: [Date, String],
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

function parseDate(value) {
  if (!value) return null
  if (value instanceof Date) return value
  if (typeof value === 'number') return new Date(value)
  if (typeof value === 'string') {
    const d = value.match(/^(\d{4})-(\d{2})-(\d{2})$/)
    if (d) return new Date(parseInt(d[1]), parseInt(d[2]) - 1, parseInt(d[3]))
    const m = value.match(/^(\d{4})-(\d{2})$/)
    if (m) return new Date(parseInt(m[1]), parseInt(m[2]) - 1, 1)
  }
  const p = new Date(value)
  return isNaN(p.getTime()) ? null : p
}

function toDate(value) {
  if (value instanceof Date) return value
  if (value && typeof value === 'object' && 'year' in value) {
    return new Date(value.year, value.month, 1)
  }
  return parseDate(value)
}

function formatDateOutput(date) {
  const d = toDate(date)
  if (props.type === 'week') {
    const year = d.getFullYear()
    const startOfYear = new Date(year, 0, 1)
    const dayOfYear = (d - startOfYear + 86400000) / 86400000
    const week = Math.ceil(dayOfYear / 7)
    return `${year}-W${String(week).padStart(2, '0')}`
  }
  if (props.type === 'month') {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
  }
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const d2 = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${d2}`
}

function formatDateDisplay(date) {
  if (!date) return ''
  const d = toDate(date)
  if (props.type === 'week') {
    const utc = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()))
    const dayNum = utc.getUTCDay() || 7
    utc.setUTCDate(utc.getUTCDate() + 4 - dayNum)
    const yearStart = new Date(Date.UTC(utc.getUTCFullYear(), 0, 1))
    const weekNo = Math.ceil((((utc - yearStart) / 86400000) + 1) / 7)
    return `Sem ${String(weekNo).padStart(2, '0')} ${utc.getUTCFullYear()}`
  }
  if (props.type === 'month') {
    return d.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' })
  }
  return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function modelToDate(value) {
  if (!value) return null
  if (props.range && Array.isArray(value)) {
    return value.map(v => parseDate(v))
  }
  if (props.type === 'week' && typeof value === 'string') {
    const match = value.match(/^(\d{4})-W?(\d{2})$/)
    if (match) {
      const d = new Date(parseInt(match[1]), 0, 4)
      d.setDate(d.getDate() - ((d.getDay() + 6) % 7) + (parseInt(match[2]) - 1) * 7)
      return d
    }
  }
  return parseDate(value)
}

const internalValue = ref(modelToDate(props.modelValue))

watch(() => props.modelValue, (val) => {
  internalValue.value = modelToDate(val)
})

function onUpdate(val) {
  if (!val) {
    internalValue.value = null
    emit('update:modelValue', null)
    return
  }
  if (props.range && Array.isArray(val)) {
    internalValue.value = val
    emit('update:modelValue', val.map(d => d ? formatDateOutput(d) : null))
    return
  }
  internalValue.value = val
  emit('update:modelValue', formatDateOutput(val))
}
</script>

<template>
  <VueDatePicker
    :model-value="internalValue"
    :locale="es"
    :format="formatDateDisplay"
    :month-picker="type === 'month'"
    :week-picker="type === 'week'"
    :model-type="type === 'month' ? 'date' : undefined"
    :clearable="clearable"
    :range="range"
    :min-date="minDate ? parseDate(minDate) : null"
    :max-date="maxDate ? parseDate(maxDate) : null"
    :required="required"
    :disabled="disabled"
    :placeholder="placeholder"
    auto-apply
    hide-input-icon
    :year-navigator="true"
    @update:model-value="onUpdate"
    class="dp-custom"
  />
</template>

<style scoped>
.dp-custom {
  --dp-border-color: #d1d5db;
  --dp-border-color-hover: #9ca3af;
  --dp-border-color-focus: #6b7280;
  --dp-primary-color: #374151;
  --dp-primary-text-color: #ffffff;
  --dp-success-color: #059669;
  --dp-danger-color: #dc2626;
}
.dp-custom :deep(.dp__input) {
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  border-radius: 0.375rem;
}
</style>
