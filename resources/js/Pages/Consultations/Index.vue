<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import debounce from 'lodash/debounce'
import DatePicker from '@/Components/DatePicker.vue'

const props = defineProps({ consultations: Object, filters: Object })

const search = ref(props.filters.search ?? '')
const consultationType = ref(props.filters.consultation_type ?? '')
const isHealthy = ref(props.filters.is_healthy ?? '')
const dateFrom = ref(props.filters.date_from ?? '')
const dateTo = ref(props.filters.date_to ?? '')
const showFilters = ref(false)

const applyFilters = debounce(() => {
    router.get(route('consultations.index'), {
        search: search.value || null,
        consultation_type: consultationType.value || null,
        is_healthy: isHealthy.value !== '' ? isHealthy.value : null,
        date_from: dateFrom.value || null,
        date_to: dateTo.value || null,
    }, { preserveState: true, replace: true })
}, 300)

const clearFilters = () => {
    search.value = ''; consultationType.value = ''; isHealthy.value = ''
    dateFrom.value = ''; dateTo.value = ''
    applyFilters()
}

const exportUrl = (format) => {
    const params = new URLSearchParams()
    if (consultationType.value) params.set('consultation_type', consultationType.value)
    if (isHealthy.value !== '') params.set('is_healthy', isHealthy.value)
    if (dateFrom.value) params.set('date_from', dateFrom.value)
    if (dateTo.value) params.set('date_to', dateTo.value)
    if (search.value) params.set('search', search.value)
    const qs = params.toString()
    return route(`export.consultations.${format}`) + (qs ? '?' + qs : '')
}

const typeLabel = (t) => ({ P: 'Primera Vez', S: 'Sucesiva', X: 'Asociada' }[t] ?? t)
</script>

