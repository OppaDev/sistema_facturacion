<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\CategoriasController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\TurnosCajaController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');



Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'check.user.status'])->name('dashboard');

Route::post('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'check.user.status'])->name('dashboard.post');

Route::middleware(['auth', 'verified', 'check.user.status'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/cancelar-borrado', [ProfileController::class, 'cancelarBorradoCuenta'])->name('profile.cancelarBorradoCuenta');
});

Route::middleware(['auth', 'verified', 'check.user.status'])->group(function () {

    // Gestión de Usuarios (incluyendo clientes): Solo Administrador y Secretario
    Route::resource('users', UserController::class)
        ->middleware('role:Administrador|Secretario')
        ->except(['show']);
    
    // Rutas adicionales para usuarios
    Route::middleware('role:Administrador|Secretario')->group(function () {
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.forceDelete');
        Route::post('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    });

    // Rutas de exportar y reportes (deben ir antes del resource para evitar conflicto)
    Route::middleware('role:Administrador|Bodega')->group(function () {
        Route::get('productos/export/{type}', [ProductosController::class, 'export'])->name('productos.export');
        Route::get('productos/reporte', [ProductosController::class, 'reporte'])->name('productos.reporte');
    });
    // Productos: Solo Bodega y Administrador
    Route::resource('productos', ProductosController::class)
        ->middleware('role:Administrador|Bodega');

    // Rutas adicionales para productos
    Route::middleware('role:Administrador|Bodega')->group(function () {
        Route::post('productos/{id}/restore', [ProductosController::class, 'restore'])->name('productos.restore');
        Route::post('productos/{id}/forceDelete', [ProductosController::class, 'forceDelete'])->name('productos.forceDelete');
    });

    // Categorías: Solo Bodega y Administrador
    Route::resource('categorias', CategoriasController::class)
        ->middleware('role:Administrador|Bodega');

    // Rutas adicionales para categorías
    Route::middleware('role:Administrador|Bodega')->group(function () {
        Route::post('categorias/{id}/restore', [CategoriasController::class, 'restore'])->name('categorias.restore');
        Route::post('categorias/{id}/forceDelete', [CategoriasController::class, 'forceDelete'])->name('categorias.forceDelete');
    });

    // Auditoría: Solo Administrador
    Route::get('/auditorias/export', [AuditoriaController::class, 'export'])
        ->name('auditorias.export')
        ->middleware('role:Administrador');
    Route::resource('auditorias', AuditoriaController::class)
        ->middleware('role:Administrador');

    // Rutas de Roles: Solo Administrador
    Route::middleware('role:Administrador')->group(function () {
        Route::get('/roles', [RolesController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RolesController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RolesController::class, 'store'])->name('roles.store');
        Route::delete('/roles/{id}', [RolesController::class, 'destroy'])->name('roles.destroy');
        
        // Rutas adicionales para usuarios (solo Administrador)
        Route::post('/users/{user}/toggle-estado', [UserController::class, 'toggleEstado'])->name('users.toggleEstado');
        Route::post('/users/{user}/activar', [UserController::class, 'activarUsuario'])->name('users.activar');
        Route::post('/users/{user}/desactivar', [UserController::class, 'desactivarUsuario'])->name('users.desactivar');
        Route::post('/users/cancelar-borrado', [UserController::class, 'cancelarBorradoCuenta'])->name('users.cancelarBorradoCuenta');
    });

    // ============================================
    // MÓDULO CAJA (Punto de Venta)
    // Acceso: Solo Administrador y Ventas
    // ============================================
    Route::middleware('role:Administrador|Ventas')->prefix('caja')->name('caja.')->group(function () {
        
        // ==================== VENTAS ====================
        
        // Punto de Venta (POS) - Vista principal
        Route::get('/pos', [CajaController::class, 'pos'])->name('pos');
        
        // Listado de ventas
        Route::get('/', [CajaController::class, 'index'])->name('index');
        
        // Registrar venta (desde POS)
        Route::post('/', [CajaController::class, 'store'])->name('store');
        
        
        // ==================== TURNOS DE CAJA ====================
        // IMPORTANTE: Turnos ANTES de las rutas con {id} para evitar conflictos
        
        Route::prefix('turnos')->name('turnos.')->group(function () {
            
            // Listado de turnos
            Route::get('/', [TurnosCajaController::class, 'index'])->name('index');
            
            // Formulario para abrir turno
            Route::get('/create', [TurnosCajaController::class, 'create'])->name('create');
            
            // Guardar nuevo turno (apertura)
            Route::post('/', [TurnosCajaController::class, 'store'])->name('store');
            
            // Ver detalle de un turno
            Route::get('/{id}', [TurnosCajaController::class, 'show'])->name('show');
            
            // Formulario para cerrar turno
            Route::get('/{id}/cierre', [TurnosCajaController::class, 'cierre'])->name('cierre');
            
            // Procesar cierre de turno
            Route::post('/{id}/cerrar', [TurnosCajaController::class, 'cerrar'])->name('cerrar');
            
            // Reporte PDF de cierre
            Route::get('/{id}/reporte', [TurnosCajaController::class, 'reporteCierre'])->name('reporte');
            
            // Registrar movimiento de caja (solo Administrador)
            Route::post('/{id}/movimientos', [TurnosCajaController::class, 'storeMovimiento'])
                ->name('movimientos.store')
                ->middleware('role:Administrador');
        });
        
        
        // ==================== RUTAS DE VENTAS CON {ID} ====================
        // IMPORTANTE: Estas rutas van AL FINAL para evitar conflictos con /pos y /turnos
        
        // Ver detalle de una venta
        Route::get('/{id}', [CajaController::class, 'show'])->name('show');
        
        // Anular venta (requiere contraseña)
        Route::post('/{id}/anular', [CajaController::class, 'anular'])->name('anular');
        
        // Imprimir ticket (formato térmico)
        Route::get('/{id}/ticket', [CajaController::class, 'ticket'])->name('ticket');
        
    });

});

require __DIR__.'/auth.php';
