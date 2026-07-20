<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const form = useForm({
  password: '',
  password_confirmation: '',
})

function submit() {
  form.post(route('password.force-change.update'), {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <Head title="Cambiar contraseña" />

  <AppLayout>
    <div class="py-12">
      <div class="max-w-lg mx-auto">
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-1">Cambia tu contraseña</h2>
          <p class="text-sm text-gray-500 mb-4">
            Esta es tu primera vez iniciando sesión. Por seguridad, debes cambiar la contraseña temporal por una nueva.
          </p>

          <form @submit.prevent="submit" class="space-y-4">
            <div class="field">
              <label>Nueva contraseña</label>
              <input type="password" v-model="form.password" class="input" />
              <p v-if="form.errors.password" class="error">{{ form.errors.password }}</p>
            </div>

            <div class="field">
              <label>Confirmar contraseña</label>
              <input type="password" v-model="form.password_confirmation" class="input" />
            </div>

            <button type="submit" class="btn btn-primary w-full" :disabled="form.processing">
              {{ form.processing ? 'Guardando...' : 'Cambiar contraseña' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.field { display: flex; flex-direction: column; gap: 0.25rem; }
.field label { font-size: 0.8125rem; font-weight: 600; color: #374151; }
.input { padding: 0.5rem 0.75rem; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 0.875rem; outline: none; }
.input:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
.error { margin: 0; font-size: 0.75rem; color: #DC2626; }
.btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.8125rem; font-weight: 600; border: none; cursor: pointer; }
.btn-primary { background: #2563EB; color: #fff; }
.btn-primary:hover { background: #1D4ED8; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.w-full { width: 100%; }
</style>
