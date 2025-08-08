<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use App\Models\Factura;
use App\Models\Pago;
use App\Models\User;

class MostrarFacturas extends Command
{
    protected $signature = 'test:mostrar-facturas';
    protected $description = 'Muestra todas las facturas y pagos del sistema';

    public function handle()
    {
        $this->info('📋 FACTURAS EN EL SISTEMA:');
        
        $facturas = Factura::with('cliente')->get();
        
        if ($facturas->count() > 0) {
            foreach ($facturas as $factura) {
                $cliente = $factura->cliente ? $factura->cliente->name : 'Sin cliente';
                $this->line("ID: {$factura->id} | Cliente: {$cliente} | Total: $" . number_format($factura->total, 2) . " | Estado: {$factura->estado}");
            }
        } else {
            $this->warn("⚠️ No hay facturas en el sistema");
        }
        
        $this->info("\n💰 PAGOS EN EL SISTEMA:");
        
        $pagos = Pago::with(['factura', 'factura.cliente'])->get();
        
        if ($pagos->count() > 0) {
            foreach ($pagos as $pago) {
                $cliente = $pago->factura->cliente ? $pago->factura->cliente->name : 'Sin cliente';
                $this->line("ID: {$pago->id} | Factura ID: {$pago->factura_id} | Cliente: {$cliente} | Monto: $" . number_format($pago->monto, 2) . " | Estado: {$pago->estado}");
            }
        } else {
            $this->warn("⚠️ No hay pagos registrados en el sistema");
        }
        
        $this->info("\n👥 CLIENTES EN EL SISTEMA:");
        
        $clientes = User::role('Cliente')->get();
        
        if ($clientes->count() > 0) {
            foreach ($clientes as $cliente) {
                $this->line("ID: {$cliente->id} | Nombre: {$cliente->name} | Email: {$cliente->email}");
            }
        } else {
            $this->warn("⚠️ No hay usuarios con rol Cliente");
        }
        
        return 0;
    }
}