<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DatePicker from '@/Components/DatePicker.vue';

const props = defineProps({
    logs: Object,
    filters: Object,
    users: Array,
});

const selectedUser = ref(props.filters.user_id || '');
const selectedAction = ref(props.filters.action || '');
const selectedModel = ref(props.filters.model_type || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const showLogDetail = ref(null);

const applyFilters = () => {
    router.get(route('audit-logs.index'), {
        user_id: selectedUser.value || '',
        action: selectedAction.value || '',
        model_type: selectedModel.value || '',
        date_from: dateFrom.value || '',
        date_to: dateTo.value || '',
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    selectedUser.value = '';
    selectedAction.value = '';
    selectedModel.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    router.get(route('audit-logs.index'), {}, { preserveState: true, replace: true });
};

const actionLabels = {
    created: 'Creación',
    updated: 'Actualización',
    deleted: 'Eliminación',
};

const actionColors = {
    created: 'label-success',
    updated: 'label-warning',
    deleted: 'label-danger',
};

const modelLabels = {
    'App\\Models\\Patient': 'Paciente',
    'App\\Models\\Consultation': 'Consulta',
    'App\\Models\\User': 'Usuario',
    'App\\Models\\SisDiagnosis': 'Diagnóstico SIS',
};

const shortModel = (type) => modelLabels[type] || type.split('\\').pop();

const exportUrl = (format) => {
    const params = new URLSearchParams();
    if (selectedUser.value) params.set('user_id', selectedUser.value);
    if (selectedAction.value) params.set('action', selectedAction.value);
    if (selectedModel.value) params.set('model_type', selectedModel.value);
    if (dateFrom.value) params.set('date_from', dateFrom.value);
    if (dateTo.value) params.set('date_to', dateTo.value);
    const qs = params.toString();
    return route(`export.audit-logs.${format}`) + (qs ? '?' + qs : '');
};
</script>

<template>
    <Head title="Bitácora del Sistema" />

    <AppLayout>
        <div class="table-container">
            <div class="table-header-row">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <h2 class="table-title">Registro de Actividad</h2>
                    <div style="display:flex;gap:8px">
                        <a :href="exportUrl('pdf')" target="_blank" class="btn btn-export-pdf">📄 PDF</a>
                        <a :href="exportUrl('csv')" class="btn btn-export-csv">📊 Excel</a>
                    </div>
                </div>
            </div>

            <div class="filters-row">
                <select v-model="selectedUser" class="field-input filter-select">
                    <option value="">Todos los usuarios</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>

                <select v-model="selectedAction" class="field-input filter-select">
                    <option value="">Todas las acciones</option>
                    <option value="created">Creación</option>
                    <option value="updated">Actualización</option>
                    <option value="deleted">Eliminación</option>
                </select>

                <select v-model="selectedModel" class="field-input filter-select">
                    <option value="">Todos los modelos</option>
                    <option value="App\Models\Patient">Pacientes</option>
                    <option value="App\Models\Consultation">Consultas</option>
                    <option value="App\Models\User">Usuarios</option>
                    <option value="App\Models\SisDiagnosis">Diagnósticos SIS</option>
                </select>

                <DatePicker v-model="dateFrom" placeholder="dd/mm/aaaa" class="field-input filter-select" />
                <DatePicker v-model="dateTo" placeholder="dd/mm/aaaa" class="field-input filter-select" />

                <button @click="applyFilters" class="btn btn-primary btn-sm" style="margin-left:auto">Filtrar</button>
                <button @click="clearFilters" class="btn btn-sm" style="margin-left:4px">Limpiar</button>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Modelo</th>
                        <th>ID</th>
                        <th>IP</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="log in logs.data" :key="log.id">
                        <td class="td-mono">{{ new Date(log.created_at).toLocaleString('es-VE') }}</td>
                        <td>{{ log.user?.name || '—' }}</td>
                        <td><span class="label" :class="actionColors[log.action]">{{ actionLabels[log.action] || log.action }}</span></td>
                        <td>{{ shortModel(log.model_type) }}</td>
                        <td class="td-mono">{{ log.model_id }}</td>
                        <td class="td-mono">{{ log.ip_address || '—' }}</td>
                        <td>
                            <button @click="showLogDetail = log" class="btn-link" title="Ver detalle">Ver</button>
                        </td>
                    </tr>
                    <tr v-if="logs.data.length === 0">
                        <td colspan="7" class="td-empty">No se encontraron registros</td>
                    </tr>
                </tbody>
            </table>

            <div v-if="logs.links" class="pagination-row">
                <Link v-for="(link, i) in logs.links" :key="i"
                      :href="link.url || '#'"
                      v-html="link.label"
                      class="page-link"
                      :class="{ active: link.active, disabled: !link.url }" />
            </div>
        </div>

        <div v-if="showLogDetail" class="modal-overlay" @click.self="showLogDetail = null">
            <div class="modal-card">
                <div class="modal-header">
                    <h3>Detalle del Cambio</h3>
                    <button @click="showLogDetail = null" class="btn-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p><strong>Modelo:</strong> {{ shortModel(showLogDetail.model_type) }} #{{ showLogDetail.model_id }}</p>
                    <p><strong>Acción:</strong> <span class="label" :class="actionColors[showLogDetail.action]">{{ actionLabels[showLogDetail.action] }}</span></p>
                    <p><strong>Usuario:</strong> {{ showLogDetail.user?.name }} (IP: {{ showLogDetail.ip_address }})</p>
                    <p><strong>Fecha:</strong> {{ new Date(showLogDetail.created_at).toLocaleString('es-VE') }}</p>

                    <div v-if="showLogDetail.old_values || showLogDetail.new_values" style="margin-top:12px">
                        <table class="data-table" style="font-size:12px">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Valor Anterior</th>
                                    <th>Valor Nuevo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(newVal, field) in showLogDetail.new_values" :key="field">
                                    <td class="td-mono">{{ field }}</td>
                                    <td class="td-old">{{ showLogDetail.old_values?.[field] ?? '—' }}</td>
                                    <td class="td-new">{{ newVal }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="showLogDetail = null" class="btn btn-primary">Cerrar</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:root {
    --surface: #f8fafc;
    --danger: #DC2626;
    --success: #059669;
    --text-secondary: #64748B;
    --primary: #2563EB;
}
.filters-row {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
    padding: 12px 16px;
    background: var(--surface);
    border-radius: 8px;
    margin-bottom: 16px;
}
.filter-select {
    max-width: 200px;
}
.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    overflow: hidden;
}
.table-header-row {
    padding: 20px 24px 0;
}
.table-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px;
}
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.data-table th {
    text-align: left;
    padding: 10px 16px;
    font-weight: 600;
    color: #64748B;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}
.data-table td {
    padding: 10px 16px;
    border-bottom: 1px solid #f1f5f9;
}
.pagination-row {
    display: flex;
    justify-content: center;
    gap: 4px;
    padding: 16px;
}
.page-link {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    color: #475569;
    text-decoration: none;
    transition: all 0.15s;
}
.page-link:hover { background: #f1f5f9; }
.page-link.active {
    background: var(--primary);
    color: white;
}
.page-link.disabled {
    color: #cbd5e1;
    pointer-events: none;
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
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
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
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}
.btn-primary:hover { background: #1d4ed8; }
.btn-sm { padding: 6px 12px; font-size: 12px; }
.btn-export-pdf {
    background: #DC2626;
    color: white;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    text-decoration: none;
}
.btn-export-pdf:hover { background: #B91C1C; }
.btn-export-csv {
    background: #059669;
    color: white;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    text-decoration: none;
}
.btn-export-csv:hover { background: #047857; }
.td-mono {
    font-family: 'DM Mono', monospace;
    font-size: 12px;
}
.td-old {
    color: var(--danger);
    font-size: 12px;
}
.td-new {
    color: var(--success);
    font-size: 12px;
}
.td-empty {
    text-align: center;
    padding: 32px;
    color: var(--text-secondary);
}
.btn-link {
    background: none;
    border: none;
    color: var(--primary);
    cursor: pointer;
    text-decoration: underline;
    font-size: 13px;
}
.label {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}
.label-success { background: #d4edda; color: #155724; }
.label-warning { background: #fff3cd; color: #856404; }
.label-danger  { background: #f8d7da; color: #721c24; }
.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-card {
    background: white;
    border-radius: 12px;
    max-width: 800px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}
.modal-header, .modal-footer {
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-body { padding: 0 24px 16px; }
.btn-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-secondary);
}
</style>
