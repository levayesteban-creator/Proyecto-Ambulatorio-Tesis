<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import debounce from 'lodash/debounce'

const props = defineProps({ consultations: Object, filters: Object, count: Number, sectors: Array })

const code = ref(props.filters.code ?? '')
const sector = ref(props.filters.sector ?? '')
const exportingPdf = ref(false)

const applyFilters = debounce(() => {
  router.get(route('reports.historical'), {
    code: code.value || null,
    sector: sector.value || null,
  }, { preserveState: true, replace: true })
}, 300)

function exportPdf() {
  exportingPdf.value = true
  const params = new URLSearchParams()
  if (code.value) params.append('code', code.value)
  if (sector.value) params.append('sector', sector.value)
  const a = document.createElement('a')
  a.href = route('export.historical.pdf') + '?' + params.toString()
  a.style.display = 'none'
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  setTimeout(() => exportingPdf.value = false, 3000)
}
</script>

<template>
  <AppLayout>
    <Head title="Historial Epidemiológico" />

    <div class="page-header">
      <h1>Historial Epidemiológico</h1>
      <div class="header-actions">
        <div class="counter">Total de casos: <strong>{{ count }}</strong></div>
        <button @click="exportPdf" :disabled="exportingPdf" class="btn-pdf">
          <span class="btn-icon">📄</span>
          {{ exportingPdf ? 'Exportando...' : 'Exportar PDF' }}
        </button>
      </div>
    </div>

    <div class="card">
      <div class="filters">
        <input type="text" v-model="code" @input="applyFilters" placeholder="Buscar por código o nombre CIE-10..." class="input" />
        <select v-model="sector" @change="applyFilters" class="input">
          <option value="">Todos los sectores</option>
          <option v-for="s in sectors" :key="s" :value="s">{{ s }}</option>
        </select>
      </div>

      <table class="table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Paciente</th>
            <th>Cédula</th>
            <th>Edad</th>
            <th>Sexo</th>
            <th>Sector</th>
            <th>Diagnóstico (CIE-10)</th>
            <th>Médico</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in consultations.data" :key="c.id">
            <td>{{ new Date(c.consultation_date).toLocaleDateString() }}</td>
            <td class="font-medium">{{ c.patient?.full_name ?? '—' }}</td>
            <td>{{ c.patient?.id_number ?? '—' }}</td>
            <td>{{ c.patient?.birth_date ? (() => { const t=new Date(), b=new Date(c.patient.birth_date); let a=t.getFullYear()-b.getFullYear(); const m=t.getMonth()-b.getMonth(); if(m<0||(m===0&&t.getDate()<b.getDate()))a--; return a })() : '—' }}</td>
            <td>{{ c.patient?.gender === 'F' ? 'Femenino' : c.patient?.gender === 'M' ? 'Masculino' : '—' }}</td>
            <td>{{ c.patient?.addr_sector ?? '—' }}</td>
            <td>
              <template v-if="c.sis_diagnoses?.length">
                <div v-for="d in c.sis_diagnoses" :key="d.id" class="diag-line">
                  <strong>{{ d.sis_diagnosis?.code }}</strong> {{ d.sis_diagnosis?.name ?? d.unlisted_diagnosis }}
                </div>
              </template>
              <span v-else class="text-muted">—</span>
            </td>
            <td>{{ c.doctor?.name ?? '—' }}</td>
          </tr>
          <tr v-if="!consultations.data?.length">
            <td colspan="8" class="empty">No se encontraron registros con esos filtros</td>
          </tr>
        </tbody>
      </table>

      <div v-if="consultations.links" class="pagination">
        <Link v-for="link in consultations.links" :key="link.label" :href="link.url || '#'" class="page-link" :class="{ active: link.active, disabled: !link.url }" v-html="link.label" />
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem; }
.page-header h1 { font-size: 1.25rem; font-weight: 700; color: #0F172A; margin: 0; }
.header-actions { display: flex; align-items: center; gap: 12px; }
.counter { font-size: 0.875rem; color: #475569; background: #EFF6FF; padding: 0.4rem 1rem; border-radius: 8px; }
.counter strong { color: #2563EB; font-size: 1.1rem; }

.btn-pdf {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #DC2626;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s, transform 0.1s;
}
.btn-pdf:hover:not(:disabled) { background: #B91C1C; transform: translateY(-1px); }
.btn-pdf:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-icon { font-size: 14px; }

.card { background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.08); overflow: hidden; }

.filters { display: flex; gap: 0.75rem; padding: 1rem; border-bottom: 1px solid #E2E8F0; background: #F8FAFC; flex-wrap: wrap; }
.input { padding: 0.45rem 0.7rem; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 0.8125rem; outline: none; flex: 1; min-width: 160px; }
.input:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

.table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.table th { text-align: left; padding: 0.6rem 0.75rem; background: #F8FAFC; color: #64748B; font-weight: 600; border-bottom: 1px solid #E2E8F0; white-space: nowrap; }
.table td { padding: 0.6rem 0.75rem; border-bottom: 1px solid #F1F5F9; vertical-align: top; }
.table tr:last-child td { border-bottom: none; }
.font-medium { font-weight: 600; color: #0F172A; }
.text-muted { color: #94A3B8; }
.empty { text-align: center; padding: 2rem !important; color: #94A3B8; }

.diag-line { margin: 1px 0; font-size: 0.75rem; line-height: 1.4; }

.pagination { display: flex; justify-content: center; gap: 2px; padding: 0.75rem; border-top: 1px solid #E2E8F0; }
.page-link { padding: 0.3rem 0.65rem; border-radius: 4px; font-size: 0.75rem; color: #64748B; text-decoration: none; border: 1px solid #E2E8F0; }
.page-link.active { background: #2563EB; color: #fff; border-color: #2563EB; }
.page-link.disabled { opacity: 0.4; pointer-events: none; }
.page-link:hover:not(.active):not(.disabled) { background: #F1F5F9; }
</style>