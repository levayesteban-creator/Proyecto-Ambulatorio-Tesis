<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed, reactive } from 'vue'

const props = defineProps({ currentYear: Number })

const formType = ref('epi12')
const year = ref(props.currentYear)
const week = ref(1)
const month = ref(1)
const loading = ref(false)
const error = ref(null)
const data = ref(null)

const months = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']

const ageGroupLabels = {
  lt1: '< 1 año',
  '1_4': '1-4 años',
  '5_6': '5-6 años',
  '7_9': '7-9 años',
  '10_11': '10-11 años',
  '12_14': '12-14 años',
  '15_19': '15-19 años',
  '20_24': '20-24 años',
  '25_44': '25-44 años',
  '45_59': '45-59 años',
  '60_64': '60-64 años',
  '65plus': '65+ años',
  ignorado: 'Edad ignorada',
}

const epi12Columns = computed(() => {
  if (!data.value) return []
  const cols = []
  for (const gk of Object.keys(data.value.ageGroups)) {
    cols.push({ key: gk, label: ageGroupLabels[gk], sub: 'H', type: 'age-sex' })
    cols.push({ key: gk, label: ageGroupLabels[gk], sub: 'M', type: 'age-sex' })
  }
  cols.push({ key: 'total_h', label: 'Total', sub: 'H', type: 'total' })
  cols.push({ key: 'total_m', label: 'Total', sub: 'M', type: 'total' })
  cols.push({ key: 'total', label: 'Total', sub: null, type: 'grand-total' })
  return cols
})

const epi15Columns = [
  { key: 'p', label: 'P', sub: null, type: 'data' },
  { key: 's', label: 'S', sub: null, type: 'data' },
  { key: 'x', label: 'X', sub: null, type: 'data' },
  { key: 'total', label: 'Total', sub: null, type: 'total' },
  { key: 'acumP', label: 'P Acum.', sub: null, type: 'acum' },
  { key: 'acumS', label: 'S Acum.', sub: null, type: 'acum' },
  { key: 'acumX', label: 'X Acum.', sub: null, type: 'acum' },
  { key: 'acumTotal', label: 'Total Acum.', sub: null, type: 'acum-total' },
]

const columns = computed(() => formType.value === 'epi15' ? epi15Columns : epi12Columns.value)

function getCsrfToken() {
  return document.querySelector('meta[name=csrf-token]')?.content || ''
}
function getCookie(name) {
  const m = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'))
  return m ? decodeURIComponent(m[2]) : ''
}

function procesar() {
  loading.value = true
  error.value = null
  data.value = null
  const routeName = formType.value === 'epi15' ? 'reports.epi.matrix.data15' : 'reports.epi.matrix.data'
  const body = formType.value === 'epi15'
    ? { year: year.value, month: month.value }
    : { year: year.value, week: week.value }
  const url = route(routeName)
  const csrf = getCsrfToken()
  const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
  const xsrfToken = getCookie('XSRF-TOKEN')
  if (xsrfToken) headers['X-XSRF-TOKEN'] = xsrfToken
  fetch(url, {
    method: 'POST',
    headers,
    credentials: 'include',
    body: JSON.stringify({ ...body, _token: csrf }),
  })
    .then(r => {
      if (!r.ok) throw new Error('Error del servidor (' + r.status + ')')
      return r.json()
    })
    .then(d => { data.value = d; loading.value = false })
    .catch(e => { error.value = e.message; loading.value = false })
}

function getEpi12Val(row, col) {
  if (col.key === 'total_h' || col.key === 'total_m') {
    return row[col.key] ?? 0
  }
  if (col.sub === 'H' || col.sub === 'M') {
    return row.grupos?.[col.key]?.[col.sub] ?? 0
  }
  return row[col.key] ?? 0
}

const ACUM_MAP = { acumP: 'p', acumS: 's', acumX: 'x', acumTotal: 'total' }
function totalFor(col) {
  if (!data.value) return 0
  if (formType.value === 'epi15') {
    const acumKey = ACUM_MAP[col.key]
    if (acumKey) return data.value.totalesAcum?.[acumKey] ?? 0
    return data.value.totales?.[col.key] ?? 0
  }
  return data.value.rows.reduce((s, r) => s + getEpi12Val(r, col), 0)
}

