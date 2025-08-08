<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\User;
use App\Models\FacturaDetalle;
use App\Services\FacturaSRIService;

class CrearFacturaPrueba extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-factura {--cliente-id= : ID del cliente específico} {--productos= : Número de productos a incluir (máx 5)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea una factura de prueba con datos SRI completos para testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🧪 Creando factura de prueba...");
        
        // Obtener cliente
        $clienteId = $this->option('cliente-id');
        $cliente = $clienteId 
            ? User::find($clienteId)
            : User::whereHas('roles', function($query) {
                $query->where('name', 'Cliente');
            })->first();
        
        if (!$cliente) {
            $this->error("❌ No hay clientes en el sistema o el ID especificado no existe.");
            $this->line("💡 Tip: Crea un cliente primero o usa --cliente-id=X");
            return 1;
        }
        
        // Obtener productos
        $numProductos = min((int) $this->option('productos', 3), 5);
        $productos = Producto::where('stock', '>', 0)->take($numProductos)->get();
        
        if ($productos->isEmpty()) {
            $this->error("❌ No hay productos con stock disponible.");
            $this->line("💡 Tip: Asegúrate de tener productos con stock > 0");
            return 1;
        }
        
        // Obtener usuario
        $usuario = User::first();
        if (!$usuario) {
            $this->error("❌ No hay usuarios en el sistema.");
            return 1;
        }
        
        try {
            // Crear factura
            $service = new FacturaSRIService();
            $datosSRI = $service->prepararDatosSRI(0); // Se calculará después
            
            $factura = Factura::create([
                'cliente_id' => $cliente->id,
                'usuario_id' => $usuario->id,
                'subtotal' => 0,
                'iva' => 0,
                'total' => 0,
                'estado' => 'pendiente',
                'numero_secuencial' => $datosSRI['numero_secuencial'],
                'cua' => $datosSRI['cua'],
                'fecha_emision' => $datosSRI['fecha_emision'],
                'hora_emision' => $datosSRI['hora_emision'],
                'ambiente' => $datosSRI['ambiente'],
                'tipo_emision' => $datosSRI['tipo_emision'],
                'tipo_documento' => $datosSRI['tipo_documento'],
                'mensaje_autorizacion' => $datosSRI['mensaje_autorizacion'],
            ]);
            
            // Crear detalles
            $subtotalTotal = 0;
            $this->info("📦 Agregando productos:");
            
            foreach ($productos as $producto) {
                $cantidad = rand(1, 3);
                $subtotal = (float) $producto->precio * $cantidad;
                $subtotalTotal += $subtotal;
                
                FacturaDetalle::create([
                    'factura_id' => $factura->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotal,
                    'created_by' => $usuario->id,
                ]);
                
                // Actualizar stock
                $producto->stock -= $cantidad;
                $producto->save();
                
                $this->line("   • {$producto->nombre} x{$cantidad} = $" . number_format($subtotal, 2));
            }
            
            // Actualizar totales
            $totales = $service->calcularTotales($subtotalTotal);
            $factura->update([
                'subtotal' => $totales['subtotal'],
                'iva' => $totales['iva'],
                'total' => $totales['total'],
            ]);
            
            // Generar firma digital y QR
            $this->info("🔐 Generando firma digital y QR...");
            $factura->generarFirmaYQR();
            
            $this->info("✅ Factura de prueba creada exitosamente!");
            $this->newLine();
            $this->line("📋 <comment>Detalles de la factura:</comment>");
            $this->line("   ID: <info>{$factura->id}</info>");
            $this->line("   Cliente: <info>{$cliente->name}</info>");
            $this->line("   Subtotal: <info>$" . number_format((float) $factura->subtotal, 2) . "</info>");
            $this->line("   IVA: <info>$" . number_format((float) $factura->iva, 2) . "</info>");
            $this->line("   Total: <info>$" . number_format((float) $factura->total, 2) . "</info>");
            $this->line("   Secuencial: <info>{$factura->numero_secuencial}</info>");
            $this->line("   CUA: <info>{$factura->cua}</info>");
            
            if ($factura->firma_digital) {
                $this->line("   Firma Digital: <info>" . substr($factura->firma_digital, 0, 50) . "...</info>");
            }
            
            $this->newLine();
            $this->line("🔗 <comment>Para probar:</comment>");
            $this->line("   php artisan test:email tu@email.com --factura-id={$factura->id}");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error al crear factura: " . $e->getMessage());
            return 1;
        }
    }
}
