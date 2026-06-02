<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

defineProps({
  title:      { type: String, default: '' },
  breadcrumb: { type: String, default: '' },
})

/** Preferencia: barra contraída (solo iconos) vs expandida (texto completo) */
const SIDEBAR_STORAGE_KEY = 'gestion-salud.sidebar-collapsed'
const LEGACY_HIDDEN_KEY = 'gestion-salud.sidebar-hidden'

const page = usePage()
const user = computed(() => page.props.auth.user)
const initials = computed(() => {
  if (!user.value?.name) return 'DR'
  return user.value.name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
})

/** false = expandida (240px) · true = contraída (72px) — la barra NUNCA desaparece */
const sidebarCollapsed = ref(false)

onMounted(() => {
  try {
    localStorage.removeItem(LEGACY_HIDDEN_KEY)
    const saved = localStorage.getItem(SIDEBAR_STORAGE_KEY)
    if (saved !== null) {
      sidebarCollapsed.value = saved === '1'
    }
  } catch {
    /* localStorage no disponible */
  }
})

watch(sidebarCollapsed, (collapsed) => {
  try {
    localStorage.setItem(SIDEBAR_STORAGE_KEY, collapsed ? '1' : '0')
  } catch {
    /* ignore */
  }
})

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const toasts = ref([])

const addToast = (type, message) => {
  const id = Date.now()
  toasts.value.push({ id, type, message })
  setTimeout(() => removeToast(id), 3500)
}

const removeToast = (id) => {
  toasts.value = toasts.value.filter(t => t.id !== id)
}

const flash = computed(() => page.props.flash)
if (flash.value?.success) addToast('success', flash.value.success)
if (flash.value?.error) addToast('error', flash.value.error)

const logout = () => router.post(route('logout'))
</script>

<template>
  <div
    class="app-shell"
    :class="{ 'sidebar-collapsed': sidebarCollapsed }"
  >

    <aside class="sidebar" :aria-expanded="!sidebarCollapsed">
      <div class="sidebar-logo">
        <div class="logo-icon" title="SistemaMed">
          <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
          </svg>
        </div>
        <div class="logo-text">
          SistemaMed
          <span class="logo-sub">MPPS · El Chaparro</span>
        </div>
      </div>

      <div class="nav-section-title">Principal</div>

      <Link class="nav-item" :href="route('dashboard')" :title="'Inicio'"
            :class="{ active: $page.url === '/dashboard' }">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
          <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        <span class="nav-label">Inicio</span>
      </Link>

      <Link class="nav-item" :href="route('patients.index')" :title="'Pacientes'"
            :class="{ active: $page.url.startsWith('/patients') && $page.url !== '/patients/create' }">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        <span class="nav-label">Pacientes</span>
      </Link>

      <Link class="nav-item" :href="route('patients.create')" :title="'Historia Clínica'"
            :class="{ active: $page.url === '/patients/create' }">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14,2 14,8 20,8"/>
          <line x1="12" y1="13" x2="12" y2="17"/><line x1="10" y1="15" x2="14" y2="15"/>
        </svg>
        <span class="nav-label">Historia Clínica</span>
      </Link>

      <div class="nav-section-title">Sistema</div>

      <Link class="nav-item" :href="route('profile.edit')" :title="'Mi perfil'"
            :class="{ active: $page.url.startsWith('/profile') }">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="3"/>
          <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 19.07a10 10 0 0 1 0-14.14"/>
        </svg>
        <span class="nav-label">Mi perfil</span>
      </Link>

      <div class="sidebar-footer">
        <div class="user-card" @click="logout" title="Cerrar sesión">
          <div class="user-avatar">{{ initials }}</div>
          <div class="user-info">
            <div class="user-name">{{ user?.name }}</div>
            <div class="user-role">Médico General</div>
          </div>
        </div>
      </div>
    </aside>

    <div class="main-content">

      <header class="app-header">
        <button
          type="button"
          class="toggle-btn"
          :title="sidebarCollapsed ? 'Expandir menú' : 'Contraer menú'"
          aria-label="Alternar menú lateral"
          @click="toggleSidebar"
        >
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>

        <div class="breadcrumb-nav">
          <span class="bc-item">Historia Clínica</span>
          <span class="bc-sep">›</span>
          <span class="bc-current">{{ title || breadcrumb || 'Registro' }}</span>
        </div>

        <div class="header-search">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
               style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input type="text" placeholder="Buscar por nombre o cédula…" @keydown.enter.prevent />
        </div>

        <div class="header-actions">
          <div class="header-avatar" :title="user?.name">{{ initials }}</div>
        </div>
      </header>

      <div class="page-body">
        <header v-if="$slots.header" class="page-header">
          <slot name="header" />
        </header>
        <slot />
      </div>

    </div>

    <div class="toast-container">
      <TransitionGroup name="toast">
        <div v-for="t in toasts" :key="t.id" class="toast" :class="`toast-${t.type}`">
          <div class="toast-icon">
            <svg v-if="t.type === 'success'" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <polyline points="20,6 9,17 4,12"/>
            </svg>
            <svg v-else width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </div>
          <span>{{ t.message }}</span>
          <button type="button" @click="removeToast(t.id)" class="toast-close">×</button>
        </div>
      </TransitionGroup>
    </div>

  </div>
</template>

