<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const page = usePage()
const emailError = computed(() => page.props.flash?.email_error || false)

const form = useForm({
    identifier: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar Contraseña" />

        <div class="security-notice">
          <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
          <p class="text-xs text-gray-600">
            Por seguridad, el enlace de recuperación se enviará <strong>automáticamente</strong> al correo registrado en tu cuenta. No puedes elegir otro correo.
          </p>
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm" :class="emailError ? 'text-amber-600 bg-amber-50 p-3 rounded-lg border border-amber-200' : 'text-green-600'">
          <template v-if="emailError">
            <div class="flex items-start gap-2">
              <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <div>
                <p class="font-semibold mb-1">No se pudo enviar el correo</p>
                <p class="text-xs opacity-80">Puede ser por falta de conexión a internet.</p>
              </div>
            </div>
          </template>
          <template v-else>
            {{ status }}
          </template>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="identifier" value="Cédula o correo registrado" />

                <TextInput
                    id="identifier"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.identifier"
                    placeholder="Ej: 12345678 o tu@correo.com"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />

                <p class="mt-1 text-xs text-gray-400">
                  Ingresa tu cédula o el correo que tienes registrado en el sistema.
                </p>
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ form.processing ? 'Enviando...' : 'Enviar enlace de recuperación' }}
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-6 pt-4 border-t border-gray-200">
          <p class="text-xs text-gray-500 text-center mb-3">¿No recibiste el correo o no tienes acceso a internet?</p>
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex items-start gap-2">
              <svg class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <div>
                <p class="text-sm font-semibold text-blue-800">Contacta al administrador</p>
                <p class="text-xs text-blue-600 mt-1">
                  El administrador puede generar una contraseña temporal directamente desde el sistema sin necesidad de correo electrónico.
                </p>
              </div>
            </div>
          </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
.security-notice {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  background: #EFF6FF;
  border: 1px solid #BFDBFE;
  border-radius: 8px;
  padding: 0.75rem;
  margin-bottom: 1rem;
}
</style>
