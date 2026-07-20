<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Respaldo y Recuperación" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Respaldo y Recuperación</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Generar respaldo manual -->
                <div class="p-6 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Generar respaldo manual</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        Para generar un respaldo en cualquier momento, abre la terminal (CMD o PowerShell) y ejecuta:
                    </p>
                    <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm mb-2">
                        cd C:\laragon\www\gestion-salud<br>
                        php artisan db:backup --cloud
                    </div>
                    <p class="text-xs text-gray-500">
                        El respaldo se enviará automáticamente al correo <strong>consultoriochaparrodeguanta@gmail.com</strong>
                    </p>
                </div>

                <!-- Respaldo automático -->
                <div class="p-6 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Respaldo automático diario</h3>
                    <p class="text-sm text-gray-600">
                        Todas las madrugadas a las <strong>2:00 AM</strong> el sistema genera un respaldo completo de la base
                        de datos y lo envía al correo del consultorio. No requiere ninguna acción manual.
                    </p>
                </div>

                <!-- Restaurar respaldo -->
                <div class="p-6 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Restaurar un respaldo</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        Si el sistema se daña o pierdes datos, puedes restaurar cualquier respaldo previo:
                    </p>

                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-sm text-gray-800 mb-1">Paso 1: Descarga el respaldo</h4>
                            <p class="text-xs text-gray-500">
                                Busca en el correo <strong>consultoriochaparrodeguanta@gmail.com</strong>
                                el archivo <code>.sql</code> más reciente con asunto "Respaldo BD".
                                Descarga el adjunto y colócalo en:
                            </p>
                            <div class="bg-gray-100 p-3 rounded-lg font-mono text-xs mt-1">
                                C:\laragon\www\gestion-salud\storage\app\backups\
                            </div>
                        </div>

                        <div>
                            <h4 class="font-medium text-sm text-gray-800 mb-1">Paso 2: Restaura con un solo comando</h4>
                            <p class="text-xs text-gray-500 mb-1">Abre la terminal y ejecuta:</p>
                            <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm mb-1">
                                cd C:\laragon\www\gestion-salud<br>
                                php artisan db:restore
                            </div>
                            <p class="text-xs text-gray-500">
                                Te mostrará la lista de respaldos disponibles. Selecciona el número y confirma.
                            </p>
                        </div>

                        <div>
                            <h4 class="font-medium text-sm text-gray-800 mb-1">Paso 3: Verifica</h4>
                            <p class="text-xs text-gray-500">
                                Ingresa al sistema y verifica que los datos estén completos.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Estructura del respaldo -->
                <div class="p-6 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">¿Qué contiene el respaldo?</h3>
                    <p class="text-sm text-gray-600">
                        Un solo archivo <code>.sql</code> con todas las tablas del sistema: pacientes, historias clínicas,
                        consultas, diagnósticos, exámenes, usuarios, roles, bitácora de auditoría y más.
                        Pesa aproximadamente <strong>~450 KB</strong>.
                    </p>
                </div>

                <!-- Nota importante -->
                <div class="p-6 bg-yellow-50 border-l-4 border-yellow-400 shadow sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">⚠ Importante</h3>
                    <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                        <li>No compartas el archivo <code>.sql</code> con nadie — contiene datos sensibles de pacientes</li>
                        <li>La contraseña de Gmail del consultorio solo debe tenerla el Administrador</li>
                        <li>Ejecuta <code>php artisan db:restore</code> solo si estás seguro de querer reemplazar los datos actuales</li>
                    </ul>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