<template>
  <AppLayout>
    <Head title="Consultas Realizadas" />

    <div class="page-header">
      <h1>Consultas Realizadas</h1>
      <div class="flex gap-2">
        <a :href="exportUrl('pdf')" target="_blank" class="btn btn-export-pdf">📄 PDF</a>
        <a :href="exportUrl('csv')" class="btn btn-export-csv">📊 Excel</a>
      </div>
    </div>

    <div class="card">
      <div class="filters">
        <input type="text" v-model="search" @input="applyFilters" placeholder="Buscar por cédula o nombre..." class="input" />
        <DatePicker v-model="dateFrom" placeholder="dd/mm/aaaa" class="input" @update:model-value="applyFilters" />
        <DatePicker v-model="dateTo" placeholder="dd/mm/aaaa" class="input" @update:model-value="applyFilters" />
        <button @click="showFilters = !showFilters" class="btn btn-outline">⚙️ Más filtros</button>
      </div>

      <div v-if="showFilters" class="filters" style="background:#EFF6FF">
        <select v-model="consultationType" @change="applyFilters" class="input" style="max-width:180px">
          <option value="">Todos los tipos</option>
          <option value="P">Primera Vez</option>
          <option value="S">Sucesiva</option>
          <option value="X">Asociada</option>
        </select>
        <select v-model="isHealthy" @change="applyFilters" class="input" style="max-width:160px">
          <option value="">Todos</option>
          <option value="1">Sanos</option>
          <option value="0">Enfermos</option>
        </select>
        <button @click="clearFilters" class="btn btn-outline" style="font-size:0.7rem">Limpiar filtros</button>
      </div>

      <table class="table">
        <thead>
          <tr>
            <th>Paciente</th>
            <th>Cédula</th>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Médico</th>
            <th>Diagnóstico</th>
            <th>Estado</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in consultations.data" :key="c.id">
            <td class="font-medium">{{ c.patient?.full_name ?? '—' }}</td>
            <td>{{ c.patient?.id_number ?? '—' }}</td>
            <td>{{ new Date(c.consultation_date).toLocaleDateString() }}</td>
            <td>
              <span class="badge" :class="'badge-type-' + (c.consultation_type ?? '').toLowerCase()">
                {{ typeLabel(c.consultation_type) }}
              </span>
            </td>
            <td>{{ c.doctor?.name ?? '—' }}</td>
            <td>
              <template v-if="c.sisDiagnoses?.length">
                <span v-for="d in c.sisDiagnoses" :key="d.id" class="diag-chip">
                  {{ d.sis_diagnosis?.code ?? '' }} {{ d.sis_diagnosis?.name ?? d.unlisted_diagnosis }}
                </span>
              </template>
              <span v-else class="text-muted">Sin diagnóstico</span>
            </td>
            <td>
              <span class="badge" :class="c.is_verified ? 'badge-ok' : 'badge-pending'">
                {{ c.is_verified ? 'Procesado' : 'Pendiente' }}
              </span>
            </td>
            <td>
              <Link :href="route('consultations.show', c.id)" class="btn btn-sm btn-outline">Ver detalle</Link>
            </td>
          </tr>
          <tr v-if="!consultations.data?.length">
            <td colspan="8" class="empty">No se encontraron consultas</td>
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
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.25rem; font-weight: 700; color: #0F172A; margin: 0; }
.card { background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.08); overflow: hidden; }
.filters { display: flex; gap: 0.75rem; padding: 1rem; border-bottom: 1px solid #E2E8F0; background: #F8FAFC; flex-wrap: wrap; }
.input { padding: 0.45rem 0.7rem; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 0.8125rem; outline: none; flex: 1; min-width: 140px; }
.input:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
.table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.table th { text-align: left; padding: 0.6rem 0.75rem; background: #F8FAFC; color: #64748B; font-weight: 600; border-bottom: 1px solid #E2E8F0; white-space: nowrap; }
.table td { padding: 0.6rem 0.75rem; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
.table tr:last-child td { border-bottom: none; }
.font-medium { font-weight: 600; color: #0F172A; }
.text-muted { color: #94A3B8; }
.empty { text-align: center; padding: 2rem !important; color: #94A3B8; }
.diag-chip { display: inline-block; background: #F1F5F9; padding: 1px 6px; border-radius: 4px; margin: 1px 2px; font-size: 0.7rem; white-space: nowrap; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 0.7rem; font-weight: 600; }
.badge-ok { background: #D1FAE5; color: #059669; }
.badge-pending { background: #FEF3C7; color: #D97706; }
.badge-type-p { background: #D1FAE5; color: #065F46; }
.badge-type-s { background: #DBEAFE; color: #1E40AF; }
.badge-type-x { background: #EDE9FE; color: #5B21B6; }
.btn { display: inline-flex; align-items: center; padding: 0.3rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer; text-decoration: none; transition: .12s; }
.btn-outline { background: transparent; color: #64748B; border: 1px solid #E2E8F0; }
.btn-outline:hover { background: #F8FAFC; color: #0F172A; }
.btn-sm { padding: 0.3rem 0.6rem; font-size: 0.7rem; }
.btn-export-pdf { background: #DC2626; color: white; padding: 0.35rem 0.75rem; font-size: 0.75rem; }
.btn-export-pdf:hover { background: #B91C1C; }
.btn-export-csv { background: #059669; color: white; padding: 0.35rem 0.75rem; font-size: 0.75rem; }
.btn-export-csv:hover { background: #047857; }
.pagination { display: flex; justify-content: center; gap: 2px; padding: 0.75rem; border-top: 1px solid #E2E8F0; }
.page-link { padding: 0.3rem 0.65rem; border-radius: 4px; font-size: 0.75rem; color: #64748B; text-decoration: none; border: 1px solid #E2E8F0; }
.page-link.active { background: #2563EB; color: #fff; border-color: #2563EB; }
.page-link.disabled { opacity: 0.4; pointer-events: none; }
.page-link:hover:not(.active):not(.disabled) { background: #F1F5F9; }
</style>
