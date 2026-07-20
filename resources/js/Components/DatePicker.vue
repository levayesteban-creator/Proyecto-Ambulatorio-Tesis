<script setup>
import { computed } from 'vue'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import { es } from 'date-fns/locale/es'

const props = defineProps({
  modelValue: [String, Number, Date, Array],
  type: { type: String, default: 'date' },
  placeholder: { type: String, default: '' },
  format: { type: String, default: null },
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

const modelValueDate = computed({
  get: () => {
    if (!props.modelValue) return null
    if (props.range && Array.isArray(props.modelValue)) {
      return props.modelValue.map(v => parseDate(v))
    }
    if (props.type === 'week' && typeof props.modelValue === 'string') {
      const match = props.modelValue.match(/^(\d{4})-W?(\d{2})$/)
      if (match) {
        const d = new Date(parseInt(match[1]), 0, 4)
        d.setDate(d.getDate() - ((d.getDay() + 6) % 7) + (parseInt(match[2]) - 1) * 7)
        return d
      }
    }
    return parseDate(props.modelValue)
  },
  set: (val) => {
    if (!val) {
      emit('update:modelValue', null)
      return
    }
    if (props.range && Array.isArray(val)) {
      emit('update:modelValue', val.map(d => d ? formatDateOutput(d) : null))
      return
    }
    emit('update:modelValue', formatDateOutput(val))
  },
})

function formatDateOutput(date) {
  if (props.type === 'week') {
    const year = date.getFullYear()
    const startOfYear = new Date(year, 0, 1)
    const dayOfYear = (date - startOfYear + 86400000) / 86400000
    const week = Math.ceil(dayOfYear / 7)
    return `${year}-W${String(week).padStart(2, '0')}`
  }
  if (props.type === 'month') {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
  }
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

function formatDateDisplay(date) {
  if (!date) return ''
  if (props.type === 'week') {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()))
    const dayNum = d.getUTCDay() || 7
    d.setUTCDate(d.getUTCDate() + 4 - dayNum)
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1))
    const weekNo = Math.ceil((((d - yearStart) / 86400000) + 1) / 7)
    return `Sem ${String(weekNo).padStart(2, '0')} ${d.getUTCFullYear()}`
  }
  if (props.type === 'month') {
    return date.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' })
  }
  return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
</script>

<template>
  <VueDatePicker
    v-model="modelValueDate"
    :locale="es"
    :format="formatDateDisplay"
    :month-picker="type === 'month'"
    :week-picker="type === 'week'"
    :clearable="clearable"
    :range="range"
    :min-date="minDate"
    :max-date="maxDate"
    :required="required"
    :disabled="disabled"
    :placeholder="placeholder"
    auto-apply
    hide-input-icon
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
