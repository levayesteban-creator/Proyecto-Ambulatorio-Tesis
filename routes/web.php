<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ConsultationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Página de bienvenida del sistema
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard principal
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/**
 * RUTAS PROTEGIDAS (SISTEMA MÉDICO)
 * Solo usuarios autenticados y verificados pueden acceder a los datos clínicos
 */
Route::middleware(['auth', 'verified'])->group(function () {

    /**
     * GESTIÓN DE PACIENTES (Planilla EPI-12 e Historial)
     * Convención 'resource' nativa de Laravel
     */
    Route::resource('patients', PatientController::class);

    /**
     * GESTIÓN DE CONSULTAS (Anamnesis / Historia Clínica Acumulativa)
     * Rediseño unificado utilizando rutas anidadas semánticas (RESTful)
     */
    Route::prefix('consultations')->group(function () {

        // Detalle: inspección minuciosa de un evento clínico específico por ID de consulta
        Route::get('/detail/{consultation}', [ConsultationController::class, 'show'])
            ->name('consultations.show');
    });

    /**
     * RUTAS ANIDADAS DE PACIENTES Y CONSULTAS
     * Expresa explícitamente que la consulta pertenece y depende de un paciente
     */
    // Formulario para nueva consulta (Carga datos filiatorios del paciente)
    Route::get('/patients/{patient}/consultations/create', [ConsultationController::class, 'create'])
        ->name('consultations.create');

    // Guardar consulta: Enfoque RESTful del segundo bloque adaptado al middleware verificado
    Route::post('/patients/{patient}/consultations', [ConsultationController::class, 'store'])
        ->name('consultations.store');

    // Historial: despliega cronológicamente el registro acumulativo del paciente específico
    Route::get('/patients/{patient}/consultations/history', [ConsultationController::class, 'showHistory'])
        ->name('consultations.history');

    /**
     * RUTAS DE EDICIÓN DE CONSULTAS (NUEVAS)
     * Permitir editar consultas existentes del paciente
     */
    // Formulario para editar consulta existente
    Route::get('/patients/{patient}/consultations/{consultation}/edit', [ConsultationController::class, 'edit'])
        ->name('consultations.edit');

    // Actualizar consulta: Almacenar cambios en la consulta editada
    Route::put('/patients/{patient}/consultations/{consultation}', [ConsultationController::class, 'update'])
        ->name('consultations.update');

    // Opcionalmente: ruta para eliminar consulta (si lo requieres)
    Route::delete('/patients/{patient}/consultations/{consultation}', [ConsultationController::class, 'destroy'])
        ->name('consultations.destroy');
});

/**
 * RUTAS DE PERFIL DE USUARIO (Autenticación Breeze)
 */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