function descargar(url) {
  const a = document.createElement('a')
  a.href = url
  a.style.display = 'none'
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
}

async function exportPDF(type) {
  const params = type === 15
    ? { periodo: `${year.value}-${String(month.value).padStart(2, '0')}` }
    : { semana: `${year.value}-${String(week.value).padStart(2, '0')}` }

  try {
    const checkUrl = route('reports.epi.check-data', { tipo: `epi${type}`, ...params })
    const res = await fetch(checkUrl)
    const data = await res.json()
    if (!data.hasData) {
      alert(data.message || 'No hay datos para el período seleccionado')
      return
    }
    const routeName = type === 15 ? 'reports.epi15.export' : `reports.epi${type}.export`
    descargar(route(routeName, params))
  } catch (e) {
    alert('Error al verificar datos: ' + e.message)
  }
}

const expandedAlerts = reactive({})
function toggleAlert(tipo) {
  expandedAlerts[tipo] = !expandedAlerts[tipo]
}

const verifying = ref(false)
function verifyWeek() {
  if (!confirm(`¿Marcar la semana ${week.value} de ${year.value} como verificada?`)) return
  verifying.value = true
  const csrf2 = getCsrfToken()
  const headers2 = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf2, 'X-Requested-With': 'XMLHttpRequest' }
  const xsrfToken2 = getCookie('XSRF-TOKEN')
  if (xsrfToken2) headers2['X-XSRF-TOKEN'] = xsrfToken2
  fetch(route('reports.epi.verify'), {
    method: 'POST',
    headers: headers2,
    body: JSON.stringify({ year: year.value, week: week.value, _token: csrf2 }),
  })
    .then(r => { if (!r.ok) throw new Error('Error del servidor al verificar'); return r.json() })
    .then(d => { alert(d.message); verifying.value = false; procesar() })
    .catch(e => { alert('Error: ' + e.message); verifying.value = false })
}

function rowStyle(row) {
  if (row.tipo === 'cap') return 'chapter-row'
  if (row.tipo === 'sub') return 'sub-row'
  return ''
}
</script>

