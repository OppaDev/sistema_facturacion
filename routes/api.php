<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user()->all();
})->middleware('auth:sanctum');


Route::get('/facturas', function (Request $request) {
    $user = $request->user();
    
    // Obtener las facturas
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
})->middleware('auth:sanctum');

