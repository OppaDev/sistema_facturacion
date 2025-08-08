<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// ============================================================================
// RUTAS DE AUTENTICACIÓN API (Sin middleware auth:sanctum)
// ============================================================================
Route::middleware('throttle:auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// ============================================================================
// RUTAS PROTEGIDAS CON AUTENTICACIÓN SANCTUM
// ============================================================================
Route::middleware(['auth:sanctum'])->group(function () {
    // ========================================================================
    // AUTENTICACIÓN Y PERFIL
    // ========================================================================
    Route::middleware('throttle:sensitive')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh-token', [AuthController::class, 'refresh']);
    });
    
    Route::middleware('throttle:read')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        
        // Mantener compatibilidad con endpoint anterior
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });

    // ========================================================================
    // CLIENTES - CRUD Completo (Rate limiting por operación)
    // ========================================================================
    Route::middleware('throttle:read')->group(function () {
        Route::get('/clientes', [App\Http\Controllers\Api\ClienteController::class, 'index']);
        Route::get('/clientes/{cliente}', [App\Http\Controllers\Api\ClienteController::class, 'show']);
    });
    
    Route::middleware('throttle:write')->group(function () {
        Route::post('/clientes', [App\Http\Controllers\Api\ClienteController::class, 'store']);
        Route::put('/clientes/{cliente}', [App\Http\Controllers\Api\ClienteController::class, 'update']);
        Route::patch('/clientes/{cliente}', [App\Http\Controllers\Api\ClienteController::class, 'update']);
    });
    
    Route::middleware('throttle:sensitive')->group(function () {
        Route::delete('/clientes/{cliente}', [App\Http\Controllers\Api\ClienteController::class, 'destroy']);
    });
    
    // ========================================================================
    // PRODUCTOS - CRUD Completo (Rate limiting por operación)
    // ========================================================================
    Route::middleware('throttle:read')->group(function () {
        Route::get('/productos', [App\Http\Controllers\Api\ProductoController::class, 'index']);
        Route::get('/productos/{producto}', [App\Http\Controllers\Api\ProductoController::class, 'show']);
    });
    
    Route::middleware('throttle:write')->group(function () {
        Route::post('/productos', [App\Http\Controllers\Api\ProductoController::class, 'store']);
        Route::put('/productos/{producto}', [App\Http\Controllers\Api\ProductoController::class, 'update']);
        Route::patch('/productos/{producto}', [App\Http\Controllers\Api\ProductoController::class, 'update']);
        Route::patch('/productos/{id}/stock', [App\Http\Controllers\Api\ProductoController::class, 'updateStock']);
    });
    
    Route::middleware('throttle:sensitive')->group(function () {
        Route::delete('/productos/{producto}', [App\Http\Controllers\Api\ProductoController::class, 'destroy']);
    });
    
    // ========================================================================
    // FACTURAS - CRUD Completo (Rate limiting por operación)
    // ========================================================================
    Route::middleware('throttle:read')->group(function () {
        Route::get('/facturas', [App\Http\Controllers\Api\FacturaController::class, 'index']);
        Route::get('/facturas/{factura}', [App\Http\Controllers\Api\FacturaController::class, 'show']);
        Route::get('/facturas-pendientes', [App\Http\Controllers\Api\FacturaController::class, 'pending']);
        
        // Mantener compatibilidad con endpoint anterior
        Route::get('/facturas-legacy', function (Request $request) {
            $user = $request->user();
            
            $facturas = $user->facturasComoCliente()
                ->with(['detalles.producto', 'usuario'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            $formatoFactura = $facturas->map(function($factura) {
                return [
                    'id' => $factura->id,
                    'numero_factura' => $factura->getNumeroFormateado(),
                    'fecha' => $factura->created_at->format('Y-m-d'),
                    'subtotal' => number_format($factura->subtotal, 2),
                    'iva' => number_format($factura->iva, 2),
                    'total' => number_format($factura->total, 2),
                    'estado' => $factura->estado,
                    'vendedor' => $factura->usuario->name ?? 'N/A',
                    'productos_count' => $factura->detalles->count()
                ];
            });
            
            return response()->json([
                'cliente' => [
                    'nombre' => $user->name,
                    'email' => $user->email,
                ],
                'total_facturas' => $facturas->count(),
                'facturas' => $formatoFactura
            ]);
        });
    });
    
    Route::middleware('throttle:write')->group(function () {
        Route::post('/facturas', [App\Http\Controllers\Api\FacturaController::class, 'store']);
    });
    
    Route::middleware('throttle:sensitive')->group(function () {
        Route::post('/facturas/{id}/cancel', [App\Http\Controllers\Api\FacturaController::class, 'cancel']);
    });

    // ========================================================================
    // PAGOS - CRUD Completo (Rate limiting por operación)
    // ========================================================================
    Route::middleware('throttle:read')->group(function () {
        Route::get('/pagos', [App\Http\Controllers\Api\PagoController::class, 'index']);
        Route::get('/pagos/{pago}', [App\Http\Controllers\Api\PagoController::class, 'show']);
    });
    
    Route::middleware('throttle:write')->group(function () {
        Route::post('/pagos', [App\Http\Controllers\Api\PagoController::class, 'store']);
    });
    
    Route::middleware('throttle:sensitive')->group(function () {
        Route::post('/pagos/{id}/approve', [App\Http\Controllers\Api\PagoController::class, 'approve']);
        Route::post('/pagos/{id}/reject', [App\Http\Controllers\Api\PagoController::class, 'reject']);
    });
});
