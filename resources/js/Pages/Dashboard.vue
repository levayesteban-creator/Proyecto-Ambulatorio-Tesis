<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import DatePicker from '@/Components/DatePicker.vue'

const props = defineProps({
  user: Object,
})

const exportando = ref(false)
const fechaSeleccionada = ref('')
const semanaSeleccionada = ref('')
const periodoSeleccionado = ref('')
const exportandoEpi10 = ref(false)
const exportandoEpi12 = ref(false)
const exportandoEpi13 = ref(false)
const exportandoEpi15 = ref(false)
const semanaEpi13 = ref('')
const helpModalOpen = ref(false)
const helpSection = ref('')

const canManageUsers = computed(() => props.user?.role_id <= 2)

const abrirAyuda = (seccion) => {
  helpSection.value = seccion
  helpModalOpen.value = true
}
const cerrarAyuda = () => {
  helpModalOpen.value = false
  helpSection.value = ''
}

const hoy = computed(() => {
  const date = new Date()
  return date.toISOString().split('T')[0]
})

const semanaActual = computed(() => {
  const date = new Date()
  const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()))
  const dayNum = d.getUTCDay() || 7
  d.setUTCDate(d.getUTCDate() + 4 - dayNum)
  const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1))
  const weekNo = Math.ceil((((d - yearStart) / 86400000) + 1) / 7)
  const yyyy = d.getUTCFullYear()
  const ww = String(weekNo).padStart(2, '0')
  return `${yyyy}-W${ww}`
})

const mesActual = computed(() => {
  const date = new Date()
  return date.toISOString().slice(0, 7)
})

onMounted(() => {
  fechaSeleccionada.value = hoy.value
  semanaSeleccionada.value = semanaActual.value
  semanaEpi13.value = semanaActual.value
  periodoSeleccionado.value = mesActual.value
})

function descargar(url) {
  const a = document.createElement('a')
  a.href = url
  a.style.display = 'none'
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
}

async function exportarConValidacion(tipo, params, flagRef) {
  if (flagRef.value) return
  flagRef.value = true
  try {
    const checkUrl = route('reports.epi.check-data', { tipo, ...params })
    const res = await fetch(checkUrl)
    const data = await res.json()
    if (!data.hasData) {
      alert(data.message || 'No hay datos para el período seleccionado')
      flagRef.value = false
      return
    }
    const exportRoutes = {
      epi10: 'reports.epi10.export',
      epi12: 'reports.epi12.export',
      epi13: 'reports.epi13.export',
      epi15: 'reports.epi15.export',
    }
    const exportUrl = route(exportRoutes[tipo], params)
    descargar(exportUrl)
  } catch (e) {
    alert('Error al verificar datos: ' + e.message)
  }
  setTimeout(() => { flagRef.value = false }, 3000)
}

const exportarEpi10 = () => exportarConValidacion('epi10', { fecha: fechaSeleccionada.value }, exportandoEpi10)

const exportarEpi12 = () => {
  const semana = semanaSeleccionada.value.replace('-W', '-')
  exportarConValidacion('epi12', { semana }, exportandoEpi12)
}

const exportarEpi13 = () => {
  const semana = semanaEpi13.value.replace('-W', '-')
  exportarConValidacion('epi13', { semana }, exportandoEpi13)
}

const exportarEpi15 = () => exportarConValidacion('epi15', { periodo: periodoSeleccionado.value }, exportandoEpi15)

const mainModules = [
  {
    title: 'Registrar Historia Clínica',
    desc: 'Ficha patronímica completa (Parte 1)',
    href: 'patients.create',
    icon: '📋',
    color: '#2563EB',
  },
  {
    title: 'Pacientes',
    desc: 'Listado, ver ficha, editar e iniciar consulta',
    href: 'patients.index',
    icon: '👥',
    color: '#059669',
  },
  {
    title: 'Consultas',
    desc: 'Historial de consultas médicas',
    href: 'consultations.index',
    icon: '🩺',
    color: '#7C3AED',
  },
]

const reportModules = [
  {
    title: 'Historial EPI',
    desc: 'Consulta histórica por año y mes',
    href: 'reports.historical',
    icon: '📅',
    color: '#0891B2',
  },
  {
    title: 'Consolidados EPI',
    desc: 'Matriz 52 enfermedades × 13 grupos de edad',
    href: 'reports.epi.matrix',
    icon: '📊',
    color: '#DC2626',
  },
]

