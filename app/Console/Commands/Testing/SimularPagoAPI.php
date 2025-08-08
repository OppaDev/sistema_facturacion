<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use App\Models\Pago;
use App\Models\Factura;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\PagoRegistradoNotification;

class SimularPagoAPI extends Command
{
    protected $signature = 'test:simular-pago-api {factura_id} {cliente_email} {--monto=} {--tipo_pago=transferencia} {--numero_transaccion=}';
    protected $description = 'Simula el registro de un pago via API sin necesidad del servidor web';

    public function handle()
    {
        $facturaId = $this->argument('factura_id');
        $clienteEmail = $this->argument('cliente_email');
        $monto = $this->option('monto');
        $tipoPago = $this->option('tipo_pago');
        $numeroTransaccion = $this->option('numero_transaccion') ?? 'SIM-' . time();

        $this->info("🚀 Simulando registro de pago via API...");

        try {
            // Buscar factura
            $factura = Factura::with('cliente')->find($facturaId);
            if (!$factura) {
                $this->error("❌ Factura ID {$facturaId} no encontrada");
                return 1;
            }

            // Buscar cliente
            $cliente = User::where('email', $clienteEmail)->first();
            if (!$cliente) {
                $this->error("❌ Cliente {$clienteEmail} no encontrado");
                return 1;
            }

            // Validar que el cliente sea el dueño de la factura
            if ($factura->cliente_id !== $cliente->id) {
                $this->error("❌ La factura {$facturaId} no pertenece al cliente {$clienteEmail}");
                return 1;
            }

            // Validar estado de la factura
            if ($factura->estado !== 'pendiente') {
                $this->error("❌ La factura {$facturaId} no está pendiente (estado: {$factura->estado})");
                return 1;
            }

            // Usar monto de la factura si no se especifica
            if (!$monto) {
                $monto = (float) $factura->total;
            }

            // Validar monto
            if ((float) $monto !== (float) $factura->total) {
                $this->error("❌ El monto ${monto} no coincide con el total de la factura $" . number_format($factura->total, 2));
                return 1;
            }

            $this->info("📋 Datos de la simulación:");
            $this->line("  • Factura ID: {$facturaId}");
            $this->line("  • Cliente: {$cliente->name} ({$clienteEmail})");
            $this->line("  • Total factura: $" . number_format($factura->total, 2));
            $this->line("  • Monto pago: $" . number_format($monto, 2));
            $this->line("  • Tipo pago: {$tipoPago}");
            $this->line("  • Número transacción: {$numeroTransaccion}");

            DB::beginTransaction();

            // Crear el pago
            $pago = Pago::create([
                'factura_id' => $facturaId,
                'tipo_pago' => $tipoPago,
                'monto' => $monto,
                'numero_transaccion' => $numeroTransaccion,
                'observacion' => "Pago simulado via comando - Banco del Pichincha",
                'estado' => 'pendiente',
                'pagado_por' => $cliente->id
            ]);

            // Registrar auditoría (simulando IP local)
            \App\Models\Auditoria::create([
                'user_id' => $cliente->id,
                'action' => 'create',
                'model_type' => 'App\\Models\\Pago',
                'model_id' => $pago->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'factura_id' => $pago->factura_id,
                    'tipo_pago' => $pago->tipo_pago,
                    'monto' => $pago->monto,
                    'numero_transaccion' => $pago->numero_transaccion,
                    'estado' => $pago->estado
                ]),
                'description' => "Pago registrado via simulación - Factura #{$pago->factura_id} - Monto: $" . number_format($pago->monto, 2),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Artisan Command Simulator'
            ]);

            // Enviar notificación de confirmación al cliente
            if ($cliente && $cliente->email) {
                try {
                    $cliente->notify(new PagoRegistradoNotification($pago));
                    
                    Log::info('Notificación de pago registrado enviada (simulación)', [
                        'pago_id' => $pago->id,
                        'cliente_email' => $cliente->email
                    ]);

                    $this->info("📧 Notificación de email enviada a: {$cliente->email}");
                } catch (\Exception $e) {
                    Log::warning('Error al enviar notificación de pago registrado (simulación)', [
                        'pago_id' => $pago->id,
                        'error' => $e->getMessage()
                    ]);
                    $this->warn("⚠️ Error al enviar notificación: " . $e->getMessage());
                }
            }

            DB::commit();

            $this->info("\n✅ Pago registrado exitosamente!");
            $this->line("🆔 ID del pago: {$pago->id}");
            $this->line("💰 Estado: {$pago->estado}");
            $this->line("📅 Fecha: " . $pago->created_at->format('Y-m-d H:i:s'));

            $this->info("\n🎯 Próximos pasos:");
            $this->line("1. Accede como usuario con rol 'Pagos' al sistema web");
            $this->line("2. Ve a /pagos para ver el pago pendiente");
            $this->line("3. Aprueba o rechaza el pago desde la interfaz");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error("❌ Error al simular el pago: " . $e->getMessage());
            Log::error('Error en simulación de pago API', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return 1;
        }
    }
}