<style scoped>
.app-shell {
  --sidebar-w-expanded: 240px;
  --sidebar-w-collapsed: 72px;
  --sidebar-w: var(--sidebar-w-expanded);
  --primary: #2563EB;
  --surface: #FFFFFF;
  --bg: #F1F5F9;
  --border: #E2E8F0;
  --text: #0F172A;
  --text-muted: #64748B;
  min-height: 100vh;
  background: var(--bg);
  font-family: 'DM Sans', 'Figtree', sans-serif;
}

.app-shell.sidebar-collapsed {
  --sidebar-w: var(--sidebar-w-collapsed);
}

.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: var(--sidebar-w);
  background: #0F172A;
  display: flex;
  flex-direction: column;
  z-index: 200;
  overflow-x: hidden;
  overflow-y: auto;
  transition: width 0.25s ease;
  box-shadow: 4px 0 24px rgba(15, 23, 42, 0.2);
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 18px 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  min-height: 64px;
}

.logo-icon {
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  background: linear-gradient(135deg, #2563EB, #0EA5E9);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-text {
  font-size: 15px;
  font-weight: 700;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  transition: opacity 0.2s, max-width 0.25s;
  max-width: 160px;
}

.logo-sub {
  font-size: 10px;
  color: #64748B;
  font-weight: 400;
  display: block;
}

.nav-section-title {
  font-size: 9px;
  font-weight: 600;
  letter-spacing: 0.12em;
  color: #475569;
  text-transform: uppercase;
  padding: 16px 16px 6px;
  white-space: nowrap;
  overflow: hidden;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  margin: 1px 8px;
  border-radius: 8px;
  color: #94A3B8;
  font-size: 13.5px;
  font-weight: 500;
  transition: all 0.18s;
  text-decoration: none;
}

.nav-item:hover { background: rgba(255, 255, 255, 0.06); color: #fff; }
.nav-item.active { background: #1E40AF; color: #fff; }
.nav-item svg { width: 17px; height: 17px; flex-shrink: 0; }

.nav-label {
  white-space: nowrap;
  overflow: hidden;
}

.sidebar-footer {
  margin-top: auto;
  border-top: 1px solid rgba(255, 255, 255, 0.07);
  padding: 12px 8px;
}

.user-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 8px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.18s;
}

.user-card:hover { background: rgba(255, 255, 255, 0.06); }

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  flex-shrink: 0;
  background: linear-gradient(135deg, #2563EB, #7C3AED);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 11px;
  font-weight: 700;
}

.user-info {
  overflow: hidden;
  min-width: 0;
}

.user-name  { font-size: 12px; font-weight: 600; color: #fff; white-space: nowrap; }
.user-role  { font-size: 10px; color: #64748B; white-space: nowrap; }

/* Modo contraído: solo iconos, barra siempre visible */
.sidebar-collapsed .logo-text,
.sidebar-collapsed .nav-section-title,
.sidebar-collapsed .nav-label,
.sidebar-collapsed .user-info {
  opacity: 0;
  max-width: 0;
  width: 0;
  padding: 0;
  margin: 0;
  pointer-events: none;
}

.sidebar-collapsed .sidebar-logo {
  justify-content: center;
  padding-left: 8px;
  padding-right: 8px;
}

.sidebar-collapsed .nav-item {
  justify-content: center;
  padding-left: 0;
  padding-right: 0;
  margin-left: 10px;
  margin-right: 10px;
}

.main-content {
  margin-left: var(--sidebar-w);
  transition: margin-left 0.25s ease;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  width: calc(100% - var(--sidebar-w));
}

.app-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border);
  height: 64px;
  display: flex;
  align-items: center;
  padding: 0 24px;
  gap: 16px;
}

.toggle-btn {
  width: 40px;
  height: 40px;
  padding: 0;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  transition: all 0.18s;
  flex-shrink: 0;
}

.toggle-btn:hover {
  background: var(--bg);
  color: var(--text);
  border-color: #94a3b8;
}

.breadcrumb-nav { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.bc-item    { font-size: 12px; color: var(--text-muted); }
.bc-sep     { color: #CBD5E1; font-size: 12px; }
.bc-current { font-size: 13px; font-weight: 600; color: var(--text); }

.header-search {
  flex: 1;
  max-width: 360px;
  margin-left: auto;
  position: relative;
}

.header-search input {
  width: 100%;
  padding: 8px 12px 8px 34px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: var(--bg);
  font-size: 13px;
  color: var(--text);
  outline: none;
  font-family: inherit;
}

.header-actions { display: flex; align-items: center; gap: 8px; }

.header-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2563EB, #7C3AED);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
}

.page-body { padding: 24px; flex: 1; max-width: 100%; }

.page-header {
  margin-bottom: 20px;
  padding: 16px 20px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 10px;
}

.page-header :deep(h2) {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text);
}

.toast-container {
  position: fixed;
  top: 80px;
  right: 24px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.toast {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
  font-size: 13px;
  min-width: 280px;
}

.toast-icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toast-success .toast-icon { background: #D1FAE5; color: #059669; }
.toast-error   .toast-icon { background: #FEE2E2; color: #DC2626; }
.toast-close {
  margin-left: auto;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
  color: var(--text-muted);
}

.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(20px); }

@media (max-width: 768px) {
  .header-search { display: none; }
  .page-body { padding: 16px; }
}
</style>