const toolModules = computed(() => {
  const tools = [
    {
      title: 'Mi Perfil',
      desc: 'Actualizar contraseña y datos personales',
      href: 'profile.edit',
      icon: '👤',
      color: '#64748B',
    },
  ]
  if (canManageUsers.value) {
    tools.push({
      title: 'Usuarios',
      desc: 'Gestionar cuentas y roles del sistema',
      href: 'admin.users.index',
      icon: '⚙️',
      color: '#F59E0B',
    })
    tools.push({
      title: 'Bitácora',
      desc: 'Registro de auditoría del sistema',
      href: 'audit-logs.index',
      icon: '📜',
      color: '#1E40AF',
    })
  }
  return tools
})
</script>

<template>
  <Head title="Inicio" />

  <AppLayout title="Inicio">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Consultorio — El Chaparro</h2>
    </template>

    <div class="dash-wrap">
      <!-- ══ HERO ════════════════════════════════════════════════════════════════════════════════════════════ -->
      <div class="dash-hero">
        <div class="dash-hero-content">
          <h1 class="dash-title">Sistema de Historias Clínicas</h1>
          <p class="dash-sub">
            Bienvenido, <strong>{{ user?.name }}</strong>. Seleccione un módulo para comenzar.
          </p>
        </div>
        <div class="dash-hero-badge">
          <span class="dash-hero-icon">🏥</span>
          <span class="dash-hero-label">El Chaparro</span>
        </div>
      </div>

      <!-- ══ MÓDULOS PRINCIPALES ═══════════════════════════════════════════════════════════════════════════ -->
      <div class="dash-section">
        <h3 class="dash-section-title">Módulos Principales</h3>
        <div class="dash-grid">
          <Link v-for="m in mainModules" :key="m.href" :href="route(m.href)" class="dash-card">
            <div class="dash-card-icon" :style="{ background: m.color + '15', color: m.color }">{{ m.icon }}</div>
            <div class="dash-card-body">
              <div class="dash-card-title">{{ m.title }}</div>
              <div class="dash-card-desc">{{ m.desc }}</div>
            </div>
            <div class="dash-card-arrow">→</div>
          </Link>
        </div>
      </div>

      <!-- ══ REPORTES EPI ═════════════════════════════════════════════════════════════════════════════════ -->
      <div class="dash-section">
        <h3 class="dash-section-title">Reportes Epidemiológicos</h3>
        <div class="dash-grid">
          <!-- Historial y Consolidados -->
          <Link v-for="r in reportModules" :key="r.href" :href="route(r.href)" class="dash-card">
            <div class="dash-card-icon" :style="{ background: r.color + '15', color: r.color }">{{ r.icon }}</div>
            <div class="dash-card-body">
              <div class="dash-card-title">{{ r.title }}</div>
              <div class="dash-card-desc">{{ r.desc }}</div>
            </div>
            <div class="dash-card-arrow">→</div>
          </Link>

          <!-- EPI-10 -->
          <div class="dash-card dash-card-epi">
            <div class="dash-card-icon" style="background: #05966915; color: #059669;">📄</div>
            <div class="dash-card-body">
              <div class="dash-card-title">EPI-10 — Registro Diario</div>
              <div class="dash-card-desc">Atención integral del establecimiento</div>
              <div class="dash-epi-controls">
                <DatePicker v-model="fechaSeleccionada" :max-date="hoy" class="dash-epi-input" />
                <button @click="exportarEpi10" :disabled="exportandoEpi10 || !fechaSeleccionada" class="dash-epi-btn">
                  {{ exportandoEpi10 ? 'Exportando...' : 'Exportar PDF' }}
                </button>
              </div>
            </div>
          </div>

          <!-- EPI-12 -->
          <div class="dash-card dash-card-epi">
            <div class="dash-card-icon" style="background: #7C3AED15; color: #7C3AED;">📊</div>
            <div class="dash-card-body">
              <div class="dash-card-title">EPI-12 — Consolidado Semanal</div>
              <div class="dash-card-desc">Enfermedades de notificación obligatoria</div>
              <div class="dash-epi-controls">
                <DatePicker v-model="semanaSeleccionada" type="week" class="dash-epi-input" />
                <button @click="exportarEpi12" :disabled="exportandoEpi12 || !semanaSeleccionada" class="dash-epi-btn">
                  {{ exportandoEpi12 ? 'Exportando...' : 'Exportar PDF' }}
                </button>
              </div>
            </div>
          </div>

          <!-- EPI-13 -->
          <div class="dash-card dash-card-epi">
            <div class="dash-card-icon" style="background: #DC262615; color: #DC2626;">📋</div>
            <div class="dash-card-body">
              <div class="dash-card-title">EPI-13 — Notificación Obligatoria</div>
              <div class="dash-card-desc">Registro semanal de casos</div>
              <div class="dash-epi-controls">
                <DatePicker v-model="semanaEpi13" type="week" class="dash-epi-input" />
                <button @click="exportarEpi13" :disabled="exportandoEpi13 || !semanaEpi13" class="dash-epi-btn">
                  {{ exportandoEpi13 ? 'Exportando...' : 'Exportar PDF' }}
                </button>
              </div>
            </div>
          </div>

          <!-- EPI-15 -->
          <div class="dash-card dash-card-epi">
            <div class="dash-card-icon" style="background: #F59E0B15; color: #F59E0B;">👶</div>
            <div class="dash-card-body">
              <div class="dash-card-title">EPI-15 — Consolidado Mensual</div>
              <div class="dash-card-desc">Morbilidad por aparatos y sistemas</div>
              <div class="dash-epi-controls">
                <input type="month" v-model="periodoSeleccionado" class="dash-epi-input" style="padding:0.5rem 0.75rem;font-size:0.875rem;border:1px solid #d1d5db;border-radius:0.375rem;width:100%;" />
                <button @click="exportarEpi15" :disabled="exportandoEpi15 || !periodoSeleccionado" class="dash-epi-btn">
                  {{ exportandoEpi15 ? 'Exportando...' : 'Exportar PDF' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ HERRAMIENTAS ════════════════════════════════════════════════════════════════════════════════ -->
      <div class="dash-section">
        <h3 class="dash-section-title">Herramientas</h3>
        <div class="dash-grid dash-grid-tools">
          <Link v-for="t in toolModules" :key="t.href" :href="route(t.href)" class="dash-card dash-card-tool">
            <div class="dash-card-icon" :style="{ background: t.color + '15', color: t.color }">{{ t.icon }}</div>
            <div class="dash-card-body">
              <div class="dash-card-title">{{ t.title }}</div>
              <div class="dash-card-desc">{{ t.desc }}</div>
            </div>
            <div class="dash-card-arrow">→</div>
          </Link>
        </div>
      </div>

      <!-- ══ CENTRO DE AYUDA ═════════════════════════════════════════════════════════════════════════════ -->
      <div class="dash-section dash-help">
        <h3 class="dash-section-title">📖 Centro de Ayuda</h3>
        <div class="dash-help-grid">
          <div class="dash-help-card" @click="abrirAyuda('guia')">
            <div class="dash-help-icon">📋</div>
            <div class="dash-help-card-title">Guía Rápida</div>
            <div class="dash-help-card-desc">Aprende a registrar pacientes y crear consultas en 5 pasos</div>
          </div>
          <div class="dash-help-card" @click="abrirAyuda('historia')">
            <div class="dash-help-icon">🏥</div>
            <div class="dash-help-card-title">Historia Clínica</div>
            <div class="dash-help-card-desc">Apertura, edición y control de expedientes digitales</div>
          </div>
          <div class="dash-help-card" @click="abrirAyuda('reportes')">
            <div class="dash-help-icon">📊</div>
            <div class="dash-help-card-title">Reportes EPI</div>
            <div class="dash-help-card-desc">Generación de formularios EPI-10, EPI-12, EPI-13 y EPI-15</div>
          </div>
          <div class="dash-help-card" @click="abrirAyuda('roles')">
            <div class="dash-help-icon">🔐</div>
            <div class="dash-help-card-title">Roles y Seguridad</div>
            <div class="dash-help-card-desc">Permisos de médicos, enfermeros y administradores</div>
          </div>
        </div>
      </div>

      <!-- ══ MODAL DE AYUDA ══════════════════════════════════════════════════════════════════════════════ -->
      <div v-if="helpModalOpen" class="help-overlay" @click.self="cerrarAyuda">
        <div class="help-modal">
          <button class="help-close" @click="cerrarAyuda">✕</button>
          <div class="help-modal-content">
            <!-- GUÍA RÁPIDA -->
            <div v-if="helpSection === 'guia'">
              <h2 class="help-modal-title">📋 Guía Rápida</h2>
              <ol class="help-list">
                <li><strong>Registrar paciente:</strong> Menú lateral → "Registrar Historia Clínica". Complete la ficha patronímica (nombre, cédula, fecha de nacimiento, datos de vivienda). Guarde.</li>
                <li><strong>Iniciar consulta:</strong> Menú lateral → "Pacientes". Busque el paciente, pulse "Iniciar Consulta" en su ficha.</li>
                <li><strong>Llenar consulta:</strong> Complete anamnesis, examen funcional, examen físico (17 secciones), signos vitales. Agregue diagnósticos del catálogo SIS/CIE-10.</li>
                <li><strong>Referir paciente:</strong> Marque la conducta "Referir" en el diagnóstico y complete el formulario de referencia.</li>
                <li><strong>Exportar reportes:</strong> Pulse sobre cualquier módulo de reportes EPI y seleccione el período.</li>
              </ol>
              <div class="help-tip">💡 <strong>Consejo:</strong> Use el botón ☰ para expandir/contraer el menú lateral.</div>
            </div>

            <!-- HISTORIA CLÍNICA -->
            <div v-if="helpSection === 'historia'">
              <h2 class="help-modal-title">🏥 Historia Clínica Digital</h2>
              <h3 class="help-subtitle">Apertura de expediente</h3>
              <p>Desde "Registrar Historia Clínica" se registran: datos personales, lugar de nacimiento, dirección estructurada (estado, municipio, parroquia, sector, calle, casa), datos socio-demográficos (escolaridad, etnia, ocupación, religión), y grupo sanguíneo.</p>
              <h3 class="help-subtitle">Antecedentes (Parte 1)</h3>
              <p>Después de crear el paciente, pulse "Editar" para agregar: antecedentes personales, familiares, hábitos psicobiológicos y gineco-obstétricos.</p>
              <h3 class="help-subtitle">Consulta médica (Parte 2)</h3>
              <p>Desde la ficha del paciente, pulse "Iniciar Consulta". Complete:</p>
              <ul class="help-list">
                <li><strong>Anamnesis:</strong> motivo de consulta, enfermedad actual</li>
                <li><strong>Examen Funcional:</strong> 14 sistemas (cardiovascular, respiratorio, digestivo, etc.)</li>
                <li><strong>Examen Físico:</strong> 17 regiones anatómicas (cabeza, cuello, tórax, abdomen, etc.)</li>
                <li><strong>Signos Vitales:</strong> PA, temperatura, FC, FR, SpO₂, peso, talla</li>
                <li><strong>Diagnóstico SIS:</strong> seleccione del catálogo CIE-10, tipo (sospechoso/probable/confirmado) y conducta médica</li>
                <li><strong>Referencia:</strong> si aplica, complete datos del centro receptor y motivo de referencia</li>
              </ul>
              <h3 class="help-subtitle">Consultas previas</h3>
              <p>En la ficha del paciente, pulse "Historial de Consultas" para ver todas las consultas anteriores ordenadas por fecha.</p>
            </div>

            <!-- REPORTES EPI -->
            <div v-if="helpSection === 'reportes'">
              <h2 class="help-modal-title">📊 Reportes Epidemiológicos (EPI)</h2>
              <p>El sistema genera automáticamente los formularios oficiales del MPPS en formato PDF, listos para imprimir y entregar al distrito sanitario.</p>
              
              <div class="help-epi-card">
                <strong>📄 EPI-10 — Registro Diario de Consulta</strong>
                <p>Consolida todas las consultas de un día específico. Incluye: datos del paciente, peso/talla, diagnóstico, conducta y referencias.</p>
              </div>
              
              <div class="help-epi-card">
                <strong>📊 EPI-12 — Consolidado Semanal de Enfermedades Notificables</strong>
                <p>Tabla de 52 enfermedades × 13 grupos de edad (H/M). Clasifica automáticamente cada diagnóstico CIE-10 en la categoría correcta.</p>
              </div>
              
              <div class="help-epi-card">
                <strong>📋 EPI-13 — Registro de Enfermedades de Notificación Obligatoria</strong>
                <p>Listado individual de casos con datos del paciente y enfermedad notificable. Hasta 16 filas por semana.</p>
              </div>
              
              <div class="help-epi-card">
                <strong>📉 EPI-15 — Consolidado Mensual de Morbilidad</strong>
                <p>Los 271 diagnósticos del MPPS organizados en 20 secciones (infecciosas, neoplasias, endocrinas, respiratorias, etc.) con desglose por sexo (P/S/X) y acumulado anual.</p>
              </div>

              <div class="help-tip">⚠️ <strong>Importante:</strong> Los reportes solo incluyen consultas registradas en el sistema. Si no hay datos para el período seleccionado, el sistema mostrará un aviso.</div>
            </div>

            <!-- ROLES Y SEGURIDAD -->
            <div v-if="helpSection === 'roles'">
              <h2 class="help-modal-title">🔐 Roles y Seguridad</h2>
              <p>El sistema utiliza autenticación por credenciales (usuario y contraseña) con roles diferenciados:</p>
              
              <div class="help-epi-card">
                <strong>🛠️ Administrador</strong>
                <p>Acceso total al sistema: gestionar usuarios, eliminar/restaurar registros (con papelera), eliminación permanente, ver y exportar bitácora de auditoría, cerrar/reabrir historias clínicas, exportar todos los reportes.</p>
              </div>

              <div class="help-epi-card">
                <strong>👨‍⚕️ Médico Coordinador</strong>
                <p>Gestionar usuarios, crear/editar pacientes y consultas, eliminar y restaurar registros, cerrar historias clínicas, ver bitácora de auditoría, exportar todos los reportes EPI.</p>
              </div>
              
              <div class="help-epi-card">
                <strong>👨‍⚕️ Médico</strong>
                <p>Registrar pacientes, crear y editar consultas, ver historiales, cerrar historias clínicas, exportar reportes. No puede editar datos de pacientes existentes, eliminar registros ni administrar usuarios.</p>
              </div>
              
              <div class="help-epi-card">
                <strong>👩‍⚕️ Enfermero</strong>
                <p>Registrar pacientes, ver consultas e historiales, exportar reportes. No puede crear ni editar consultas, ni acceder a funciones administrativas.</p>
              </div>

              <h3 class="help-subtitle">Controles de seguridad</h3>
              <ul class="help-list">
                <li>Contraseñas almacenadas con hash bcrypt</li>
                <li>Acciones administrativas requieren re-verificación de contraseña</li>
                <li>Usuarios nuevos reciben contraseña temporal y deben cambiarla al iniciar sesión</li>
                <li>Recuperación de contraseña por correo electrónico o por el administrador</li>
                <li>Pacientes eliminados se guardan en papelera; eliminación permanente solo para Administrador</li>
                <li>Historias clínicas cerradas protegen sus consultas contra edición o eliminación</li>
                <li>Justificación de edición registrada en cada modificación de consulta</li>
                <li>Todos los accesos requieren autenticación</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.dash-wrap { max-width: 1100px; }

/* ══ HERO ════════════════════════════════════════════════════════════════════════════════════════════════ */
.dash-hero {
  background: linear-gradient(135deg, #1E40AF, #2563EB);
  color: #fff;
  border-radius: 12px;
  padding: 28px 32px;
  margin-bottom: 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}
.dash-hero-content { flex: 1; }
.dash-title { font-size: 24px; font-weight: 700; margin: 0 0 6px; }
.dash-sub { margin: 0; font-size: 14px; opacity: 0.9; }
.dash-hero-badge {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  background: rgba(255,255,255,0.15);
  border-radius: 10px;
  padding: 12px 18px;
  flex-shrink: 0;
}
.dash-hero-icon { font-size: 32px; }
.dash-hero-label { font-size: 12px; font-weight: 600; opacity: 0.9; }

/* ══ SECTIONS ═══════════════════════════════════════════════════════════════════════════════════════════ */
.dash-section { margin-bottom: 24px; }
.dash-section-title {
  font-size: 13px;
  font-weight: 700;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0 0 14px;
  padding-left: 2px;
}

/* ══ GRID ══════════════════════════════════════════════════════════════════════════════════════════════ */
.dash-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 14px;
}
.dash-grid-tools {
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
}

/* ══ CARDS ═════════════════════════════════════════════════════════════════════════════════════════════ */
.dash-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  background: #fff;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  text-decoration: none;
  color: inherit;
  transition: all 0.2s;
  cursor: pointer;
}
.dash-card:hover {
  border-color: #93C5FD;
  box-shadow: 0 4px 16px rgba(37, 99, 235, 0.1);
  transform: translateY(-1px);
}
.dash-card-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  flex-shrink: 0;
}
.dash-card-body { flex: 1; min-width: 0; }
.dash-card-title { font-weight: 700; font-size: 14px; color: #0F172A; }
.dash-card-desc { font-size: 12px; color: #64748B; margin-top: 2px; line-height: 1.4; }
.dash-card-arrow {
  font-size: 16px;
  color: #CBD5E1;
  flex-shrink: 0;
  transition: color 0.2s, transform 0.2s;
}
.dash-card:hover .dash-card-arrow {
  color: #2563EB;
  transform: translateX(3px);
}

/* ══ EPI CARDS ══════════════════════════════════════════════════════════════════════════════════════════ */
.dash-card-epi {
  cursor: default;
}
.dash-card-epi:hover {
  transform: none;
}
.dash-epi-controls {
  display: flex;
  gap: 8px;
  margin-top: 10px;
}
.dash-epi-input {
  flex: 1;
  padding: 6px 10px;
  border: 1px solid #E2E8F0;
  border-radius: 6px;
  font-size: 12px;
  min-width: 0;
}
.dash-epi-input:focus {
  outline: none;
  border-color: #93C5FD;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
}
.dash-epi-btn {
  padding: 6px 14px;
  background: #059669;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  white-space: nowrap;
}
.dash-epi-btn:hover:not(:disabled) { background: #047857; }
.dash-epi-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* ══ TOOL CARDS ═════════════════════════════════════════════════════════════════════════════════════════ */
.dash-card-tool {
  padding: 14px 16px;
}
.dash-card-tool .dash-card-icon {
  width: 38px;
  height: 38px;
  font-size: 18px;
}

/* ══ HELP ═══════════════════════════════════════════════════════════════════════════════════════════════ */
.dash-help {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 20px;
}
.dash-help-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}
.dash-help-card {
  cursor: pointer;
  background: #fff;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  padding: 14px;
  text-align: center;
  transition: all 0.2s;
}
.dash-help-card:hover {
  border-color: #93C5FD;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
  transform: translateY(-2px);
}
.dash-help-icon { font-size: 28px; margin-bottom: 6px; }
.dash-help-card-title { font-weight: 600; font-size: 13px; color: #0F172A; margin-bottom: 2px; }
.dash-help-card-desc { font-size: 11px; color: #64748B; line-height: 1.4; }

/* ══ MODAL ══════════════════════════════════════════════════════════════════════════════════════════════ */
.help-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.help-modal {
  background: #fff;
  border-radius: 12px;
  max-width: 640px;
  width: 100%;
  max-height: 85vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.help-close {
  position: absolute;
  top: 12px;
  right: 16px;
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #64748B;
  padding: 4px 8px;
  border-radius: 6px;
  line-height: 1;
}
.help-close:hover { background: #F1F5F9; color: #0F172A; }
.help-modal-content { padding: 28px; }
.help-modal-title { font-size: 20px; font-weight: 700; color: #0F172A; margin: 0 0 16px; }
.help-subtitle { font-size: 15px; font-weight: 600; color: #334155; margin: 20px 0 8px; }
.help-list { padding-left: 20px; margin: 8px 0; line-height: 1.7; }
.help-list li { margin-bottom: 6px; color: #334155; font-size: 13px; }
.help-modal-content p { font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 8px; }
.help-epi-card {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  padding: 12px 16px;
  margin: 12px 0;
}
.help-epi-card strong { display: block; font-size: 13px; color: #0F172A; margin-bottom: 4px; }
.help-epi-card p { margin: 0; font-size: 12px; }
.help-tip {
  background: #FEF3C7;
  border: 1px solid #FCD34D;
  border-radius: 6px;
  padding: 10px 14px;
  margin-top: 16px;
  font-size: 12px;
  color: #92400E;
}
</style>