<template>
  <AppLayout>
    <Head :title="'Consolidados Epidemiológicos'" />

    <div class="page-header">
      <h1>Consolidados Epidemiológicos</h1>
    </div>

    <div class="card">
      <div class="epi-controls">
        <label class="epi-field">
          Formato
          <select v-model="formType" class="input">
            <option value="epi12">EPI-12 (Semanal)</option>
            <option value="epi15">EPI-15 (Mensual)</option>
          </select>
        </label>
        <label class="epi-field">
          Año
          <select v-model.number="year" class="input">
            <option v-for="y in 10" :key="y" :value="props.currentYear - y + 1">{{ props.currentYear - y + 1 }}</option>
          </select>
        </label>
        <label v-if="formType === 'epi12'" class="epi-field">
          Semana
          <select v-model.number="week" class="input">
            <option v-for="w in 53" :key="w" :value="w">{{ w }}</option>
          </select>
        </label>
        <label v-else class="epi-field">
          Mes
          <select v-model.number="month" class="input">
            <option v-for="(m, i) in months" :key="i" :value="i + 1">{{ m }}</option>
          </select>
        </label>
        <button class="btn btn-primary" @click="procesar" :disabled="loading">
          {{ loading ? 'Procesando…' : 'Procesar' }}
        </button>
        <button v-if="data" class="btn btn-secondary" @click="procesar" :disabled="loading">
          Actualizar
        </button>
        <Link :href="route('dashboard')" class="btn btn-ghost">Volver</Link>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <div v-if="data" class="card">
      <div class="epi-summary">
        <span v-if="formType === 'epi12'">Semana: <strong>{{ data.semana }}</strong></span>
        <span v-else>Período: <strong>{{ data.periodo }}</strong></span>
        <span>{{ data.fechaInicio }} – {{ data.fechaFin }}</span>
        <span>Consultas: <strong>{{ data.totalConsultas }}</strong></span>
        <span>Diagnósticos clasificados: <strong>{{ data.totalGeneral }}</strong></span>
      </div>

      <div class="epi-actions">
        <template v-if="formType === 'epi12'">
          <button class="btn btn-secondary" @click="exportPDF(12)">EPI-12 (PDF)</button>
          <button class="btn btn-success" @click="verifyWeek" :disabled="verifying" style="margin-left:auto">
            {{ verifying ? 'Verificando…' : 'Verificar Semana' }}
          </button>
        </template>
        <template v-else>
          <button class="btn btn-secondary" @click="exportPDF(15)">EPI-15 (PDF)</button>
        </template>
      </div>

      <div v-if="data.alertas?.length" class="epi-alerts-stack">
        <div v-for="alerta in data.alertas" :key="alerta.tipo" class="alerta-card" :class="'alerta--' + alerta.tipo">
          <div class="alerta-header">
            <span class="alerta-titulo">{{ alerta.titulo }}</span>
            <span v-if="alerta.items.length > 0" class="alerta-contador">{{ alerta.items.length }}</span>
          </div>
          <div class="alerta-cuerpo">
            <template v-if="alerta.items.length > 0">
              <p class="alerta-ayuda">Estos son los pacientes. Puedes editarlos para completar la información faltante:</p>
              <ul class="alerta-lista">
                <li v-for="item in (expandedAlerts[alerta.tipo] ? alerta.items : alerta.items.slice(0, 5))" :key="item">{{ item }}</li>
              </ul>
              <button v-if="alerta.items.length > 5" @click="toggleAlert(alerta.tipo)" class="btn-alerta-expandir">
                {{ expandedAlerts[alerta.tipo] ? 'Mostrar menos' : 'Ver los ' + alerta.items.length + ' pacientes' }}
              </button>
            </template>
            <p v-else class="alerta-ayuda">Abre la consulta correspondiente para corregirlo.</p>
          </div>
        </div>
      </div>

      <div v-if="data.calidadDatos" class="quality-card">
        <h3>Calidad del Dato</h3>
        <div class="quality-grid">
          <div class="quality-item ok" :class="{ warn: data.calidadDatos.sinFechaNac > 0 }">
            <span class="qlabel">Fecha de nacimiento</span>
            <span class="qval">{{ data.calidadDatos.totalPacientes - data.calidadDatos.sinFechaNac }}/{{ data.calidadDatos.totalPacientes }}</span>
          </div>
          <div class="quality-item ok" :class="{ warn: data.calidadDatos.sinSexo > 0 }">
            <span class="qlabel">Sexo</span>
            <span class="qval">{{ data.calidadDatos.totalPacientes - data.calidadDatos.sinSexo }}/{{ data.calidadDatos.totalPacientes }}</span>
          </div>
          <div class="quality-item ok" :class="{ warn: data.calidadDatos.sinSector > 0 }">
            <span class="qlabel">Sector</span>
            <span class="qval">{{ data.calidadDatos.totalPacientes - data.calidadDatos.sinSector }}/{{ data.calidadDatos.totalPacientes }}</span>
          </div>
        </div>
      </div>

      <!-- EPI-12 Matrix -->
      <div v-if="formType === 'epi12'" class="epi-table-wrapper">
        <table class="epi-table">
          <thead>
            <tr>
              <th rowspan="2" class="sticky-col">Diagnóstico (CIE-10)</th>
              <th v-for="col in columns" :key="col.key + col.sub"
                  :class="{ 'col-total': col.type === 'total', 'col-grand-total': col.type === 'grand-total' }">
                <span v-if="!col.sub">{{ col.label }}</span>
                <template v-else>
                  <span class="age-label">{{ col.label }}</span>
                  <span class="sex-label" :class="{ male: col.sub === 'H', female: col.sub === 'M' }">{{ col.sub }}</span>
                </template>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in data.rows" :key="row.codigo">
              <td class="sticky-col diag-cell">
                <span class="diag-code">{{ row.codigo }}</span>
                <span class="diag-name">{{ row.diagnostico }}</span>
              </td>
              <td v-for="col in columns" :key="col.key + col.sub" class="num-cell">
                {{ getEpi12Val(row, col) || '–' }}
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td class="sticky-col"><strong>Total</strong></td>
              <td v-for="col in columns" :key="'t' + col.key + col.sub" class="num-cell total-row">
                <strong>{{ totalFor(col) || '–' }}</strong>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- EPI-15 Matrix -->
      <div v-else class="epi-table-wrapper">
        <table class="epi-table epi15-table">
          <thead>
            <tr>
              <th class="sticky-col">#</th>
              <th class="sticky-col" style="left:40px">Diagnóstico</th>
              <th v-for="col in columns" :key="col.key"
                  :class="{ 'col-total': col.type === 'total', 'col-acum-total': col.type === 'acum-total' }">
                {{ col.label }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, ri) in data.rows" :key="ri" :class="rowStyle(row)">
              <td class="sticky-col" :class="{ 'num-cell': row.n }" style="left:0">
                <span v-if="row.n">{{ row.n }}</span>
              </td>
              <td class="sticky-col diag-cell" style="left:40px">
                <span class="diag-name" :class="{ 'chapter-name': row.tipo === 'cap', 'sub-name': row.tipo === 'sub' }">{{ row.nombre }}</span>
              </td>
              <template v-if="row.tipo === 'enf' || row.tipo === 'enf-3e'">
                <td v-for="col in columns" :key="col.key" class="num-cell">
                  {{ row[col.key] ?? '–' }}
                </td>
              </template>
              <td v-else v-for="col in columns" :key="'e' + col.key"></td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td class="sticky-col" style="left:0"></td>
              <td class="sticky-col" style="left:40px"><strong>Total del Mes</strong></td>
              <td v-for="col in columns" :key="'tf' + col.key" class="num-cell total-row">
                <strong>{{ totalFor(col) || '–' }}</strong>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.epi-controls {
  display: flex;
  gap: 1rem;
  align-items: flex-end;
  flex-wrap: wrap;
}
.epi-field {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.875rem;
  color: var(--text-muted, #666);
}
.epi-field .input { width: auto; min-width: 100px; }
.epi-summary {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}
.epi-actions {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}
.alerta-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-left: 4px solid #eab308;
  border-radius: 8px;
  padding: 0;
  margin-bottom: 0.75rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  overflow: hidden;
}
.alerta-card.alerta--sin_datos { border-left-color: #6b7280; }
.alerta-card.alerta--sin_diag { border-left-color: #ef4444; }
.alerta-card.alerta--sin_codigo { border-left-color: #f97316; }
.alerta-card.alerta--sin_fecha_nac { border-left-color: #eab308; }
.alerta-card.alerta--sin_sexo { border-left-color: #eab308; }
.alerta-card.alerta--sin_sector { border-left-color: #eab308; }

.alerta-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.6rem 0.9rem;
  background: #f9fafb;
  border-bottom: 1px solid #f3f4f6;
}
.alerta-titulo {
  font-size: 0.88rem;
  font-weight: 600;
  color: #1f2937;
}
.alerta-contador {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 1.5rem;
  height: 1.5rem;
  padding: 0 0.4rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: #fff;
  background: #eab308;
  border-radius: 999px;
}
.alerta--sin_diag .alerta-contador { background: #ef4444; }
.alerta--sin_codigo .alerta-contador { background: #f97316; }
.alerta--sin_datos .alerta-contador { background: #6b7280; }

.alerta-cuerpo { padding: 0.5rem 0.9rem 0.6rem; }
.alerta-ayuda {
  margin: 0 0 0.3rem;
  font-size: 0.8rem;
  color: #6b7280;
  line-height: 1.4;
}
.alerta-lista {
  margin: 0;
  padding: 0;
  list-style: none;
}
.alerta-lista li {
  padding: 0.2rem 0;
  font-size: 0.82rem;
  color: #374151;
  border-bottom: 1px solid #f9fafb;
}
.alerta-lista li:last-child { border-bottom: none; }

.btn-alerta-expandir {
  display: block;
  width: 100%;
  margin-top: 0.4rem;
  padding: 0.35rem;
  font-size: 0.8rem;
  font-weight: 500;
  color: #92400e;
  background: #fffbeb;
  border: 1px dashed #fcd34d;
  border-radius: 6px;
  cursor: pointer;
  text-align: center;
  transition: background 0.15s;
}
.btn-alerta-expandir:hover { background: #fef3c7; }

.epi-table-wrapper {
  overflow: auto;
  max-height: 75vh;
  border: 1px solid var(--border, #ddd);
  border-radius: 6px;
}
.epi-table {
  border-collapse: collapse;
  font-size: 0.78rem;
  width: max-content;
  min-width: 100%;
}
.epi-table th, .epi-table td {
  border: 1px solid var(--border, #ddd);
  padding: 3px 5px;
  white-space: nowrap;
  text-align: center;
}
.epi-table thead th {
  background: var(--bg-card-header, #f5f5f5);
  position: sticky;
  top: 0;
  z-index: 2;
}
.epi-table tfoot td {
  background: var(--bg-card-header, #f5f5f5);
  position: sticky;
  bottom: 0;
}
.sticky-col {
  position: sticky;
  left: 0;
  z-index: 3;
  background: var(--bg-card, #fff);
}
.epi-table thead .sticky-col { z-index: 4; }
.epi-table tfoot .sticky-col { z-index: 4; }

.diag-cell {
  text-align: left;
  min-width: 200px;
  display: flex;
  gap: 0.4rem;
  align-items: baseline;
  border-right: 2px solid var(--border, #ddd);
}
.diag-code {
  font-weight: 600;
  color: var(--primary, #2563eb);
  flex-shrink: 0;
}
.diag-name { overflow: hidden; text-overflow: ellipsis; }
.chapter-name { font-weight: 700; font-size: 0.82rem; color: var(--primary, #2563eb); }
.sub-name { font-weight: 600; font-style: italic; padding-left: 1rem; }

.age-label { display: block; font-size: 0.7rem; }
.sex-label { font-weight: 700; font-size: 0.78rem; }
.sex-label.male { color: #2563eb; }
.sex-label.female { color: #dc2626; }

.col-total { background: #e8f4f8 !important; }
.col-grand-total { background: #d4edda !important; }
.col-acum-total { background: #d4edda !important; }

.num-cell { font-variant-numeric: tabular-nums; }
.num-cell.total-row { background: #f0f0f0; }

.chapter-row td { background: #e8eaf6 !important; font-weight: 700; }
.chapter-row td.sticky-col { background: #e8eaf6 !important; }
.sub-row td { background: #f3e5f5 !important; font-style: italic; }
.sub-row td.sticky-col { background: #f3e5f5 !important; }

.epi-table tbody tr:not(.chapter-row):not(.sub-row):nth-child(even) td:not(.sticky-col) { background: #fafafa; }
.epi-table tbody tr:not(.chapter-row):not(.sub-row):nth-child(even) .sticky-col { background: #fafafa; }
.epi-table tbody tr:not(.chapter-row):not(.sub-row):hover td:not(.sticky-col) { background: #eef2ff; }
.epi-table tbody tr:not(.chapter-row):not(.sub-row):hover .sticky-col { background: #eef2ff; }

.epi15-table th:first-child, .epi15-table td:first-child { min-width: 30px; width: 30px; }
.epi15-table .diag-cell { min-width: 280px; }

.quality-card {
  background: #F0FDF4;
  border: 1px solid #BBF7D0;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
}
.quality-card h3 { margin: 0 0 0.5rem; font-size: 0.9rem; color: #166534; }
.quality-grid { display: flex; gap: 1.5rem; flex-wrap: wrap; }
.quality-item { display: flex; gap: 0.5rem; align-items: center; font-size: 0.85rem; }
.quality-item .qlabel { color: #64748B; }
.quality-item .qval { font-weight: 700; color: #166534; }
.quality-item.warn .qval { color: #92400E; }
.quality-item.warn { color: #92400E; }

/* Ensure select elements show native dropdown arrow */
.epi-field select { appearance: auto; -webkit-appearance: auto; -moz-appearance: auto; min-width: 130px; }
</style>
