<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    patient: Object,
});

const form = useForm({
    phone_number: props.patient.phone_number || '',
    addr_sector: props.patient.addr_sector || '',
    addr_street: props.patient.addr_street || '',
    addr_house_number: props.patient.addr_house_number || '',
    addr_locality: props.patient.addr_locality || '',
    addr_parish: props.patient.addr_parish || '',
    addr_municipality: props.patient.addr_municipality || '',
    addr_state: props.patient.addr_state || '',
    addr_zip_code: props.patient.addr_zip_code || '',
    addr_reference: props.patient.addr_reference || '',
});

const filterDigits = (e) => {
  const val = e.target.value.replace(/\D/g, '');
  e.target.value = val;
  form.addr_zip_code = val;
};

const submit = () => {
    form.put(route('patients.update-contact', props.patient.id));
};
</script>

<template>
    <Head :title="'Editar Contacto - ' + patient.full_name" />

    <AppLayout>
        <div class="form-container">
            <div class="form-card max-w-3xl">
                <div class="form-header">
                    <h3 class="form-title">Editar Información de Contacto</h3>
                    <p class="form-subtitle">{{ patient.full_name }} · {{ patient.nationality }}-{{ patient.id_number }}</p>
                </div>

                <form @submit.prevent="submit" class="p-6">
                    <!-- Teléfono -->
                    <div class="form-section" style="margin-top:0">
                        <div class="section-title">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            Teléfono
                        </div>
                        <div class="form-grid grid-2">
                            <div>
                                <label class="field-label">Teléfono de Contacto</label>
                                <input v-model="form.phone_number" class="field-input" type="tel" placeholder="0412-1234567" />
                                <p v-if="form.errors.phone_number" class="field-error">{{ form.errors.phone_number }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="form-section">
                        <div class="section-title">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="section-icon">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            Dirección Actual
                        </div>
                        <div class="form-grid grid-4">
                            <div>
                                <label class="field-label">Estado</label>
                                <input v-model="form.addr_state" class="field-input" type="text" placeholder="Anzoátegui" />
                                <p v-if="form.errors.addr_state" class="field-error">{{ form.errors.addr_state }}</p>
                            </div>
                            <div>
                                <label class="field-label">Municipio</label>
                                <input v-model="form.addr_municipality" class="field-input" type="text" placeholder="Guanta" />
                                <p v-if="form.errors.addr_municipality" class="field-error">{{ form.errors.addr_municipality }}</p>
                            </div>
                            <div>
                                <label class="field-label">Parroquia</label>
                                <input v-model="form.addr_parish" class="field-input" type="text" />
                                <p v-if="form.errors.addr_parish" class="field-error">{{ form.errors.addr_parish }}</p>
                            </div>
                            <div>
                                <label class="field-label">Localidad / Barrio</label>
                                <input v-model="form.addr_locality" class="field-input" type="text" />
                                <p v-if="form.errors.addr_locality" class="field-error">{{ form.errors.addr_locality }}</p>
                            </div>
                            <div class="col-2">
                                <label class="field-label">Urbanización / Sector</label>
                                <input v-model="form.addr_sector" class="field-input" type="text" placeholder="El Chaparro" />
                                <p v-if="form.errors.addr_sector" class="field-error">{{ form.errors.addr_sector }}</p>
                            </div>
                            <div class="col-2">
                                <label class="field-label">Av. / Calle</label>
                                <input v-model="form.addr_street" class="field-input" type="text" />
                                <p v-if="form.errors.addr_street" class="field-error">{{ form.errors.addr_street }}</p>
                            </div>
                            <div>
                                <label class="field-label">N° Casa / Apto.</label>
                                <input v-model="form.addr_house_number" class="field-input" type="text" />
                                <p v-if="form.errors.addr_house_number" class="field-error">{{ form.errors.addr_house_number }}</p>
                            </div>
                            <div>
                                <label class="field-label">Código Postal</label>
                                <input :value="form.addr_zip_code" class="field-input" type="text" style="font-family:'DM Mono',monospace" @input="filterDigits" />
                                <p v-if="form.errors.addr_zip_code" class="field-error">{{ form.errors.addr_zip_code }}</p>
                            </div>
                            <div class="col-full">
                                <label class="field-label">Punto de Referencia</label>
                                <input v-model="form.addr_reference" class="field-input" type="text" placeholder="Frente a…" />
                                <p v-if="form.errors.addr_reference" class="field-error">{{ form.errors.addr_reference }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <Link :href="route('patients.show', patient.id)" class="btn btn-secondary">
                            Cancelar
                        </Link>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            {{ form.processing ? 'Guardando...' : 'Guardar Contacto' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.form-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 24px;
}
.form-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    overflow: hidden;
}
.form-header {
    padding: 24px 24px 0;
}
.form-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px;
}
.form-subtitle {
    font-size: 13px;
    color: #64748B;
    margin: 0;
}
.field-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.field-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 13px;
    color: #1e293b;
    background: white;
    transition: border-color 0.15s;
    box-sizing: border-box;
}
.field-input:focus {
    outline: none;
    border-color: #2563EB;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.field-error {
    color: #DC2626;
    font-size: 11px;
    margin-top: 4px;
}
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding-top: 8px;
}
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.15s;
    text-decoration: none;
    color: #475569;
    background: white;
}
.btn:hover { background: #f8fafc; }
.btn-primary {
    background: #2563EB;
    color: white;
    border-color: #2563EB;
}
.btn-primary:hover { background: #1d4ed8; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary {
    color: #475569;
    background: white;
}
.form-section { margin-bottom: 28px; }
.section-title {
  font-size: 11px; font-weight: 700; letter-spacing: 0.1em;
  text-transform: uppercase; color: #64748B;
  padding-bottom: 10px; margin-bottom: 16px;
  border-bottom: 1px solid #E2E8F0;
  display: flex; align-items: center; gap: 8px;
}
.section-icon { width: 14px; height: 14px; }
.form-grid { display: grid; gap: 14px; }
.grid-2    { grid-template-columns: repeat(2, 1fr); }
.grid-4    { grid-template-columns: repeat(4, 1fr); }
.col-2     { grid-column: span 2; }
.col-full  { grid-column: 1 / -1; }
@media (max-width: 900px) {
  .grid-2,.grid-4 { grid-template-columns: 1fr 1fr; }
  .col-full       { grid-column: 1 / -1; }
}
@media (max-width: 600px) {
  .grid-2,.grid-4 { grid-template-columns: 1fr; }
  .col-2 { grid-column: 1; }
}
</style>
