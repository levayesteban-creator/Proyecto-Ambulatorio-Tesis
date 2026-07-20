<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ users: Array })

const deleting = ref(null)
const form = useForm({ admin_password: '' })

function confirmDelete(user) {
  deleting.value = user
  form.admin_password = ''
}

function destroy() {
  if (!deleting.value) return
  form.delete(route('admin.users.destroy', deleting.value.id), {
    onSuccess: () => { deleting.value = null },
    onFinish: () => { form.admin_password = '' },
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Usuarios" />

    <div class="page-header">
      <h1>Usuarios del Sistema</h1>
      <Link :href="route('admin.users.create')" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Nuevo Usuario
      </Link>
    </div>

    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Cédula</th>
            <th>Email / Teléfono</th>
            <th>Rol</th>
            <th>Registrado</th>
            <th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td class="font-medium">{{ user.name }}</td>
            <td>{{ user.id_number ?? '—' }}</td>
            <td>{{ user.email }}<br v-if="user.phone"><span class="text-muted">{{ user.phone ?? '' }}</span></td>
            <td>
              <span class="badge" :class="{
                'badge-admin': user.role?.name === 'Administrador',
                'badge-coord': user.role?.name === 'Médico Coordinador',
                'badge-med': user.role?.name === 'Médico',
              }">{{ user.role?.name ?? 'Sin rol' }}</span>
            </td>
            <td>{{ new Date(user.created_at).toLocaleDateString() }}</td>
            <td class="text-right">
              <Link :href="route('admin.users.edit', user.id)" class="btn btn-sm btn-outline">Editar</Link>
              <button @click="confirmDelete(user)" class="btn btn-sm btn-danger ms-2"
                      :disabled="user.id === $page.props.auth.user.id">
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Teleport to="body">
      <div v-if="deleting" class="modal-overlay" @click.self="deleting = null">
        <div class="modal">
          <h3>¿Eliminar usuario?</h3>
          <p>Se eliminará <strong>{{ deleting.name }}</strong> ({{ deleting.email }}).</p>
          <div class="form-group" style="margin-bottom: 1rem;">
            <label for="admin_password" class="form-label">Tu contraseña</label>
            <input id="admin_password" v-model="form.admin_password" type="password" class="form-input"
                   placeholder="Confirma tu contraseña" required />
            <div v-if="form.errors.admin_password" class="form-error">{{ form.errors.admin_password }}</div>
          </div>
          <div class="modal-actions">
            <button class="btn btn-outline" @click="deleting = null">Cancelar</button>
            <button class="btn btn-danger" @click="destroy" :disabled="form.processing || !form.admin_password">
              {{ form.processing ? 'Eliminando...' : 'Eliminar' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.25rem; font-weight: 700; color: #0F172A; margin: 0; }

.card { background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.08); overflow: hidden; }

.table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.table th { text-align: left; padding: 0.75rem 1rem; background: #F8FAFC; color: #64748B; font-weight: 600; border-bottom: 1px solid #E2E8F0; }
.table td { padding: 0.75rem 1rem; border-bottom: 1px solid #F1F5F9; }
.table tr:last-child td { border-bottom: none; }
.font-medium { font-weight: 600; color: #0F172A; }
.text-muted { color: #94A3B8; font-size: 0.75rem; }
.text-right { text-align: right; }

.badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.badge-admin { background: #FEE2E2; color: #DC2626; }
.badge-coord { background: #DBEAFE; color: #2563EB; }
.badge-med { background: #F3E8FF; color: #7C3AED; }

.btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.8125rem; font-weight: 600; border: none; cursor: pointer; text-decoration: none; transition: .12s; }
.btn-primary { background: #2563EB; color: #fff; }
.btn-primary:hover { background: #1D4ED8; }
.btn-outline { background: transparent; color: #64748B; border: 1px solid #E2E8F0; }
.btn-outline:hover { background: #F8FAFC; color: #0F172A; }
.btn-danger { background: #DC2626; color: #fff; }
.btn-danger:hover { background: #B91C1C; }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.ms-2 { margin-left: 0.5rem; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.modal { background: #fff; border-radius: 12px; padding: 1.5rem; width: 400px; max-width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
.modal h3 { margin: 0 0 0.5rem; font-size: 1.05rem; color: #0F172A; }
.modal p { margin: 0 0 1.5rem; color: #475569; font-size: 0.875rem; }
.modal-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
.form-group { margin-bottom: 0; }
.form-label { display: block; font-size: 0.8125rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem; }
.form-input { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 0.875rem; }
.form-input:focus { outline: none; border-color: #2563EB; box-shadow: 0 0 0 2px rgba(37,99,235,0.15); }
.form-error { color: #DC2626; font-size: 0.75rem; margin-top: 0.25rem; }
</style>
