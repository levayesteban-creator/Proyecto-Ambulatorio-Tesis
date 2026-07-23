<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ExportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- Login (por defecto) ---
Route::get('/', function () {
    return redirect('/login');
});

// --- Dashboard ---
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->must_change_password) {
        return redirect()->route('password.force-change');
    }
    return Inertia::render('Dashboard', [
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'role_id' => $user->role_id,
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// --- Reportes Médicos (vista Inertia) ---
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reportes/epi', [ReportController::class, 'epiMatrix'])->name('reports.epi.matrix');
    Route::post('/reportes/epi/data', [ReportController::class, 'epiMatrixData'])->name('reports.epi.matrix.data');
    Route::post('/reportes/epi/data-15', [ReportController::class, 'epiMatrixData15'])->name('reports.epi.matrix.data15');
    Route::post('/reportes/epi/verify', [ReportController::class, 'verifyWeek'])->name('reports.epi.verify');
    Route::get('/reportes/historical', [ReportController::class, 'historical'])->name('reports.historical');
});

// --- Verificación de datos para exportación (devuelve JSON) ---
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reportes/epi/check-data', [ReportController::class, 'checkExportData'])->name('reports.epi.check-data');
});

// --- Exportaciones PDF/CSV (respuesta binaria directa) ---
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reportes/epi10', [ReportController::class, 'exportEpi10'])->name('reports.epi10.export');
    Route::get('/reportes/epi12', [ReportController::class, 'exportEpi12'])->name('reports.epi12.export');
    Route::get('/reportes/epi13', [ReportController::class, 'exportEpi13'])->name('reports.epi13.export');
    Route::get('/reportes/epi15', [ReportController::class, 'exportEpi15'])->name('reports.epi15.export');
    Route::get('/exportar/pacientes/pdf', [ExportController::class, 'patientsPdf'])->name('export.patients.pdf');
    Route::get('/exportar/pacientes/csv', [ExportController::class, 'patientsCsv'])->name('export.patients.csv');
    Route::get('/exportar/consultas/pdf', [ExportController::class, 'consultationsPdf'])->name('export.consultations.pdf');
    Route::get('/exportar/consultas/csv', [ExportController::class, 'consultationsCsv'])->name('export.consultations.csv');
    Route::get('/exportar/bitacora/pdf', [ExportController::class, 'auditLogsPdf'])->name('export.audit-logs.pdf');
    Route::get('/exportar/bitacora/csv', [ExportController::class, 'auditLogsCsv'])->name('export.audit-logs.csv');
    Route::get('/exportar/historial/pdf', [ExportController::class, 'historicalPdf'])->name('export.historical.pdf');
});

// --- Rutas del Sistema Médico (Protegidas) ---
Route::middleware(['auth', 'verified'])->group(function () {

    // Bitácora del Sistema
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Manual de Respaldo y Recuperación
    Route::get('/help/backup', function () {
        return \Inertia\Inertia::render('HelpBackup');
    })->name('help.backup');

    // Gestión de Pacientes
    // NOTA: trashed debe ir ANTES de resource para que /patients/trashed
    // no sea capturado por la ruta show del resource como {patient}=trashed
    Route::get('/patients/trashed', [PatientController::class, 'trashed'])->name('patients.trashed');

    Route::resource('patients', PatientController::class);

    // Rutas adicionales para cerrar/reabrir historias clínicas
    Route::post('/patients/{patient}/close', [PatientController::class, 'close'])->name('patients.close');
    Route::post('/patients/{patient}/reopen', [PatientController::class, 'reopen'])->name('patients.reopen');

    // Edición de datos de contacto (accesible para todos)
    Route::get('/patients/{patient}/edit-contact', [PatientController::class, 'editContact'])->name('patients.edit-contact');
    Route::put('/patients/{patient}/contact', [PatientController::class, 'updateContact'])->name('patients.update-contact');

    // Rutas para SoftDeletes (trazabilidad legal)
    Route::post('/patients/{patient}/restore', [PatientController::class, 'restore'])->name('patients.restore');
    Route::delete('/patients/{patient}/force-delete', [PatientController::class, 'forceDelete'])->name('patients.force-delete');

    // Gestión de Consultas
    Route::prefix('consultations')->group(function () {
        Route::get('/', [ConsultationController::class, 'index'])->name('consultations.index');
        Route::get('/detail/{consultation}', [ConsultationController::class, 'show'])
            ->name('consultations.show');
    });

    // Rutas anidadas (Pacientes -> Consultas)
    Route::get('/patients/{patient}/consultations/create', [ConsultationController::class, 'create'])->name('consultations.create');
    Route::post('/patients/{patient}/consultations', [ConsultationController::class, 'store'])->name('consultations.store');
    Route::get('/patients/{patient}/consultations/history', [ConsultationController::class, 'showHistory'])->name('consultations.history');

    // Edición, Actualización y Eliminación
    Route::get('/patients/{patient}/consultations/{consultation}/edit', [ConsultationController::class, 'edit'])->name('consultations.edit');
    Route::put('/patients/{patient}/consultations/{consultation}', [ConsultationController::class, 'update'])->name('consultations.update');
    Route::delete('/patients/{patient}/consultations/{consultation}', [ConsultationController::class, 'destroy'])->name('consultations.destroy');
});

// --- Rutas de Perfil (Autenticación Breeze) ---
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Cambio obligatorio de contraseña (primer inicio)
    Route::get('/force-password-change', function () {
        $user = auth()->user();
        if (!$user || !$user->must_change_password) {
            return redirect('/dashboard');
        }
        return \Inertia\Inertia::render('Auth/ForcePasswordChange');
    })->name('password.force-change');

    Route::post('/force-password-change', [ProfileController::class, 'forcePasswordChange'])->name('password.force-change.update');
});

// --- Administración de Usuarios (solo Admin y Médico Coordinador) ---
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::put('/users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
