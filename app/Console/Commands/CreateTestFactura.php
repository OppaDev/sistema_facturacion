<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Producto;
use App\Models\Factura;
use App\Models\FacturaDetalle;

class CreateTestFactura extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-factura';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear una factura de prueba para testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creando factura de prueba...');

        try {
            // Buscar o crear cliente
            $cliente = User::whereHas('roles', function($query) {
                $query->where('name', 'Cliente');
            })->first();
            
            if (!$cliente) {
                $this->info('No hay clientes. Creando uno...');
                $cliente = User::create([
                    'name' => 'Cliente Prueba',
                    'email' => 'cliente@prueba.com',
                    'password' => bcrypt('password'),
                    'telefono' => '0999999999'
                ]);
                $cliente->assignRole('Cliente');
                $this->info("Cliente creado con ID: {$cliente->id}");
            } else {
                $this->info("Usando cliente existente: {$cliente->name}");
            }

            // Buscar o crear producto
            $producto = Producto::first();
            if (!$producto) {
                $this->info('No hay productos. Creando uno...');
                $producto = Producto::create([
                    'nombre' => 'Producto Prueba',
                    'precio' => 100,
                    'stock' => 10,
                    'descripcion' => 'Producto de prueba para testing'
                ]);
                $this->info("Producto creado con ID: {$producto->id}");
            } else {
                $this->info("Usando producto existente: {$producto->nombre}");
            }

            // Crear factura
            /** @var \App\Models\Factura $factura */
            $factura = Factura::create([
                'cliente_id' => $cliente->id,
                'usuario_id' => 1,
                'subtotal' => 100,
                'iva' => 12,
                'total' => 112,
                'estado_emision' => 'EMITIDA',
                'fecha_emision_email' => now()
            ]);

            // Crear detalle
            FacturaDetalle::create([
                'factura_id' => $factura->id,
                'producto_id' => $producto->id,
                'cantidad' => 1,
                'precio_unitario' => 100,
                'subtotal' => 100
            ]);

            $this->info("✅ Factura de prueba creada con ID: {$factura->id}");
            $this->info("Cliente: {$cliente->nombre}");
            $this->info("Producto: {$producto->nombre}");
            $this->info("Total: $" . number_format((float) $factura->total, 2));

            return 0;

        } catch (\Exception $e) {
            $this->error("Error creando factura de prueba: " . $e->getMessage());
            return 1;
        }
    }
} 