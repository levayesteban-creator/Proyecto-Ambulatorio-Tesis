<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const steps = [
  {
    step: '1',
    title: 'Historia Patronímica (Parte 1)',
    desc: 'Datos personales, antecedentes, hábitos y vivienda.',
    href: 'patients.create',
    label: 'Registrar paciente',
    color: '#2563EB',
  },
  {
    step: '2',
    title: 'Consulta médica (Parte 2)',
    desc: 'Anamnesis, examen funcional, examen físico, diagnóstico SIS y referencias.',
    href: 'patients.index',
    label: 'Ir a pacientes → Iniciar consulta',
    color: '#059669',
  },
]
</script>

<template>
  <Head title="Inicio" />

  <AppLayout title="Inicio">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Consultorio — El Chaparro</h2>
    </template>

    <div class="dash-wrap">
      <div class="dash-hero">
        <h1 class="dash-title">Sistema de Historias Clínicas</h1>
        <p class="dash-sub">
          Menú lateral a la izquierda. Use el botón ☰ arriba para expandir o contraer.
        </p>
      </div>

      <div class="dash-grid">
        <Link :href="route('patients.index')" class="dash-card dash-card-primary">
          <div class="dash-card-icon">👥</div>
          <div>
            <div class="dash-card-title">Pacientes</div>
            <div class="dash-card-desc">Listado, ver ficha, editar e iniciar consulta</div>
          </div>
        </Link>

        <Link :href="route('patients.create')" class="dash-card">
          <div class="dash-card-icon">📋</div>
          <div>
            <div class="dash-card-title">Nueva historia (Parte 1)</div>
            <div class="dash-card-desc">Ficha patronímica completa</div>
          </div>
        </Link>
      </div>

      <div class="dash-steps">
        <h3 class="dash-steps-title">Flujo de trabajo</h3>
        <div v-for="item in steps" :key="item.step" class="dash-step">
          <div class="dash-step-num" :style="{ background: item.color }">{{ item.step }}</div>
          <div style="flex:1">
            <div class="dash-step-title">{{ item.title }}</div>
            <div class="dash-step-desc">{{ item.desc }}</div>
            <Link :href="route(item.href)" class="dash-step-link">{{ item.label }} →</Link>
          </div>
        </div>
      </div>

      <div class="dash-note">
        <strong>Parte 2 (consulta):</strong> entre en <em>Pacientes</em>, elija un paciente y pulse
        <strong>Iniciar Consulta</strong> o <strong>+ Nueva Consulta</strong> en su ficha.
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.dash-wrap { max-width: 900px; }
.dash-hero {
  background: linear-gradient(135deg, #1E40AF, #2563EB);
  color: #fff;
  border-radius: 12px;
  padding: 24px 28px;
  margin-bottom: 24px;
}
.dash-title { font-size: 22px; font-weight: 700; margin: 0 0 8px; }
.dash-sub { margin: 0; font-size: 14px; opacity: 0.9; }
.dash-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}
.dash-card {
  display: flex;
  gap: 14px;
  align-items: flex-start;
  padding: 20px;
  background: #fff;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  text-decoration: none;
  color: inherit;
  transition: box-shadow 0.2s, border-color 0.2s;
}
.dash-card:hover {
  border-color: #93C5FD;
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.12);
}
.dash-card-primary { border-left: 4px solid #2563EB; }
.dash-card-icon { font-size: 28px; }
.dash-card-title { font-weight: 700; font-size: 15px; color: #0F172A; }
.dash-card-desc { font-size: 13px; color: #64748B; margin-top: 4px; }
.dash-steps {
  background: #fff;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
}
.dash-steps-title { margin: 0 0 16px; font-size: 14px; font-weight: 700; color: #334155; }
.dash-step {
  display: flex;
  gap: 14px;
  padding: 14px 0;
  border-bottom: 1px solid #F1F5F9;
}
.dash-step:last-child { border-bottom: none; }
.dash-step-num {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  color: #fff;
  font-weight: 700;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.dash-step-title { font-weight: 600; color: #0F172A; }
.dash-step-desc { font-size: 13px; color: #64748B; margin-top: 2px; }
.dash-step-link {
  display: inline-block;
  margin-top: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #2563EB;
  text-decoration: none;
}
.dash-step-link:hover { text-decoration: underline; }
.dash-note {
  font-size: 13px;
  color: #475569;
  background: #FEF3C7;
  border: 1px solid #FCD34D;
  border-radius: 8px;
  padding: 12px 16px;
}
</style>
