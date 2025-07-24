<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\FacturasController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FacturaEstadoController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');



Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'check.user.status'])->name('dashboard');

Route::post('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'check.user.status'])->name('dashboard');

Route::middleware(['auth', 'verified', 'check.user.status'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/cancelar-borrado', [ProfileController::class, 'cancelarBorradoCuenta'])->name('profile.cancelarBorradoCuenta');
});

Route::middleware(['auth', 'verified', 'check.user.status'])->group(function () {

    //Tocken
    Route::post('crearTokenAcceso', [UserController::class, 'crearTokenAcceso'])->name('crearTokenAcceso');


    
    // Clientes: Acceso para Administrador y Secretario
    Route::resource('clientes', ClientesController::class)
        ->middleware('role:Administrador|Secretario');
    
    // Rutas adicionales para clientes
    Route::middleware('role:Administrador|Secretario')->group(function () {
        Route::post('/clientes/{id}/restore', [ClientesController::class, 'restore'])->name('clientes.restore');
        Route::delete('/clientes/{id}/force-delete', [ClientesController::class, 'forceDelete'])->name('clientes.force-delete');
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

    // Facturas: Solo Ventas y Administrador
    Route::middleware('role:Administrador|Ventas')->group(function () {
        Route::get('/facturas', [FacturasController::class, 'index'])->name('facturas.index');
        Route::get('/facturas/create', [FacturasController::class, 'create'])->name('facturas.create');
        Route::post('/facturas', [FacturasController::class, 'store'])->name('facturas.store');
        Route::get('/facturas/{factura}', [FacturasController::class, 'show'])->name('facturas.show');
        Route::get('/facturas/{factura}/pdf', [FacturasController::class, 'downloadPDF'])->name('facturas.pdf');
        Route::post('/facturas/{factura}/send-email', [FacturasController::class, 'sendEmail'])->name('facturas.send-email');
        Route::post('/facturas/preview-pdf', [FacturasController::class, 'previewPDF'])->name('facturas.preview-pdf');
        Route::get('/facturas/debug-stock', [FacturasController::class, 'debugStock'])->name('facturas.debug-stock');
        
        // Rutas con permisos específicos
        Route::get('/facturas/{factura}/edit', [FacturasController::class, 'edit'])
            ->name('facturas.edit')
            ->middleware('factura.permissions:edit');
        Route::put('/facturas/{factura}', [FacturasController::class, 'update'])
            ->name('facturas.update')
            ->middleware('factura.permissions:edit');
        Route::delete('/facturas/{factura}', [FacturasController::class, 'destroy'])
            ->name('facturas.destroy')
            ->middleware('factura.permissions:delete');
        Route::post('/facturas/{factura}/restore', [FacturasController::class, 'restore'])
            ->name('facturas.restore')
            ->middleware('factura.permissions:restore');
        Route::post('/facturas/{factura}/force-delete', [FacturasController::class, 'forceDelete'])
            ->name('facturas.force-delete')
            ->middleware('factura.permissions:forceDelete');
        
        // Rutas para firma y emisión de facturas
        Route::post('/facturas/{factura}/firmar', [FacturaEstadoController::class, 'firmar'])->name('facturas.firmar');
        Route::post('/facturas/{factura}/emitir', [FacturaEstadoController::class, 'emitir'])->name('facturas.emitir');
        Route::get('/facturas/{factura}/estado', [FacturaEstadoController::class, 'estado'])->name('facturas.estado');
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
    });

    // Rutas de Usuarios: Solo Administrador
    Route::middleware('role:Administrador')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-estado', [UserController::class, 'toggleEstado'])->name('users.toggleEstado');
        Route::post('/users/{user}/activar', [UserController::class, 'activarUsuario'])->name('users.activar');
        Route::post('/users/{user}/desactivar', [UserController::class, 'desactivarUsuario'])->name('users.desactivar');
        Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.forceDelete');
        Route::post('/users/cancelar-borrado', [UserController::class, 'cancelarBorradoCuenta'])->name('users.cancelarBorradoCuenta');
    });

});

require __DIR__.'/auth.php';
