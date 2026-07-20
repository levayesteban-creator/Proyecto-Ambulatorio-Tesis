<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({ user: Object, roles: Array })

const form = useForm({
  name: props.user.name,
  id_number: props.user.id_number ?? '',
  email: props.user.email,
  phone: props.user.phone ?? '',
  password: '',
  password_confirmation: '',
  role_id: props.user.role_id,
  admin_password: '',
})

function submit() {
  form.put(route('admin.users.update', props.user.id), {
    onError: () => form.reset('admin_password'),
  })
}
</script>

<template>
  <AppLayout>
    <Head :title="'Editar - ' + user.name" />

    <div class="page-header">
      <h1>Editar Usuario</h1>
      <Link :href="route('admin.users.index')" class="btn btn-outline">Volver</Link>
    </div>

    <div class="card">
      <form @submit.prevent="submit" class="form">
        <div class="field">
          <label>Nombre completo</label>
          <input type="text" v-model="form.name" class="input" />
          <p v-if="form.errors.name" class="error">{{ form.errors.name }}</p>
        </div>

        <div class="row">
          <div class="field flex-1">
            <label>Cédula de Identidad</label>
            <input type="text" v-model="form.id_number" class="input" />
            <p v-if="form.errors.id_number" class="error">{{ form.errors.id_number }}</p>
          </div>
          <div class="field flex-1">
            <label>Teléfono</label>
            <input type="text" v-model="form.phone" class="input" />
            <p v-if="form.errors.phone" class="error">{{ form.errors.phone }}</p>
          </div>
        </div>

        <div class="field">
          <label>Correo electrónico</label>
          <input type="email" v-model="form.email" class="input" />
          <p v-if="form.errors.email" class="error">{{ form.errors.email }}</p>
        </div>

        <div class="row">
          <div class="field flex-1">
            <label>Nueva contraseña <span class="optional">(opcional)</span></label>
            <input type="password" v-model="form.password" class="input" placeholder="Dejar vacío para mantener" />
            <p v-if="form.errors.password" class="error">{{ form.errors.password }}</p>
          </div>
          <div class="field flex-1">
            <label>Confirmar contraseña</label>
            <input type="password" v-model="form.password_confirmation" class="input" />
          </div>
        </div>

        <div class="field">
          <label>Rol</label>
          <select v-if="user.role_id !== 1" v-model="form.role_id" class="input">
            <option value="" disabled>Seleccione un rol...</option>
            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
          </select>
          <div v-else class="readonly-role">
            <input type="text" :value="user.role?.name || 'Administrador'" class="input" disabled />
          </div>
          <p v-if="form.errors.role_id" class="error">{{ form.errors.role_id }}</p>
        </div>

        <hr class="divider" />

        <div class="field">
          <label>Tu contraseña (para autorizar)</label>
          <input type="password" v-model="form.admin_password" placeholder="Ingresa tu propia contraseña" class="input" />
          <p v-if="form.errors.admin_password" class="error">{{ form.errors.admin_password }}</p>
          <p class="hint">Ingresa tu contraseña para confirmar tu identidad y autorizar la modificación.</p>
        </div>

        <div class="form-actions">
          <Link :href="route('admin.users.index')" class="btn btn-outline">Cancelar</Link>
          <button type="submit" class="btn btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.25rem; font-weight: 700; color: #0F172A; margin: 0; }

.card { background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.08); padding: 1.5rem; max-width: 640px; }

.form { display: flex; flex-direction: column; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.25rem; }
.field label { font-size: 0.8125rem; font-weight: 600; color: #374151; }
.optional { font-weight: 400; color: #9CA3AF; }
.input { padding: 0.5rem 0.75rem; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 0.875rem; outline: none; transition: .12s; }
.input:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
.row { display: flex; gap: 1rem; }
.flex-1 { flex: 1; }
.error { margin: 0; font-size: 0.75rem; color: #DC2626; }
.hint { margin: 0; font-size: 0.75rem; color: #6B7280; }
.divider { border: none; border-top: 1px solid #E5E7EB; margin: 0.5rem 0; }
.form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 0.5rem; }

.btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.8125rem; font-weight: 600; border: none; cursor: pointer; text-decoration: none; transition: .12s; }
.btn-primary { background: #2563EB; color: #fff; }
.btn-primary:hover { background: #1D4ED8; }
.btn-outline { background: transparent; color: #64748B; border: 1px solid #E2E8F0; }
.btn-outline:hover { background: #F8FAFC; color: #0F172A; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
