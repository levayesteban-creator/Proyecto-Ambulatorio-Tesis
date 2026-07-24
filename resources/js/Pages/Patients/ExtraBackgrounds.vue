<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  category: { type: String, required: true },
  categoryLabel: { type: String, required: true },
  icon: { type: String, default: '📋' },
})

const emit = defineEmits(['update:modelValue'])

const items = ref([...props.modelValue])

watch(items, (val) => {
  emit('update:modelValue', val)
}, { deep: true })

watch(() => props.modelValue, (val) => {
  if (JSON.stringify(val) !== JSON.stringify(items.value)) {
    items.value = [...val]
  }
}, { deep: true })

function addItem() {
  items.value.push({
    category: props.category,
    disease_name: '',
    onset_value: null,
    onset_unit: 'años',
    treatment: '',
    complications: '',
    description: '',
  })
}

function removeItem(index) {
  items.value.splice(index, 1)
}
</script>

<template>
  <div class="extra-backgrounds-section">
    <div class="extra-header">
      <span class="extra-icon">{{ icon }}</span>
      <span class="extra-title">Otros {{ categoryLabel }}</span>
      <button type="button" class="btn-add-extra" @click="addItem">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Agregar otro
      </button>
    </div>

    <TransitionGroup name="list" tag="div" class="extra-items">
      <div v-for="(item, index) in items" :key="index" class="extra-card">
        <div class="extra-card-header">
          <span class="extra-card-number">{{ categoryLabel }} #{{ index + 2 }}</span>
          <button type="button" class="btn-remove-extra" @click="removeItem(index)" title="Eliminar">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2v2"/>
            </svg>
          </button>
        </div>

        <div class="extra-fields">
          <div class="extra-row">
            <div class="extra-field flex-2">
              <label class="field-label">Enfermedad / Intervención</label>
              <input v-model="item.disease_name" class="field-input" type="text"
                     placeholder="Ej: Hipertensión, Appendicectomía…"/>
            </div>
            <div class="extra-field">
              <label class="field-label">Edad de inicio</label>
              <div class="input-group">
                <input v-model="item.onset_value" class="field-input" type="number" min="0" placeholder="Ej: 35"/>
                <select v-model="item.onset_unit" class="field-select field-select-sm">
                  <option value="años">Años</option>
                  <option value="meses">Meses</option>
                  <option value="días">Días</option>
                </select>
              </div>
            </div>
          </div>

          <div class="extra-row">
            <div class="extra-field flex-2">
              <label class="field-label">Tratamiento actual</label>
              <input v-model="item.treatment" class="field-input" type="text"
                     placeholder="Medicamentos, dosis, frecuencia"/>
            </div>
            <div class="extra-field flex-2">
              <label class="field-label">Complicaciones</label>
              <input v-model="item.complications" class="field-input" type="text"
                     placeholder="Complicaciones asociadas"/>
            </div>
          </div>

          <div class="extra-row">
            <div class="extra-field full-width">
              <label class="field-label">Descripción libre</label>
              <textarea v-model="item.description" class="field-input field-textarea" rows="2"
                        placeholder="Información adicional que desee registrar…"></textarea>
            </div>
          </div>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.extra-backgrounds-section {
  margin-top: 0.75rem;
}

.extra-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: #F0F9FF;
  border: 1px solid #BAE6FD;
  border-radius: 8px;
  margin-bottom: 0.5rem;
}

.extra-icon { font-size: 1rem; }
.extra-title { font-size: 0.8rem; font-weight: 600; color: #0369A1; flex: 1; }

.btn-add-extra {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.3rem 0.65rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: #0369A1;
  background: #E0F2FE;
  border: 1px solid #7DD3FC;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-add-extra:hover { background: #BAE6FD; border-color: #38BDF8; }

.extra-items { display: flex; flex-direction: column; gap: 0.5rem; }

.extra-card {
  background: #FAFBFC;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  padding: 0.75rem;
}

.extra-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.extra-card-number {
  font-size: 0.8rem;
  font-weight: 600;
  color: #6B7280;
}

.btn-remove-extra {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  color: #9CA3AF;
  background: transparent;
  border: 1px solid transparent;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-remove-extra:hover { color: #EF4444; background: #FEE2E2; border-color: #FECACA; }

.extra-fields { display: flex; flex-direction: column; gap: 0.5rem; }
.extra-row { display: flex; gap: 0.75rem; }
.extra-field { flex: 1; }
.extra-field.flex-2 { flex: 2; }
.extra-field.full-width { width: 100%; }

.input-group {
  display: flex;
  gap: 0.35rem;
}

.input-group .field-select-sm {
  width: auto;
  min-width: 80px;
}

.field-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  color: #6B7280;
  margin-bottom: 0.25rem;
}

.field-input, .field-select {
  width: 100%;
  padding: 0.45rem 0.65rem;
  font-size: 0.85rem;
  border: 1px solid #D1D5DB;
  border-radius: 6px;
  background: #fff;
  color: #111827;
  transition: border-color 0.2s;
}
.field-input:focus, .field-select:focus {
  outline: none;
  border-color: #93C5FD;
  box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.15);
}

.field-textarea {
  resize: vertical;
  min-height: 60px;
}

/* Animaciones */
.list-enter-active, .list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from, .list-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
